<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Controllers\Utils\Notifications as SendNotifications;

use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionContractRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionVideosRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserManagerRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Repositories\ResourcesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\OnlineMediaAuditionsRepository;

use App\Http\Resources\AuditionResponse;
use App\Http\Resources\AuditionsDetResponse;
use App\Http\Resources\AuditionVideosResource;
use App\Http\Resources\ContractResponse;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserAuditionsResource;

use App\Models\Appointments;
use App\Models\AuditionContract;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\AuditionVideos;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use App\Models\UserSlots;
use App\Models\Resources;
use App\Models\Slots;
use App\Models\OnlineMediaAudition;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuditionManagementController extends Controller
{
    protected $log;
    protected $collection;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function saveUserAudition(Request $request)
    {
        if (!$request->online) {
            return $this->registerNotOnline($request);
        }

        return $this->registerOnline($request);
    }

    public function saveAuditionNotificationToUser($user, $audition): void
    {
        try {
            if ($user instanceof User) {
                $user->notification_history()->create([
                    'title' => $audition->title,
                    'code' => 'upcoming_audition',
                    'status' => 'unread',
                    'message' => 'You have been added to the audition ' . $audition->title
                ]);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function sendSaveAuditionNotificationToUser($user, $audition): void
    {
        try {
            $this->pushNotifications(
                'You have been added to upcoming audition ' . $audition->title,
                $user
            );
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function updateAudition(Request $request)
    {
        try {
            DB::beginTransaction();

            $dataSlot = isset($request->slot['slot']) ? $request->slot['slot'] : null;

            $dataRepoAuditionUser = new UserAuditionsRepository(new UserAuditions());
            $dataAuditionsUser = $dataRepoAuditionUser->find($request->id);
            $dataRepo = new UserSlotsRepository(new UserSlots());
            $dataRepo->create([
                'user_id' => $this->getUserLogging(),
                'appointment_id' => $dataAuditionsUser->appointment_id,
                'slots_id' => $dataSlot,
                'roles_id' => $dataAuditionsUser->rol_id,
                'status' => 1
            ]);

            $updateAudi = $dataAuditionsUser->update([
                'type' => '1',
                'slot_id' => $dataSlot
            ]);
            if (!$updateAudi) {
                throw new UpdateException('Not Update audition status');
            }
            $code = 200;
            $responseData = 'Audition update';
            DB::commit();
            return response()->json(['data' => $responseData], $code);
        } catch (Exception $exception) {
            DB::rollBack();
            $this->log->error($exception->getMessage());
            // return response()->json(['error' => 'Audition not update'], 406);
            return response()->json(['error' => trans('messages.audition_not_update')], 406);
        }
    }

    public function getUpcoming()
    {
        try {
            // $userAuditions = new UserAuditionsRepository(new UserAuditions());
            // $data = $userAuditions->getByParam('user_id', $this->getUserLogging());
            // $dataAuditions = $data->sortByDesc('created_at');
            // // $dataAuditions = $data->where('type', '=', '1')->sortByDesc('created_at');

            $dataAuditions = DB::table('appointments')
                // ->select('UA.id', 'UA.appointment_id', 'UA.rol_id', 'UA.slot_id', 'UA.type', 'UA.created_at', 'UA.updated_at', 'appointments.status')
                ->select('UA.id', 'UA.user_id', 'UA.appointment_id', 'UA.rol_id', 'UA.slot_id', 'UA.type', 'UA.created_at', 'UA.updated_at', 'UA.assign_no')
                ->Join('user_auditions AS UA', 'appointments.id', '=', 'UA.appointment_id')
                ->where('UA.user_id', $this->getUserLogging())
                ->where('appointments.status', 1)
                ->get()->sortByDesc('created_at');

            if ($dataAuditions->count() > 0) {
                $dataResponse = ['data' => UserAuditionsResource::collection($dataAuditions)];
            } else {
                $dataResponse = ['data' => []];
            }
            return response()->json($dataResponse, 200);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getLine());
            $this->log->error($exception->getTraceAsString());
            // return response()->json(['data' => 'Not Found Data'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function getPassed()
    {
        try {
            // $userAuditions = new UserAuditionsRepository(new UserAuditions());
            // $data = $userAuditions->getByParam('user_id', $this->getUserLogging())->sortByDesc('created_at');

            $data = DB::table('appointments')
                ->select('UA.id', 'UA.appointment_id', 'UA.rol_id', 'UA.slot_id', 'UA.type', 'UA.created_at', 'UA.updated_at', 'F.comment', 'appointments.status', 'UA.assign_no')
                ->leftJoin('user_auditions AS UA', 'appointments.id', '=', 'UA.appointment_id')
                ->leftJoin('feedbacks AS F', 'appointments.id', '=', 'F.appointment_id')
                ->where('UA.user_id', $this->getUserLogging())
                ->where('F.user_id', $this->getUserLogging())
                // ->where('F.favorite', 1)
                // ->where('appointments.status', 1)
                ->get()->sortByDesc('created_at');


            // $dataAuditions = $data->where('type', '=', '3')->sortByDesc('created_at');
            // if ($dataAuditions->count() > 0) {
            //     $dataResponse = ['data' => UserAuditionsResource::collection($dataAuditions)];
            // } else {
            //     $dataResponse = ['data' => []];
            // }

            $data = $userAuditions->getByParam('user_id', $this->getUserLogging());

            // $dataAuditions = $data->where('type', '=', '3')->sortByDesc('created_at');
            // if ($dataAuditions->count() > 0) {
            //     $dataResponse = ['data' => UserAuditionsResource::collection($dataAuditions)];
            // } else {
            //     $dataResponse = ['data' => []];
            // }

            if ($data->count() > 0) {
                $dataResponse = ['data' => UserAuditionsResource::collection($data)];
            } else {
                $dataResponse = ['data' => []];
            }

            return response()->json($dataResponse, 200);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Not Found Data'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function getUpcomingDet(Request $request)
    {
        try {
            $userAuditions = new UserAuditionsRepository(new UserAuditions());

            $data = $userAuditions->find($request->id);

            return response()->json(['data' => new AuditionsDetResponse($data)], 200);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function getUpcomingMangement()
    {
        try {
            $this->collection = new Collection();
            $dataAuditions = new AuditionRepository(new Auditions());
            $data = $dataAuditions->findbyparam('user_id', $this->getUserLogging());

            $dataContributors = new AuditionContributorsRepository(new AuditionContributors());
            $dataContri = $dataContributors->findbyparam('user_id', $this->getUserLogging())->where('status', '=', 1)->sortByDesc('created_at');

            $dataContri->each(function ($item) {
                $auditionRepo = new AuditionRepository(new Auditions());
                $audiData = $auditionRepo->find($item['auditions_id']);
                if ($audiData->status != 2) {
                    $this->collection->push($audiData);
                }
            });


            $data->each(function ($item) {
                if ($item['status'] != 2 && $item['user_id'] === $this->getUserLogging()) {
                    $this->collection->push($item);
                }
            });

            if ($this->collection->count() > 0) {
                $dataResponse = ['data' => AuditionResponse::collection($this->collection->sortByDesc('created_at')->unique())];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }


            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function getPassedMangement()
    {
        try {
            $data = $this->getPassedAuditions();

            if ($data->count() > 0) {
                $dataResponse = ['data' => AuditionResponse::collection($data)];
                $code = 200;
            } else {
                throw new Exception('Not Found Data');
            }


            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function getRequested()
    {
        try {
            $userAuditions = new UserAuditionsRepository(new UserAuditions());

            $data = $userAuditions->getByParam('user_id', $this->getUserLogging());

            $dataAuditions = $data->where('type', '=', '2')->sortByDesc('created_at');
            if ($dataAuditions->count() > 0) {
                $dataResponse = ['data' => UserAuditionsResource::collection($dataAuditions)];
            } else {
                $dataResponse = ['data' => []];
            }

            return response()->json($dataResponse, 200);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function openAudition(Request $request)
    {
        try {
            $auditionRepo = new AuditionRepository(new Auditions());
            $result = $auditionRepo->find($request->id)->update([
                'status' => 1,
            ]);

            if ($result) {
                $dataResponse = ['data' => ['status' => 1]];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'error to open audition'];
                $code = 406;
            }


            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.error_to_open_audition')], 406);
            // return response()->json(['data' => 'error to open audition'], 406);
        }
    }

    public function closeAudition(Request $request)
    {
        try {
            $auditionRepo = new AuditionRepository(new Auditions());
            $result = $auditionRepo->find($request->id);
            $result->update([
                'status' => 2,
            ]);
            if ($result) {
                $repoAppointment = new AppointmentRepository(new Appointments());
                $dataAppointments = $repoAppointment->findbyparam('auditions_id', $result->id);
                $appointmentToClose = $dataAppointments->get()->pluck('id');

                $dataAppointments->update(['status' => false]);

                $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                $dataUserAuditions = $repoUserAuditions->all()->whereIn('appointment_id', $appointmentToClose);
                if ($dataUserAuditions->count() > 0) {
                    $dataUserAuditions->each(function ($element) {
                        $element->update(['type' => 3]);
                    });
                }
                $dataResponse = ['data' => ['status' => 2]];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'error to close audition'];
                $code = 406;
            }


            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.error_to_close_audition')], 406);
            // return response()->json(['data' => 'error to close audition'], 406);
        }
    }

    public function getUserProfile(Request $request)
    {
        try {
            // print_r($request->appointment_id);
            $userRepo = new UserRepository(new User());
            $data = $userRepo->find($request->id);
            if ($data) {
                $dataResponse = ['data' => new ProfileResource($data, $request->appointment_id)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Not Found Data'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function saveVideo(Request $request)
    {
        try {
            $videoRepo = new AuditionVideosRepository(new AuditionVideos());
            $toData = $videoRepo->findbyparam('slot_id', $request->slot_id);
            if ($toData->count() > 0) {
                $dataResponse = ['data' => 'Video already saved'];
                $code = 406;
            } else {
                // ==============================
                // insert batch in audition videos

                $repo = new AppointmentRepository(new Appointments());
                $apppointment_data = $repo->find($request->appointment_id);
                if ($apppointment_data->is_group_open) {

                    $user_ids_of_group_member = DB::table('user_auditions')
                        ->where('group_no', $apppointment_data->group_no)
                        ->where('appointment_id', $request->appointment_id)
                        ->pluck('user_id');

                    $data_to_add = array();
                    foreach ($user_ids_of_group_member as $user_id) {
                        $data_to_add[] = array(
                            'name' => $request->name,
                            'user_id' => $user_id,
                            'appointment_id' => $request->appointment_id,
                            'url' => $request->url,
                            'contributors_id' => $this->getUserLogging(),
                            'slot_id' => $request->slot_id
                        );
                    }
                    $data = AuditionVideos::insert($data_to_add);
                    if ($data) {
                        $dataResponse = ['data' => trans('messages.video_saved')];
                        $code = 200;
                    } else {
                        $dataResponse = ['data' => trans('messages.video_not_saved')];
                        $code = 406;
                    }
                } else {
                    $data = $videoRepo->create([
                        'name' => $request->name,
                        'user_id' => $request->performer,
                        'appointment_id' => $request->appointment_id,
                        'url' => $request->url,
                        'contributors_id' => $this->getUserLogging(),
                        'slot_id' => $request->slot_id,
                    ]);
                    if (isset($data->id)) {
                        $dataResponse = ['data' => trans('messages.video_saved')];
                        $code = 200;
                    } else {
                        $dataResponse = ['data' => trans('messages.video_not_saved')];
                        $code = 406;
                    }
                }
            }
            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function deleteVideo(Request $request)
    {

        $isOnline = DB::table('auditions')
            ->where('id', $request->audition_id)
            ->value('online');
        try {

            if ($isOnline) {
                $videoRepo = new OnlineMediaAuditionsRepository(new OnlineMediaAudition());
                $delvideo = $videoRepo->find($request->id);
                $data = $delvideo->delete();
            } else {
                $videoRepo = new AuditionVideosRepository(new AuditionVideos());
                $delvideo = $videoRepo->find($request->id);
                $data = $delvideo->delete();
            }

            if ($data) {
                $dataResponse = ['data' => 'Video deleted'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Video not deleted'];
                $code = 406;
            }

            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function listVideos(Request $request)
    {
        try {
            $videoRepo = new OnlineMediaAuditionsRepository(new OnlineMediaAudition());
            $data = $videoRepo->findbyparam('appointment_id', $request->id)->get();
            if ($data->count() > 0) {
                $dataResponse = ['data' => AuditionVideosResource::collection($data)];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }
            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function saveContract(Request $request)
    {
        try {
            $contractRepo = new AuditionContractRepository(new AuditionContract());
            $data = $contractRepo->create([
                'user_id' => $request->performer,
                'auditions_id' => $request->audition,
                'url' => $request->url,
            ]);
            if (isset($data->id)) {
                $dataResponse = ['message' => 'Contract saved', 'data' => $data];
                $code = 200;
            } else {
                $dataResponse = ['message' => 'Contract Not saved', 'data' => []];
                $code = 406;
            }


            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.not_processable'), 'data' => []], 406);
            // return response()->json(['message' => 'Not processable', 'data' => []], 406);
        }
    }

    public function deleteContract(Request $request)
    {
        try {
            $contractRepo = new AuditionContractRepository(new AuditionContract());
            $del = $contractRepo->find($request->id);
            $data = $del->delete();
            if ($data) {
                $dataResponse = ['data' => 'Contract deleted'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Contract not deleted'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function getContact(Request $request)
    {
        try {
            $contractRepo = new AuditionContractRepository(new AuditionContract());
            $data = $contractRepo->findbyparam('auditions_id', $request->audition_id)->get();
            if ($data->where('user_id', $request->user_id)->count() > 0) {
                $dataResponse = ['data' => new ContractResponse($data[0])];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }
            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Not Found Data'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function alertSlotsEmpty($id)
    {
        try {
            $available = true;
            $repoAppointments = new AppointmentRepository(new Appointments());
            $repoUserSlots = new UserSlotsRepository(new UserSlots());

            $slotsAppointment = $repoAppointments->find($id);
            $countSlotsAppointment = $slotsAppointment->slot ?? collect([]);
            $userSlots = $repoUserSlots->findbyparam('appointment_id', $id);
            $countUserSlots = $userSlots ?? collect([]);
            $a = $countUserSlots->count();
            $b = $countSlotsAppointment->count();
            if ($a >= $b) {
                $available = false;
            }

            return $available;
        } catch (\Throwable $exception) {
            $this->log->error($exception->getMessage());
            return false;
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return false;
        }
    }

    public function updateDocument(Request $request)
    {
        try {
            $repoResource = new ResourcesRepository(new Resources());
            $resourceData = $repoResource->find($request->id);


            $data = [
                'shareable' => $request->shareable
            ];

            $resource = $resourceData->update($data);

            if ($resource) {
                $dataResponse = 'Document update';
                $code = 200;
            } else {
                $dataResponse = 'Error';
                $code = 422;
            }

            return response()->json(['data' => $dataResponse], $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Error to process'], 406);
            return response()->json(['data' => trans('messages.not_processable')], 406);
        }
    }

    public function reorderAppointmentTimes(Request $request)
    {
        try {


            $repoApp = new AppointmentRepository(new Appointments());
            $appoiment = $repoApp->find($request->id);

            foreach ($request->slots as $slot) {

                $userSlotRepo = new UserSlotsRepository(new  UserSlots);
                $userSlot = $userSlotRepo->findbyparam('user_id', $slot['user_id'])->first();

                $update = $userSlot->update(['slots_id' => $slot['slot_id']]);

                $userRepo = new UserRepository(new User());
                $newUserSlot = $userSlotRepo->findbyparam('user_id', $slot['user_id'])->first();

                $user = $userRepo->find($slot['user_id']);

                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointment = $appointmentRepo->find($newUserSlot->appointment_id);

                $slotRepo = new SlotsRepository(new Slots());
                $slot = $slotRepo->find($slot['slot_id']);

                $dataMail = [
                    'name' => $user->details->first_name,
                    'audition_title' => $appointment->auditions->title,
                    'slot_time' => $slot->time
                ];

                $mail = new SendMail();
                $mail->sendPerformance($user->email, $dataMail);

                $this->saveReorderAppointmentTimesNotificationToUser($user, $audition);

                $this->sendReorderAppointmentTimesNotification($audition);
            }

            if ($userSlotRepo) {
                $this->log->info(UserSlots::all());
                $dataResponse = 'success';
                $code = 200;
            } else {
                $dataResponse = 'Error';
                $code = 422;
            }

            return response()->json(['data' => $dataResponse], $code);
        } catch (\Exception $exception) {
            $this->log->error($exception);
            return response()->json(['data' => trans('messages.unprocesable_entity')], 422);
            // return response()->json(['data' => 'Unprocesable Entity'], 422);
        }
    }

    public function sendReorderAppointmentTimesNotification($audition): void
    {
        try {
            $userRepo = new UserRepository(new User);

            $audition->user_auditions->each(function ($user_audition) use ($audition) {
                $user = $userRepo->find($user_audition['user_id']);
                $this->pushNotifications(
                    'Your appointment to audition ' . '* ' . $audition->title . ' *' . ' has been moved',
                    $user_auditions
                );
            });
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function saveReorderAppointmentTimesNotificationToUser($user, $audition): void
    {
        try {
            if ($user instanceof User) {
                $user->notification_history()->create([
                    'title' => $audition->title,
                    'code' => 'appointment_reorder',
                    'status' => 'unread',
                    'message' => 'Your appointment to audition ' . '* ' . $audition->title . ' *' . ' has been moved'
                ]);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function bannedAuditions(Request $request)
    {
        try {
            $repoAudition = new AuditionRepository(new Auditions());
            $audition = $repoAudition->find($request->audition_id);


            $data = [
                'banned' => 'pending'
            ];

            $resource = $audition->update($data);

            if ($resource) {
                return response()->json(['data' => trans('messages.audition_banned')], 200);
                // $dataResponse = 'Audition Banned';
                // $code = 200;
            } else {
                return response()->json(['data' => trans('messages.error')], 422);
                // $dataResponse = 'Error';
                // $code = 422;
            }
            // return response()->json(['data' => $dataResponse], $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Error to process'], 406);
        }
    }

    public function getPassedAuditions()
    {
        $collection = new Collection();
        $dataAuditions = new AuditionRepository(new Auditions());
        $data = $dataAuditions->findbyparam('user_id', $this->getUserLogging());

        $dataContributors = new AuditionContributorsRepository(new AuditionContributors());
        $dataContri = $dataContributors->findbyparam('user_id', $this->getUserLogging())->where('status', '=', 1)->sortByDesc('created_at');

        $dataContri->each(function ($item) use ($collection) {
            $auditionRepo = new AuditionRepository(new Auditions());
            $audiData = $auditionRepo->find($item['auditions_id']);
            if ($audiData->status == 2) {
                $collection->push($audiData);
            }
        });


        $data->each(function ($item) use ($collection) {
            if ($item['status'] == 2) {
                $collection->push($item);
            }
        });
        return $collection;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerNotOnline(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (!$this->alertSlotsEmpty($request->appointment)) {
                throw new Exception('all the spaces of this audition have been reserved', 10);
            }
            $userAuditions = new UserAuditionsRepository(new UserAuditions());
            $data = [
                'user_id' => $this->getUserLogging(),
                'appointment_id' => $request->appointment,
                'rol_id' => $request->rol,
                'type' => $request->type,
            ];
            $userAudi = new UserAuditions();
            $datacompare = $userAudi->where('user_id', '=', $data['user_id'])
                ->where('appointment_id', '=', $data['appointment_id'])
                ->where('rol_id', '=', $data['rol_id'])
                ->get();
            if ($datacompare->count() > 0) {
                return response()->json(['data' => trans('messages.you_already_registered')], 406);
                // return response()->json(['data' => 'You already registered'], 406);
            } else {
                $data = $userAuditions->create($data);
                if ($request->type === 2) {
                    $user = new UserManagerRepository(new UserManager());
                    $userData = new UserRepository(new User());
                    $detailData = $userData->find($this->getUserLogging());
                    $userDetailname = $detailData->details->first_name . " " . is_null($detailData->details->last_name) ? '' : $detailData->details->last_name;
                    $userManager = $user->findbyparam('user_id', $this->getUserLogging());
                    $appoinmetRepo = new AppointmentRepository(new Appointments());
                    $auditionsId = $appoinmetRepo->find($request->appointment)->auditions->id;
                    $auditionRepo = new AuditionRepository(new Auditions());
                    $audition = $auditionRepo->find($auditionsId);
                    $dataMail = ['name' => $userDetailname, 'audition' => $audition->title, 'url' => $audition->url];
                    if (isset($userManager->email) !== null && isset($userManager->notifications)) {
                        $mail = new SendMail();
                        $mail->sendManager($userManager->email, $dataMail);
                    }

                    $this->sendSaveAuditionNotificationToUser($detailData, $audition);

                    $this->saveAuditionNotificationToUser($detailData, $audition);
                } else {
                    $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                    $dataSlotRepo->create([
                        'user_id' => $this->getUserLogging(),
                        'appointment_id' => $request->appointment,
                        'slots_id' => null,
                        'roles_id' => $request->rol,
                        'status' => 1
                    ]);
                }
            }
            return response()->json(['data' => trans('messages.audition_saved')], 201);
            // return response()->json(['data' => 'Audition Saved'], 201);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            $message = $exception->getMessage();
            $code = 406;
            if ($exception->getCode() !== 10) {
                $message = 'Not Saved';
            }

            return response()->json(['error' => $message], $code);
        }
    }

    public function registerOnline(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $userAuditions = new UserAuditionsRepository(new UserAuditions());
            $data = [
                'user_id' => $this->getUserLogging(),
                'appointment_id' => $request->appointment,
                'rol_id' => $request->rol,
                'type' => $request->type,
            ];
            $userAudi = new UserAuditions();
            $datacompare = $userAudi->where('user_id', '=', $data['user_id'])
                ->where('appointment_id', '=', $data['appointment_id'])
                ->where('rol_id', '=', $data['rol_id'])
                ->get();
            if ($datacompare->count() > 0) {
                return response()->json(['data' => trans('messages.you_already_registered')], 406);
                // return response()->json(['data' => 'You already registered'], 406);
            } else {
                $data = $userAuditions->create($data);
                $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                $dataSlotRepo->create([
                    'user_id' => $this->getUserLogging(),
                    'appointment_id' => $request->appointment,
                    'slots_id' => factory(Slots::class)->create([
                        'appointment_id' => $request->appointment,
                        'time' => "00:00",
                        'status' => false,
                    ])->id,
                    'roles_id' => $request->rol,
                    'status' => 2
                ]);
            }
            return response()->json(['data' => trans('messages.audition_saved')], 201);
            // return response()->json(['data' => 'Audition Saved'], 201);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            $message = $exception->getMessage();
            $code = 406;
            return response()->json(['error' => $message], $code);
        }
    }

    public function createGroup(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);

            if ($data->is_group_open) {
                return response()->json(['message' => trans('messages.group_already_open'), 'data' => []], 409);
            }
            // check if user has uploaded video before or not
            $videoRepo = new AuditionVideos();
            $videoData = $videoRepo->whereIn('user_id', $request->user_ids)
                ->where('appointment_id', $request->appointment_id)
                ->groupBy('user_id')
                ->pluck('user_id');

            if ($videoData->count() > 0) {
                $userRepo = new UserDetailsRepository(new UserDetails());
                $user_names = $userRepo->all()->whereIn('user_id', $videoData)->pluck('first_name')->toArray();
                $names = implode(", ", $user_names);
                return response()->json(['message' => trans('messages.user_already_uploaded_video', ['user' => $names]), 'data' => []], 409);
                // return response()->json(['message' => trans('messages.already_uploaded_video'), 'data' => []], 409);
            }
            // Update Appointments
            $group_no = $data->group_no + 1;
            $update = $data->update([
                "group_no" => $group_no,
                'is_group_open' => 1
            ]);
            if ($update) {
                // Update User Auditions
                $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                $dataUserAuditions = $repoUserAuditions->all()->whereIn('user_id', $request->user_ids)
                    ->where('appointment_id', $request->appointment_id);
                if ($dataUserAuditions->count() > 0) {
                    $dataUserAuditions->each(function ($element) use ($group_no) {
                        $element->update(['group_no' => $group_no]);
                    });
                }
                return response()->json(['message' => trans('messages.group_creaed'), 'data' => []], 200);
            } else {
                return response()->json(['message' => trans('messages.group_not_creaed'), 'data' => []], 400);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.group_not_creaed'), 'data' => []], 400);
        }
    }

    public function checkGroupStatus(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);

            $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
            $groupUserIds = $repoUserAuditions->getByParam('group_no', $data->group_no)->pluck('user_id');

            $userRepo = new UserDetailsRepository(new UserDetails());

            //    $user_data = DB::table('user_details')->whereIn('user_id', $groupUserIds)->get();

            $user_data = DB::table('user_details AS UD')
                ->select(
                    'UD.id',
                    'UD.first_name',
                    'UD.last_name',
                    'UD.url',
                    'UD.address',
                    'UD.city',
                    'UD.state',
                    'UD.birth',
                    'UD.user_id',
                    'UA.group_no',
                    'UA.assign_no',
                    'UA.assign_no_by',
                    'UA.slot_id',
                    'UA.rol_id',
                    'UA.appointment_id'
                )
                ->Join('user_auditions AS UA', 'UD.user_id', '=', 'UA.user_id')
                ->whereIn('UD.user_id', $groupUserIds)
                ->where('UA.group_no', $data->group_no)
                ->get();


            if ($data->is_group_open) {
                return response()->json(['message' => trans('messages.group_open'), 'data' => $user_data], 200);
            } else {
                return response()->json(['message' => trans('messages.group_close'), 'data' => false], 200);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.data_not_found'), 'data' => false], 404);
        }
    }

    public function closeGroup(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);

            if (!$data->is_group_open) {
                return response()->json(['message' => trans('messages.group_already_close')], 409);
            }

            $update = $data->update(['is_group_open' => 0]);
            if ($update) {
                return response()->json(['message' => trans('messages.group_close_success')], 200);
            } else {
                return response()->json(['message' => trans('messages.group_not_closed')], 400);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.not_processable'), 'data' => false], 404);
        }
    }

    public function assignNumber(Request $request)
    {
        try {
            $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
            $dataUserAuditions = $repoUserAuditions->findbyparams([
                'user_id' => $request->user_id,
                'appointment_id' => $request->appointment_id
            ]);

            if ($dataUserAuditions->count() > 0) {
                $userAuditionData = $dataUserAuditions->first();
                if ($userAuditionData->assign_no != NULL) {
                    return response()->json(['message' => trans('messages.number_already_assigned'), 'data' => []], 409);
                }

                // Check if Number is unique or not
                $list_of_numbers = $repoUserAuditions->all()->where('assign_no', $request->assign_no);
                if ($list_of_numbers->count() > 0) {
                    return response()->json(['message' => trans('messages.number_already_used'), 'data' => []], 409);
                }

                // Update User Auditions
                $updateUserAudi = $dataUserAuditions->update([
                    'assign_no' => $request->assign_no,
                    'assign_no_by' => $this->getUserLogging()
                ]);

                if ($updateUserAudi) {
                    return response()->json(['message' => trans('messages.assign_number_created'), 'data' => []], 200);
                } else {
                    return response()->json(['message' => trans('messages.server_error'), 'data' => []], 500);
                }
            } else {
                return response()->json(['data' => trans('messages.data_not_found')], 404);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.something_went_wrong'), 'data' => []], 400);
        }
    }
}
