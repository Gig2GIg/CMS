<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\ManageDates;
use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionsDatesRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\Notification\NotificationHistoryRepository;
use App\Http\Repositories\Notification\NotificationRepository;
use App\Http\Repositories\ResourcesRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Requests\AuditionEditRequest;
use App\Http\Requests\AuditionRequest;
use App\Http\Requests\MediaRequest;
use App\Http\Resources\AuditionFullResponse;
use App\Http\Resources\AuditionAnalyticsResponse;
use App\Http\Resources\AuditionResponse;
use App\Http\Resources\ContributorsResource;
use App\Http\Resources\AuditionCasterResponse;
use App\Models\Appointments;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\Dates;
use App\Models\Feedbacks;
use App\Models\Notifications\Notification;
use App\Models\Notifications\NotificationHistory;
use App\Models\Resources;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\AuditionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Excel;

class AuditionsController extends Controller
{
    public const DESCRIPTION = 'description';
    protected $log;
    protected $find;
    protected $toDate;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['']]);
        $this->log = new LogManger();
        $this->find = new AuditionsFindController();
        $this->toDate = new ManageDates();
    }

    /**
     * @param AuditionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AuditionRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->isJson()) {
                $this->log->info($request);
                $auditionData = $this->dataAuditionToProcess($request);
                if($request->online){
                    $auditionData['status'] = true;
                }
                $auditionData['user_id'] = Auth::user()->getAuthIdentifier();
                if (isset($request['media'])) {
                    foreach ($request['media'] as $file) {
                        $auditionFilesData[] = [
                            'url' => $file['url'],
                            'thumbnail' => isset($file['thumbnail']) ? $file['thumbnail'] : NULL,
                            'type' => $file['type'],
                            'name' => $file['name'],
                            'share' => $file['share'],
                        ];
                    }
                }
                $auditionFilesData[] = [
                    'url' => $request->cover,
                    'thumbnail' => $request->has('cover_thumbnail') ? $request->cover_thumbnail : NULL,
                    'type' => 'cover',
                    'name' => $request->cover_name,
                    'share' => 'no',
                ];
                $auditRepo = new AuditionRepository(new Auditions());
                $audition = $auditRepo->create($auditionData);

                if ($request->cover != '') {
                    foreach ($auditionFilesData as $file) {
                        $audition->media()->create(['url' => $file['url'], 'thumbnail' => $file['thumbnail'], 'type' => $file['type'], 'name' => $file['name'], 'shareable' => $file['share']]);
                    }
                }

                if (isset($request['dates'])) {
                    foreach ($request['dates'] as $date) {
                        if(!empty($date['to']) && !empty($date['from'])){
                            $audition->dates()->create($this->dataDatesToProcess($date));
                        }
                    }
                }

                if (isset($request['dates'])) {
                    foreach ($request->roles as $roles) {
                        $roldata = $this->dataRolesToProcess($audition, $roles);
                        $rolesRepo = new RolesRepository(new Roles());
                        $rol = $rolesRepo->create($roldata);
                        $imageUrl = $roles['cover'] ?? App::make('url')->to('/') . '/images/roles.png';
                        $imageName = $roles['name_cover'] ?? 'default';
                        $imageThumbnail = $roles['cover_thumbnail'] ?? App::make('url')->to('/') . '/images/roles.png';
                        $rol->image()->create(['type' => 4, 'thumbnail' => $imageThumbnail, 'url' => $imageUrl, 'name' => $imageName]);
                    }
                }

                foreach ($request->rounds as $count => $round) {
                    $status = $count == 0 ? true : 2;
                    $dataAppoinment = $this->dataToAppointmentProcess($round, $audition, $status, $count+1);
                    $appointmentRepo = new AppointmentRepository(new Appointments());
                    $appointment = $appointmentRepo->create($dataAppoinment);
                    if (!$request->online) {
                        if (isset($round['appointment']['slots'])) {
                            foreach ($round['appointment']['slots'] as $slot) {
                                $dataSlots = $this->dataToSlotsProcess($appointment, $slot);
                                $slotsRepo = new SlotsRepository(new Slots());
                                $slotsRepo->create($dataSlots);
                            }
                        }
                    }
                }

                if (isset($request['contributors'])) {
                    foreach ($request['contributors'] as $contrib) {
                        $this->saveContributor($contrib, $audition);
                    }
                    $this->sendStoreNotificationToContributors($audition);
                }

                DB::commit();

                // if($request->online){
                //     $this->onlineSubmision($audition);
                // }

                $responseData = ['data' => ['message' => 'Auditions create', 'data' => $audition]];
                $code = 201;
            } else {
                $responseData = ['error' => 'Unauthorized'];
                $code = 404;
            }
            return response()->json($responseData, $code);
        } catch (\Exception $exception) {
            // dd($exception);
            DB::rollBack();
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getLine());
            return response()->json(['error' => trans('messages.not_processable')], 406);
            // return response()->json(['error' => 'Unprocessable '], 406);
        }
    }

    public function onlineSubmision($audition)
    {
        try {            
            $repo = new AppointmentRepository(new Appointments());
            $appointment = [
                'date' => '',
                'time' => '0',
                'location' => '0',
                'lat' => '0',
                'lng' => '0',
                'slots' => '0',
                'type' => '1',
                'length' => '0',
                'start' => '0',
                'end' => '0',
                'round' => 1,
                'status' => true,
                'auditions_id' => $audition->id,
            ];
            $data = $repo->create($appointment);
            $testDebug = array();
            $newAppointmentId = $data->id;
            $testDebug['newAppointmentId'] = $newAppointmentId;

            // return response()->json(['message' => 'Round create', 'data' => $data], 200);
            return true;
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function sendStoreNotificationToContributors($audition): void
    {
        try {
            $this->sendPushNotification(
                $audition,
                SendNotifications::AUTIDION_ADD_CONTRIBUIDOR,
                1,
                'You have been invited for the audition ' . $audition->title
            );
            $audition->contributors->each(function ($user_contributor) use ($audition) {
                //                $this->pushNotifications(
                //                    'You have been registered for the audition ' . $audition->title,
                //                    $user_contributor,
                //                    $audition->title
                //                );
                // $this->sendPushNotification(
                //     $audition,
                //     SendNotifications::AUTIDION_ADD_CONTRIBUIDOR,
                //     $user_contributor,
                //     'You have been invited for the audition ' . $audition->title
                // );
            });
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    /**
     * @param $request
     * @return array
     */
    public function dataAuditionToProcess($request): array
    {
        return [
            'title' => $request->title,

            self::DESCRIPTION => $request->description,
            'url' => isset( $request->url ) ? $request->url : null,
            'personal_information' => $request->personal_information,
            'phone' => $request->phone, //null
            'email' => $request->email, //null
            'end_date' => isset( $request->end_date ) && $request->end_date != null && $request->end_date != '' ? $request->end_date : null,
            'other_info' => $request->other_info, //null
            'additional_info' => $request->additional_info,
            'union' => strtoupper($request->union),
            'contract' => strtoupper($request->contract),
            'production' => strtoupper($request->production),
            'status' => false,
            'online' => $request->online ?? false
        ];
    }

    /**
     * @param $date
     * @param $audition
     * @return array
     */
    public function dataDatesToProcess($date): array
    {
        return [
            'to' => $this->toDate->transformDate($date['to']),
            'from' => $this->toDate->transformDate($date['from']),
            'type' => $date['type'],

        ];
    }

    /**
     * @param $audition
     * @param $roles
     * @return array
     */
    public function dataRolesToProcess($audition, $roles): array
    {
        return [
            'auditions_id' => $audition->id,
            'name' => $roles['name'],
            self::DESCRIPTION => $roles[self::DESCRIPTION],
        ];
    }

    /**
     * @param AuditionRequest $request
     * @param $audition
     * @return array
     */
    public function dataToAppointmentProcess($round, $audition, $status = true, $count = null): array
    {
        $lat = NULL;
        $lng = NULL;
        if(isset($round['location'])){
            $lat = $round['location']['latitude'];
            $lng = $round['location']['longitude']; 
        }

        return [
            'auditions_id' => $audition->id,
            'date' => isset($round['date']) ? $this->toDate->transformDate($round['date']) : null, //null
            'time' => isset($round['time']) ? $round['time'] : null, //null
            'location' => isset($round['location']) ? json_encode($round['location']) : null, //null
            'lat' => $lat,
            'lng' => $lng,
            'slots' => $round['appointment']['spaces'] ?? null,
            'type' => $round['appointment']['type'] ?? null,
            'length' => $round['appointment']['length'] ?? null,
            'start' => $round['appointment']['start'] ?? null,
            'end' => $round['appointment']['end'] ?? null,
            'status' => $status,
            'round' => $count,
        ];
    }

    /**
     * @param Appointments $appointment
     * @param $slot
     * @return array
     */
    public function dataToSlotsProcess($appointment, $slot): array
    {
        return [
            'appointment_id' => $appointment->id,
            'time' => $slot['time'],
            'number' => $slot['number'] ?? null,
            'status' => $slot['status'],
            'is_walk' => $slot['is_walk'],
        ];
    }

    public function createNotification($audition, $auditionContributor, $user): void
    {
        try {

            $notificationData = [
                'title' => $audition->title,
                'code' => SendNotifications::AUTIDION_ADD_CONTRIBUIDOR,
                'type' => 'audition',
                'notificationable_type' => 'auditions',
                'notificationable_id' => $audition->id,
            ];

            $notificationHistoryData = [
                'title' => $audition->title,
                'code' => SendNotifications::AUTIDION_ADD_CONTRIBUIDOR,
                'user_id' => $user->id,
                'message' => 'You have been invited to audition ' . $audition->title,
                'custom_data' => $auditionContributor->id,
                'status' => 'unread',
            ];

            if ($audition !== null) {
                $notificationRepo = new NotificationRepository(new Notification());
                $notificationRepo->create($notificationData);

                $notificationHistoryRepo = new NotificationHistoryRepository(new NotificationHistory);
                $notificationLog = $notificationHistoryRepo->create($notificationHistoryData);

                $this->log->info("Notification History " . $notificationLog);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    /**
     * @param $contrib
     * @param $auditionappointment
     * @throws NotFoundException
     * @throws \App\Http\Exceptions\CreateException
     */
    public function saveContributor($contrib, $audition): void
    {
        try {
            $user = new UserRepository(new User());
            $email = new SendMail();
            $dataUser = $user->findbyparam('email', $contrib['email']);

            if ($dataUser !== null) {
                $auditionContributorsData = $this->dataToContributorsProcess($dataUser, $audition);
                $contributorRepo = new AuditionContributorsRepository(new AuditionContributors());
                $contributors = $contributorRepo->create($auditionContributorsData);
                $send = $email->sendContributor($contrib['email'], $audition->title);
                // $this->createNotification($audition, $contributors, $dataUser);
                // $this->sendPushNotification(
                //     $audition,
                //     SendNotifications::AUTIDION_ADD_CONTRIBUIDOR,
                //     $dataUser->id,
                //     'You have been invited for the audition ' . $audition->title
                // );
                $this->log->info("Contributors" . $contributors);
                $this->log->info("send mail" . $send);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    /**
     * @param $contrib
     * @param $audition
     * @return array
     */
    public function dataToContributorsProcess($contrib, $audition): array
    {
        return [
            'user_id' => $contrib->id,
            'auditions_id' => $audition->id,
            'status' => false,
        ];
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getall()
    {
        $data = new Collection();
        $dataTemp = new AuditionRepository(new Auditions());

        $repoAppoRound = new AppointmentRepository(new Appointments());
        $dataRepoRound = $repoAppoRound->all()
            ->where('status', true)
            ->where('round', 1) //We need only first round in all search
            ->pluck('auditions_id');

        $dataTemp->all()->whereIn('id', $dataRepoRound)->where("status" , "!=", 2)->each(function ($item) use ($data) {
            $data->push($item);
        });

        $userAuditions = new UserAuditionsRepository(new UserAuditions());
        $dataUserrepo = $userAuditions->getByParam('user_id', $this->getUserLogging());
        $dataAuditions = $dataUserrepo->where('type', '=', '3');
        $idAuditions = new Collection();
        $dataAuditions->each(function ($item) use ($idAuditions) {
            $repoAppoinmets = new AppointmentRepository(new Appointments());
            $dataRepoAppo = $repoAppoinmets->find($item->appointment_id);
            $audirepo = new AuditionRepository(new Auditions());
            $idAuditions->push($audirepo->find($dataRepoAppo->auditions_id));
        });
        $repoFeedback = new FeedbackRepository(new Feedbacks());
        $dataFeedBackRepo = $repoFeedback->findbyparam('user_id', $this->getUserLogging());
        $dataFeedBackRepo = $dataFeedBackRepo->get()->where('favorite', true);

        $idFeedAuditions = new Collection();
        $dataFeedBackRepo->each(function ($item) use ($idFeedAuditions) {
            $repoAppoinmets = new AppointmentRepository(new Appointments());
            $dataRepoAppo = $repoAppoinmets->find($item->appointment_id);
            $audirepo = new AuditionRepository(new Auditions());
            $idFeedAuditions->push($audirepo->find($dataRepoAppo->auditions_id));
        });

        $dataExclude = $idAuditions->pluck('id')->unique();
        $dataInclude = $idFeedAuditions->pluck('id')->unique();
        $count = count($data->all());
        if ($count !== 0) {
            if ($dataExclude->count() > 0) {
                $data = $data->whereNotIn('id', $dataExclude);
            }

            if ($dataInclude->count() > 0) {
                $repoAuditionsExtra = new AuditionRepository(new Auditions());
                $repoAuditionsExtraData = $repoAuditionsExtra->all()->whereIn('id', $dataInclude)->where("status" , "!=", 2);
                $repoAuditionsExtraData->each(function ($item) use ($data) {
                    $data->push($item);
                });
            }
            $dataTemp2 = new AuditionRepository(new Auditions());
            $repoAppoRound1 = new AppointmentRepository(new Appointments());
            $dataRepoRound1 = $repoAppoRound1->all()
                ->where('status', true)
                ->where('round', 1)
                ->pluck('auditions_id');
            $dataTemp2->all()->whereIn('id', $dataRepoRound1)->where("status" , "!=", 2)->each(function ($item) use ($data) {
                $data->push($item);
            });

            $data = $data->sortBy('created_at')->unique();
            $responseData = AuditionResponse::collection($data);
            $dataResponse = ['data' => $responseData];
            $code = 200;
        } else {
            $dataResponse = ['data' => "Not found Data"];
            $code = 404;
        }
        return response()->json($dataResponse, $code);
    }

    public function getFullData(Request $request)
    {
        try {
            $data = new AuditionRepository(new Auditions());
            $count = count($data->all());
            if ($count !== 0) {
                $responseData = AuditionFullResponse::collection($data->all()->sortByDesc('created_at'));
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['data' => trans('messages.data_not_found')];
                // $dataResponse = ['data' => "Not found Data"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => trans('messages.data_not_found')], 404);
            // return response()->json(['error' => 'Not Found'], 404);
        }
    }

    public function get(Request $request)
    {
        try {
            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);

            if (isset($data->id)) {
                $responseData = new AuditionFullResponse($data);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => trans('messages.data_not_found')], 404);
            // return response()->json(['error' => 'Not Found'], 404);
        }
    }

    public function getAuditionUserData(Request $request)
    {
        try {
            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);

            if (isset($data->id)) {
                $responseData = new AuditionCasterResponse($data);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => trans('messages.data_not_found')], 404);
            // return response()->json(['error' => 'Not Found'], 404);
        }
    }

    public function deleteContributor($id)
    {
        AuditionContributors::find($id)->delete();
        // return response()->json(['status' => 'Success',]);
        return response()->json(['status' => trans('messages.success')]);
    }

    public function show_contributors(Request $request)
    {
        try {
            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);

            if (isset($data->id)) {
                $responseData = ContributorsResource::collection($data->contributors);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            return response()->json(['error' => trans('messages.data_not_found')], 404);
            // return response()->json(['error' => 'Not Found'], 404);
        }
    }

    public function update(AuditionEditRequest $request)
    {
        $this->log->info("UPDATE AUDITION");
        $this->log->info($request);
        $auditionFilesData = [];
        try {
            if (isset($request['media']) && is_array($request['media'])) {
                foreach ($request['media'] as $file) {
                    /*
                    $auditionFilesData[] = [
                        'url' => $file['url'],
                        'thumbnail' => isset($file['thumbnail']) ? $file['thumbnail'] : NULL,
                        'type' => $file['type'],
                        'name' => $file['name'],
                    ];
                    */
                    if(isset($file['id']) && $file['id'] != "") {
                        $auditionFilesData[] = [
                            'id' => $file['id'],
                            'url' => $file['url'],
                            'thumbnail' => isset($file['thumbnail']) ? $file['thumbnail'] : NULL,
                            'type' => $file['type'],
                            'name' => $file['name'],
                        ];
                    } else {

                        $auditionFilesData[] = [
                            'url' => $file['url'],
                            'thumbnail' => isset($file['thumbnail']) ? $file['thumbnail'] : NULL,
                            'type' => $file['type'],
                            'name' => $file['name'],
                        ];
                    }
                }
            }

            $auditionRepo = new AuditionRepository(new Auditions());
            $audition = $auditionRepo->find($request->id);
            $oldAudition = new AuditionFullResponse($audition);

            if (isset($audition->id)) {

                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointment = $appointmentRepo->findbyparam('auditions_id', $audition->id)->first();

                if (isset($appointment->id) && isset($request->location) && isset($request->location['latitude']) &&  isset($request->location['longitude'])) {
                    $appointment->update([
                        'location' => json_encode($request->location),
                        'lat' => $request->location['latitude'],
                        'lng' => $request->location['longitude']
                    ]);
                }
                DB::beginTransaction();
                $updateRepo = new AuditionRepository($audition);
                $auditionData = $this->dataAuditionToProcess($request);
                $updateRepo->update($auditionData);
                if ($request->cover_name) {
                    $audition->resources()->where('id', '=', $request->id_cover)->update([
                        'url' => $request->cover,
                        'name' => $request->cover_name,
                        'thumbnail' => $request->has('cover_thumbnail') ? $request->cover_thumbnail : NULL
                    ]);
                }
                foreach ($auditionFilesData as $file) {
                    // $audition->media()->updateOrCreate(['url' => $file['url'], 'thumbnail' => $file['thumbnail'], 'type' => $file['type'], 'name' => $file['name']]);
                    
                    if(isset($file['id'])) {
                        $auditionsResourcesRepo = new ResourcesRepository(new Resources());
                        $auditionsResources = $auditionsResourcesRepo->find($file['id']);
                        $auditionsResources->update($file);
                    } else {
                        $audition->media()->create($file);
                    }
                }
                if (isset($request['dates']) && is_array($request['dates'])) {
                    foreach ($request['dates'] as $date) {
                        if(isset($date['id'])) {
                            $auditionsDatesRepo = new AuditionsDatesRepository(new Dates());
                            $auditionsDates = $auditionsDatesRepo->find($date['id']);
                            $auditionsDates->update($this->dataDatesToProcess($date));
                        } else {
                            $audition->dates()->create($this->dataDatesToProcess($date));
                        }
                    }

                }
                foreach ($request->roles as $roles) {
                    $roldata = $this->dataRolesToProcess($audition, $roles);
                    $rolesRepo = new RolesRepository(new Roles());
                    $rol = $rolesRepo->find($roles['id']);

                    $rolUpdateData = array();

                    if (isset($roles['cover'])) {
                        $rolUpdateData['url'] = $roles['cover'];
                    }

                    if (isset($roles['cover_thumbnail'])) {
                        $rolUpdateData['thumbnail'] = $roles['cover_thumbnail'];
                    }

                    if (isset($roles['cover_name'])) {
                        $rolUpdateData['name'] = $roles['cover_name'];
                    }

                    if(!empty($rolUpdateData)) {
                        $rol->image()->update($rolUpdateData);
                        $rol->update($roldata);
                    }
                }
                if (isset($request->appointment) && isset($request->appointment[0]['slots'])) {
                    foreach ($request->appointment[0]['slots'] as $slot) {

                        $dataSlots = [
                            'time' => $slot['time'],
                            'number' => $slot['number'] ?? null,
                            'status' => $slot['status'],
                        ];
                        $slotsRepo = new SlotsRepository(new Slots());
                        $slotsRepo->find($slot['id'])->update($dataSlots);
                    }
                }

                if (isset($request['contributors'])) {
                    foreach ($request['contributors'] as $contrib) {
                        if (!isset($contrib['id'])) {
                            $this->saveContributor($contrib, $audition);
                        }
                    }
                    $this->sendPushNotification(
                        $audition,
                        SendNotifications::AUTIDION_ADD_CONTRIBUIDOR
                    );
                }
                DB::commit();

                // Tracking audition update records
                $newAudition = new AuditionFullResponse($auditionRepo->find($request->id));
                $this->trackAuditionUpdate($oldAudition->toArray(), $newAudition->toArray());

                $dataResponse = ['data' => 'Data Updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getFile());
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getLine());
            // return response()->json(['data' => 'Data Not Found'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        } catch (\Exception $exception) {
            // dd($exception->getMessage());
            $this->log->error($exception->getFile());
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getLine());
            DB::rollBack();
            return response()->json(['data' => trans('messages.data_not_update')], 406);
            // return response()->json(['data' => 'Data Not Update'], 406);
        }
    }

    public function addContruibuitor(Request $request)
    {

        $auditionFilesData = [];
        try {

            $auditionRepo = new AuditionRepository(new Auditions());
            $audition = $auditionRepo->find($request->id);

            if (isset($request['contributors'])) {
                foreach ($request['contributors'] as $contrib) {
                    $this->saveContributor($contrib, $audition);
                }

                // $dataResponse = 'Contruibuitors Add';
                // $code = 200;
                return response()->json(['data' => trans('messages.contruibuitors_add')], 200);
                // return response()->json(['data' => $dataResponse], $code);
            }
        } catch (NotFoundException $exception) {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Data Not Found'], 404);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getLine());
            DB::rollBack();
            return response()->json(['data' => trans('messages.data_not_update')], 406);
            // return response()->json(['data' => 'Data Not Update'], 406);
        }
    }

    public function findby(Request $request)
    {

        try {
            $this->log->info($request);
            if (isset($request->base)) {
                return $this->find->findByTitleAndMulti($request);
            } else {
                return $this->find->findByProductionAndMulty($request);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => trans('messages.data_not_found')], 404);
            // return response()->json(['error' => 'Not Found'], 404);
        }
    }

    public function media(MediaRequest $request, Auditions $audition)
    {
        $repository = new AuditionRepository($audition);
        $data = $repository->findMediaByParams($request->type);

        return response()->json(['data' => $data]);
    }

    public function destroy($audition)
    {
        Auditions::find($audition)->delete();
        return response()->json(['status' => trans('messages.success')]);
        // return response()->json(['status' => 'Success',]);
    }

    public function updateInviteContribuidor(Request $request)
    {
        try {
            $repo = new AuditionContributorsRepository(new AuditionContributors());
            $auditionContributorsData = $repo->find($request->id);

            $auditionRepo = new AuditionRepository(new Auditions());
            $audition = $auditionRepo->find($auditionContributorsData->auditions_id);

            $notificationHistoryRepo = new NotificationHistoryRepository(new NotificationHistory());

            $notification = $notificationHistoryRepo->find($request->notification_id);

            // $this->sendInviteNotificationToContributors($audition);

            $data = [
                'status' => $request->status,
            ];

            $invite = $auditionContributorsData->update($data);

            if ($request->status === '1') {

                $dataNotification = [
                    'message' => 'You have accepted this invitation to ' . $audition->title,
                    'status' => 'accepted',
                ];

                if ($notification->update($dataNotification)) {
                    $dataResponse = 'Invite Update';
                    $code = 200;
                } else {
                    $dataResponse = 'Invite Error';
                    $code = 404;
                }
            }

            if ($request->status === '0') {

                $dataNotification = [
                    'message' => 'You have rejected this invitation to ' . $audition->title,
                    'status' => 'rejected',
                ];

                if ($notification->update($dataNotification)) {
                    $dataResponse = 'Invite Update';
                    $code = 200;
                } else {
                    $dataResponse = 'Invite Error';
                    $code = 404;
                }
            }

            return response()->json(['data' => $dataResponse], $code);
        } catch (\Exception $exception) {
            $this->log->error($exception);
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Error to process'], 406);
        }
    }

    public function sendInviteNotificationToContributors($audition): void
    {
        try {
            $audition->contributors->each(function ($user_contributor) use ($audition) {
                $this->sendPushNotification(
                    $audition,
                    SendNotifications::CASTER_AUDITION_INVITE,
                    $user_contributor,
                    $audition->title,
                    'You have been invited for the audition ' . $audition->title
                );
            });
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function updateBannedStatus(Request $request)
    {
        try {
            $auditionRepo = new AuditionRepository(new Auditions());
            $banStatus = $request->banned;
            $audition = $auditionRepo->find($request->id);
            $updateRepo = new AuditionRepository($audition);
            $update = $updateRepo->update(['banned' => $banStatus]);

            if ($update) {
                $responseData = new AuditionFullResponse($audition);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Updated'];
                $code = 406;
            }
            return response()->json(['data' => $dataResponse], $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Data Not Updated'], 406);
            return response()->json(['data' => trans('messages.data_not_update')], 406);
        }
    }

    public function getAnalytics(Request $request)
    {
        try {
            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);
            if (isset($data->id)) {
                $responseData = new AuditionAnalyticsResponse($data);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            //dd($exception->getMessage());
            return response()->json(['error' => trans('messages.data_not_found')], 404);
            // return response()->json(['error' => 'Not Found'], 404);
        }
    }

    // public function testTract(Request $request){
    //     $this->trackAuditionUpdate($request->oldData, $request->newData);
    // }

    public function trackAuditionUpdate($oldData = null, $newData = null)
    {
        try {
            //checking diff in two arrays old and new
            $diff_old = array_diff(array_map('serialize', $oldData), array_map('serialize', $newData));
            $diff_new = array_diff(array_map('serialize', $newData), array_map('serialize', $oldData));
            $multidimensional_diff_old = array_map('unserialize', $diff_old);
            $multidimensional_diff_new = array_map('unserialize', $diff_new);

            $oldCount = count($multidimensional_diff_old);
            $newCount = count($multidimensional_diff_new);

            $greater = $oldCount >= $newCount ? 'old' : 'new';

            $loopArr = $oldCount >= $newCount ? $multidimensional_diff_old : $multidimensional_diff_new;
            $diffArray = $oldCount < $newCount ? $multidimensional_diff_old : $multidimensional_diff_new;

            $insertData = array();

            foreach ($loopArr as $key => $value) {
                $d = array();
                $d['audition_id'] = $oldData['id'];
                $d['edited_by'] = $this->getUserLogging();
                $d['created_at'] = Carbon::now('UTC')->format('Y-m-d H:i:s');
                
                if($key != 'cover_thumbnail' && $key != 'apointment') {
                    if($key == 'dates') {
                        // checking dates array diffrances
                        $date_old = array_diff(array_map('serialize', $oldData['dates']), array_map('serialize', $newData['dates']));
                        $date_new = array_diff(array_map('serialize', $newData['dates']), array_map('serialize', $oldData['dates']));
                        $multidimensional_date_old = array_map('unserialize', $date_old);
                        $multidimensional_date_new = array_map('unserialize', $date_new);

                        foreach ($multidimensional_date_old as $k => $v) {
                            if($v['from'] != $multidimensional_date_new[$k]['from']){
                                $d['key'] = $v['type'] . '_from_date';
                                $d['old_value'] = $v['from'];
                                $d['new_value'] = $multidimensional_date_new[$k]['from'];
                                array_push($insertData, $d);
                            } 
                            if($v['to'] != $multidimensional_date_new[$k]['to']){
                                $d['key'] = $v['type'] . '_to_date';
                                $d['old_value'] = $v['to'];
                                $d['new_value'] = $multidimensional_date_new[$k]['to'];
                                array_push($insertData, $d);
                            }                        
                        }
                    } else if($key == 'roles') {
                        // checking roles array diffrances
                        $role_old = array_diff(array_map('serialize', $oldData['roles']), array_map('serialize', $newData['roles']));
                        $role_new = array_diff(array_map('serialize', $newData['roles']), array_map('serialize', $oldData['roles']));
                        $multidimensional_role_old = array_map('unserialize', $role_old);
                        $multidimensional_role_new = array_map('unserialize', $role_new);

                        $oldCount = count($multidimensional_role_old);
                        $newCount = count($multidimensional_role_new);

                        $greater = $oldCount >= $newCount ? 'old' : 'new';

                        $loopArray = $oldCount >= $newCount ? $multidimensional_role_old : $multidimensional_role_new;
                        $smallerArray = $oldCount < $newCount ? $multidimensional_role_old : $multidimensional_role_new;

                        foreach ($loopArray as $k => $v) {
                            if($v['name'] != $smallerArray[$k]['name']){
                                $d['key'] = $key . '_name';
                                if($greater == 'old') {
                                    $d['old_value'] = $v['name'];  
                                    $d['new_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['name'] : '--';
                                }else {
                                    $d['old_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['name'] : '--';
                                    $d['new_value'] = $v['name'];  
                                }
                                
                                array_push($insertData, $d);
                            }
                            if($v['description'] != $smallerArray[$k]['description']){
                                $d['key'] = $key . '_description';
                                if($greater == 'old') {
                                    $d['old_value'] = $v['description'];  
                                    $d['new_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['description'] : '--';
                                }else {
                                    $d['old_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['description'] : '--';
                                    $d['new_value'] = $v['description'];  
                                }
                                
                                array_push($insertData, $d);
                            } 
                            if($v['image']['url'] != $smallerArray[$k]['image']['url']){
                                $d['key'] = $key . '_image_url';
                                if($greater == 'old') {
                                    $d['old_value'] = $v['image']['url'];  
                                    $d['new_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['image']['url'] : '--';
                                }else {
                                    $d['old_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['image']['url'] : '--';
                                    $d['new_value'] = $v['image']['url'];  
                                }
                                
                                array_push($insertData, $d);
                            } 
                            if($v['image']['name'] != $smallerArray[$k]['image']['name']){
                                $d['key'] = $key . '_image_name';
                                if($greater == 'old') {
                                    $d['old_value'] = $v['image']['name'];  
                                    $d['new_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['image']['name'] : '--';
                                }else {
                                    $d['old_value'] = isset($smallerArray[$k]['image']) ? $smallerArray[$k]['image']['name'] : '--';
                                    $d['new_value'] = $v['image']['name'];  
                                }
                                
                                array_push($insertData, $d);
                            }                         
                        }
                    } else if($key == 'contributors') {
                        // checking contributor array diffrances
                        $contributor_old = array_diff(array_map('serialize', $oldData['contributors']), array_map('serialize', $newData['contributors']));
                        $contributor_new = array_diff(array_map('serialize', $newData['contributors']), array_map('serialize', $oldData['contributors']));
                        $multidimensional_contributor_old = array_map('unserialize', $contributor_old);
                        $multidimensional_contributor_new = array_map('unserialize', $contributor_new);

                        $oldCount = count($multidimensional_contributor_old);
                        $newCount = count($multidimensional_contributor_new);

                        $greater = $oldCount >= $newCount ? 'old' : 'new';

                        $loopArray = $oldCount >= $newCount ? $multidimensional_contributor_old : $multidimensional_contributor_new;
                        $smallerArray = $oldCount < $newCount ? $multidimensional_contributor_old : $multidimensional_contributor_new;

                        foreach ($loopArray as $k => $v) {
                            $d['key'] = $key;

                            if($greater == 'old') {
                                $d['old_value'] = $v['contributor_info']['email'];
                                $d['new_value'] = isset($smallerArray[$k]) ? $smallerArray[$k]['contributor_info']['email'] : '--';
                                array_push($insertData, $d);    
                            } else {
                                $d['old_value'] = isset($smallerArray[$k]) ? $smallerArray[$k]['contributor_info']['email'] : '--';
                                $d['new_value'] = $v['contributor_info']['email'];
                                array_push($insertData, $d);    
                            }
                        }
                    } else {
                        $d['key'] = $key;
                        if($greater == 'old'){
                            $d['old_value'] = $key == 'location' || $key == 'production' ? json_encode($value) : $value;
                            if(isset($diffArray[$key])){
                                $d['new_value'] = $key == 'location' || $key == 'production' ? json_encode($diffArray[$key]) : $diffArray[$key];    
                            }else {
                                $d['new_value'] = '--';    
                            }
                            
                            array_push($insertData, $d);    
                        }else {
                            if(isset($diffArray[$key])){
                                $d['old_value'] = $key == 'location' || $key == 'production' ? json_encode($diffArray[$key]) : $diffArray[$key];    
                            }else {
                                $d['old_value'] = '--';    
                            }
                            $d['new_value'] = $key == 'location' || $key == 'production' ? json_encode($value) : $value;
                            array_push($insertData, $d);    
                        }
                        
                    }
                }
            }

            // dd($insertData);
            AuditionLog::insert($insertData);
            // dd([$multidimensional_date_old, $multidimensional_date_new]);
            return true;
        } catch (NotFoundException $exception) {
            $this->log->error("ERR IN AUDITION UPDATE TRACK:::: " . $exception->getMessage());
            return false;
        }
    }
}
