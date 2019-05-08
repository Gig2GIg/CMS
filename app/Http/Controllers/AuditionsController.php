<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\ManageDates;
use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionsDatesRepository;
use App\Http\Repositories\Notification\NotificationRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
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
use App\Models\Notifications\Notification;
use App\Models\Resources;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                if(isset($request['media'])) {
                    foreach ($request['media'] as $file) {
                        $auditionFilesData[] = [
                            'url' => $file['url'],
                            'type' => $file['type'],
                            'name' => $file['name'],
                        ];
                    }
                }
                $auditionFilesData[] = [
                    'url' => $request->cover,
                    'type' => 'cover',
                    'name' => $request->cover_name,
                ];
                $auditRepo = new AuditionRepository(new Auditions());
                $audition = $auditRepo->create($auditionData);

                foreach ($auditionFilesData as $file) {
                    $audition->media()->create(['url' => $file['url'], 'type' => $file['type'], 'name' => $file['name']]);
                }
                foreach ($request['dates'] as $date) {
                    $audition->dates()->create($this->dataDatesToProcess($date));
                }
                foreach ($request->roles as $roles) {
                    $roldata = $this->dataRolesToProcess($audition, $roles);
                    $rolesRepo = new RolesRepository(new Roles());
                    $rol = $rolesRepo->create($roldata);
                    $imageUrl =  $roles['cover'] ?? 'https://publicdomainvectors.org/photos/icon_user_whiteongrey.png';
                    $imageName = $roles['name_cover'] ?? 'default';
                    $rol->image()->create(['type' => 4, 'url' => $imageUrl, 'name' => $imageName]);
                }
                $dataAppoinment = $this->dataToAppointmentProcess($request, $audition);
                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointment = $appointmentRepo->create($dataAppoinment);
                foreach ($request['appointment']['slots'] as $slot) {
                    $dataSlots = $this->dataToSlotsProcess($appointment, $slot);
                    $slotsRepo = new SlotsRepository(new Slots());
                    $slotsRepo->create($dataSlots);
                }
                $this->createNotification($audition);

                foreach ($request['contributors'] as $contrib) {
                    $this->saveContributor($contrib, $audition);
                }
                $this->sendPushNotification(
                    $audition,
                    SendNotifications::AUTIDION_ADD_CONTRIBUIDOR
                );

                DB::commit();

                $responseData = ['data' => ['message' => 'Auditions create']];
                $code = 201;
            } else {
                $responseData = ['error' => 'Unauthorized'];
                $code = 404;
            }
            return response()->json($responseData, $code);
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Unprocessable '], 406);
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
            'date' => $this->toDate->transformDate($request->date),
            'time' => $request->time,
            'location' => json_encode($request->location),
            self::DESCRIPTION => $request->description,
            'url' => $request->url,
            'union' => $request->union,
            'contract' => $request->contract,
            'production' => $request->production,
            'status' => false,
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
            'slots' => $request['appointment']['spaces'],
            'type' => $request['appointment']['type'],
            'length' => $request['appointment']['length'],
            'start' => $request['appointment']['start'],
            'end' => $request['appointment']['end'],
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

    public function createNotification($audition): void
    {
        try {

            $notificationData = [
                'title' => $audition->title,
                'code' => Str::random(12),
                'type' => 'audition',
                'notificationable_type' => 'auditions',
                'notificationable_id' => $audition->id
            ];

            if ($audition !== null) {
                $notificationRepo = new NotificationRepository(new Notification());
                $notificationRepo->create($notificationData);
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
            $dataUser = $user->findbyparam('email', $contrib['email']);
            if ($dataUser !== null) {
                $auditionContributorsData = $this->dataToContributorsProcess($dataUser, $audition);
                $contributorRepo = new AuditionContributorsRepository(new AuditionContributors());
                $contributors = $contributorRepo->create($auditionContributorsData);

                $this->log->info("Contributors" . $contributors);
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
            'status' => true
        ];

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getall()
    {
        $data = new AuditionRepository(new Auditions());
        $count = count($data->all());
        if ($count !== 0) {
            $responseData = AuditionResponse::collection($data->all()->sortBy('created_at'));
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
                $dataResponse = ['data' => "Not found Data"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);

        } catch (NotFoundException $exception) {
            return response()->json(['error' => 'Not Found'], 404);

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
            return response()->json(['error' => 'Not Found'], 404);

        }

    }

    public function deleteContributor($id)
    {
        AuditionContributors::find($id)->delete();

        return response()->json([
            'status' => 'Success',
        ]);
    }

    public function show_contributors(Request $request)
    {
        try {
            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);

            if (isset($data->id)) {
                $responseData =  ContributorsResource::collection($data->contributors);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);

        } catch (NotFoundException $exception) {
            return response()->json(['error' => 'Not Found'], 404);

        }

    }

    public function update(AuditionEditRequest $request)
    {
        $this->log->info("FROM UPDATE");
        $this->log->info($request);
        $this->log->info("=================");
        $auditionFilesData=[];
        try {
            if (isset($request['media'])) {
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
                if($request->cover_name) {
                  $audition->resources()->where('id','=',$request->id_cover)->update([
                     'url'=>$request->cover,
                     'name'=>$request->cover_name,
                  ]);
                }
                foreach ($auditionFilesData as $file) {
                    $audition->media()->updateOrCreate(['url' => $file['url'], 'type' => $file['type'], 'name' => $file['name']]);
                }
                foreach ($request['dates'] as $date) {
                    $audition->dates()->update($this->dataDatesToProcess($date));
                }
                foreach ($request->roles as $roles) {
                    $roldata = $this->dataRolesToProcess($audition, $roles);
                    $rolesRepo = new RolesRepository(new Roles());
                    $rol = $rolesRepo->find($roles['id']);
                    $rol->image()->update(['url' => $roles['cover']]);
                    $rol->update($roldata);
                }
                if (isset($request->appointment)) {
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
                DB::commit();

                $dataResponse = ['data' => 'Data Updated'];
                $code = 200;


            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            return response()->json(['data' => 'Data Not Found'], 404);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            DB::rollBack();
            return response()->json(['data' => 'Data Not Update'], 406);
        }
    }

    public function findby(Request $request)
    {
        if (isset($request->base)) {
            return $this->find->findByTitleAndMulti($request);
        } else {
            return $this->find->findByProductionAndMulty($request);
        }
    }

    public function media(MediaRequest $request, Auditions $audition)
    {
        $repository = new AuditionRepository($auditions);
        $data = $repository->findMediaByParams($request->type);

        return response()->json(['data' => $data]);

    }

    public function destroy($audition)
    {
        Auditions::find($audition)->delete();

        return response()->json([
            'status' => 'Success',
        ]);
    }
}
