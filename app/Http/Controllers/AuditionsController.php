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
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\Notification\NotificationHistoryRepository;
use App\Http\Repositories\Notification\NotificationRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Requests\AuditionEditRequest;
use App\Http\Requests\AuditionRequest;
use App\Http\Requests\MediaRequest;
use App\Http\Resources\AuditionFullResponse;
use App\Http\Resources\AuditionResponse;
use App\Http\Resources\ContributorsResource;
use App\Models\Appointments;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Notifications\Notification;
use App\Models\Notifications\NotificationHistory;
use App\Models\Resources;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserAuditions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditionsController extends Controller
{
    public const DESCRIPTION = 'description';
    protected $log;
    protected $find;
    protected $toDate;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
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
                if (isset($request['media'])) {
                    foreach ($request['media'] as $file) {
                        $auditionFilesData[] = [
                            'url' => $file['url'],
                            'type' => $file['type'],
                            'name' => $file['name'],
                            'share' => $file['share'],
                        ];
                    }
                }
                $auditionFilesData[] = [
                    'url' => $request->cover,
                    'type' => 'cover',
                    'name' => $request->cover_name,
                    'share' => 'no',
                ];
                $auditRepo = new AuditionRepository(new Auditions());
                $audition = $auditRepo->create($auditionData);

                if ($request->cover != '') {
                    foreach ($auditionFilesData as $file) {
                        $audition->media()->create(['url' => $file['url'], 'type' => $file['type'], 'name' => $file['name'], 'shareable' => $file['share']]);
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
                        $rol->image()->create(['type' => 4, 'url' => $imageUrl, 'name' => $imageName]);
                    }
                }

                $dataAppoinment = $this->dataToAppointmentProcess($request, $audition);
                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointment = $appointmentRepo->create($dataAppoinment);
                if (!$request->online) {
                    if (isset($request['appointment']['slots'])) {
                        foreach ($request['appointment']['slots'] as $slot) {
                            $dataSlots = $this->dataToSlotsProcess($appointment, $slot);
                            $slotsRepo = new SlotsRepository(new Slots());
                            $slotsRepo->create($dataSlots);
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

                $responseData = ['data' => ['message' => 'Auditions create', 'data' => $audition]];
                $code = 201;
            } else {
                $responseData = ['error' => 'Unauthorized'];
                $code = 404;
            }
            return response()->json($responseData, $code);
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getLine());
            return response()->json(['error' => trans('messages.not_processable')], 406);
            // return response()->json(['error' => 'Unprocessable '], 406);
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
            'url' => $request->url,
            'personal_information' => $request->personal_information,
            'phone' => $request->phone, //null
            'email' => $request->email, //null
            'other_info' => $request->other_info, //null
            'additional_info' => $request->additional_info,
            'union' => strtoupper($request->union),
            'contract' => strtoupper($request->contract),
            'production' => strtoupper($request->production),
            'status' => false,
            'online' => $request->online ?? false,
            'user_id' => Auth::user()->getAuthIdentifier(),

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
    public function dataToAppointmentProcess($request, $audition): array
    {
        return [
            'auditions_id' => $audition->id,
            'date' => $this->toDate->transformDate($request->date) ?? null, //null
            'time' => $request->time ?? null, //null
            'location' => json_encode($request->location) ?? null, //null
            'slots' => $request['appointment']['spaces'] ?? null,
            'type' => $request['appointment']['type'] ?? null,
            'length' => $request['appointment']['length'] ?? null,
            'start' => $request['appointment']['start'] ?? null,
            'end' => $request['appointment']['end'] ?? null,
            'status' => true,
            'round' => 1,
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
     * @param $audition
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

        $dataTemp->all()->whereIn('id', $dataRepoRound)->each(function ($item) use ($data) {
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
                $repoAuditionsExtraData = $repoAuditionsExtra->all()->whereIn('id', $dataInclude);
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
            $dataTemp2->all()->whereIn('id', $dataRepoRound1)->each(function ($item) use ($data) {
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
                    $auditionFilesData[] = [
                        'url' => $file['url'],
                        'type' => $file['type'],
                        'name' => $file['name'],
                    ];
                }
            }

            $auditionRepo = new AuditionRepository(new Auditions());
            $audition = $auditionRepo->find($request->id);

            if (isset($audition->id)) {
                DB::beginTransaction();
                $updateRepo = new AuditionRepository($audition);
                $auditionData = $this->dataAuditionToProcess($request);
                $updateRepo->update($auditionData);
                if ($request->cover_name) {
                    $audition->resources()->where('id', '=', $request->id_cover)->update([
                        'url' => $request->cover,
                        'name' => $request->cover_name,
                    ]);
                }
                foreach ($auditionFilesData as $file) {
                    $audition->media()->updateOrCreate(['url' => $file['url'], 'type' => $file['type'], 'name' => $file['name']]);
                }
                if (isset($request['dates']) && is_array($request['dates'])) {
                    foreach ($request['dates'] as $date) {
                        $audition->dates()->update($this->dataDatesToProcess($date));
                    }
                }
                foreach ($request->roles as $roles) {
                    $roldata = $this->dataRolesToProcess($audition, $roles);
                    $rolesRepo = new RolesRepository(new Roles());
                    $rol = $rolesRepo->find($roles['id']);
                    $rol->image()->update(['url' => $roles['cover']]);
                    $rol->update($roldata);
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

                $dataResponse = ['data' => 'Data Updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            // return response()->json(['data' => 'Data Not Found'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        } catch (\Exception $exception) {
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
                $this->pushNotifications(
                    'You have been invited for the audition ' . $audition->title,
                    $user_contributor,
                    $audition->title
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
}
