<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Controllers\Utils\Notifications as SendNotifications;

use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionVideosRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserManagerRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Repositories\ResourcesRepository;

use App\Http\Resources\AuditionResponse;
use App\Http\Resources\AuditionsDetResponse;
use App\Http\Resources\AuditionVideosResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserAuditionsResource;

use App\Models\Appointments;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\AuditionVideos;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use App\Models\UserSlots;
use App\Models\Resources;

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

        try {
            if(!$this->alertSlotsEmpty($request->auditions)){
                throw new Exception('all the spaces of this audition have been reserved',10);
            }
            $userAuditions = new UserAuditionsRepository(new UserAuditions());
            $data = [
                'user_id' => $this->getUserLogging(),
                'auditions_id' => $request->auditions,
                'rol_id' => $request->rol,
                'type' => $request->type,
            ];
            $userAudi = new UserAuditions();
            $datacompare = $userAudi->where('user_id', '=', $data['user_id'])
                ->where('auditions_id', '=', $data['auditions_id'])
                ->where('rol_id', '=', $data['rol_id'])
                ->get();
            if ($datacompare->count() > 0) {
                return response()->json(['data' => 'You already registered'], 406);
            } else {
                $data = $userAuditions->create($data);
               if ($request->type === 2) {
                    $user = new UserManagerRepository(new UserManager());
                    $userData = new UserRepository(new User());
                    $detailData = $userData->find($this->getUserLogging());
                    $userDetailname = $detailData->details->first_name . " " . $detailData->details->last_name;
                    $userManager = $user->findbyparam('user_id', $this->getUserLogging());
                    $auditionRepo = new AuditionRepository(new Auditions());
                    $audition = $auditionRepo->find($request->auditions);
                    $dataMail = ['name' => $userDetailname, 'audition' => $audition->title, 'url' => $audition->url];
                    if (isset($userManager->email) !== null && isset($userManager->notifications)) {
                        $mail = new SendMail();
                        $mail->sendManager($userManager->email, $dataMail);
                    }
                    $this->sendPushNotification(
                        $audition,
                        'upcoming_audition',
                        $detailData
                    );
                }else{
                 $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                $dataSlotRepo->create([
                    'user_id' => $this->getUserLogging(),
                    'auditions_id' => $request->auditions,
                    'slots_id' => null,
                    'roles_id' => $request->rol,
                    'status' => 1
                ]);

                }
            }
            return response()->json(['data' => 'Audition Saved'], 201);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            $message = $exception->getMessage();
            $code = 406;
            if($exception->getCode() !== 10){
                $message = 'Not Saved';
            }

            return response()->json(['error' => $message], $code);
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
                    'auditions_id' => $dataAuditionsUser->auditions_id,
                    'slots_id' => $dataSlot,
                    'roles_id' => $dataAuditionsUser->rol_id,
                    'status'=>1
                ]);

            $updateAudi =$dataAuditionsUser ->update([
                'type' => '1',
                'slot_id' => $dataSlot
            ]);
            if ($updateAudi) {
                $code = 200;
                $responseData = 'Audition update';
                DB::commit();
            } else {
                $responseData = 'Audition not update';
                $code = 406;
                DB::rollBack();
            }
            return response()->json(['data' => $responseData], $code);
        } catch (Exception $exception) {
            DB::rollBack();
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Audition not update'], 406);
        }
    }

    public function getUpcoming()
    {
        try {
            $userAuditions = new UserAuditionsRepository(new UserAuditions());

            $data = $userAuditions->getByParam('user_id', $this->getUserLogging());

            $dataAuditions = $data->where('type', '=', '1')->sortByDesc('created_at');
            if ($dataAuditions->count() > 0) {
                $dataResponse = ['data' => UserAuditionsResource::collection($dataAuditions)];
            } else {
                $dataResponse = ['data' => []];
            }

            return response()->json($dataResponse, 200);

        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function getPassed()
    {
        try {
            $userAuditions = new UserAuditionsRepository(new UserAuditions());

            $data = $userAuditions->getByParam('user_id', $this->getUserLogging());

            $dataAuditions = $data->where('type', '=', '3')->sortByDesc('created_at');
            if ($dataAuditions->count() > 0) {
                $dataResponse = ['data' => UserAuditionsResource::collection($dataAuditions)];
            } else {
                $dataResponse = ['data' => []];
            }

            return response()->json($dataResponse, 200);

        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
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
            return response()->json(['data' => 'Not Found Data'], 404);
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
                $dataResponse = ['data' => AuditionResponse::collection($this->collection->unique())];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }


            return response()->json($dataResponse, $code);

        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function getPassedMangement()
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
                if ($audiData->status == 2) {
                    $this->collection->push($audiData);
                }
            });


            $data->each(function ($item) {
                if ($item['status'] == 2) {
                    $this->collection->push($item);
                }
            });

            if ($this->collection->count() > 0) {
                $dataResponse = ['data' => AuditionResponse::collection($this->collection)];
                $code = 200;
            } else {
             throw new Exception('Not Found Data');
            }


            return response()->json($dataResponse, $code);

        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
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
            return response()->json(['data' => 'Not Found Data'], 404);
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
            return response()->json(['data' => 'error to open audition'], 406);
        }
    }

    public function closeAudition(Request $request)
    {
        try {
            $auditionRepo = new AuditionRepository(new Auditions());
            $result = $auditionRepo->find($request->id)->update([
                'status' => 2,
            ]);

            if ($result) {
                $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                $dataUserAuditions = $repoUserAuditions->getByParam('auditions_id', $request->id);
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
            return response()->json(['data' => 'error to close audition'], 406);
        }
    }

    public function getUserProfile(Request $request)
    {
        try {
            $userRepo = new UserRepository(new User());
            $data = $userRepo->find($request->id);
            if ($data) {


                $dataResponse = ['data' => new ProfileResource($data)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);


        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
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
                $data = $videoRepo->create([
                    'user_id' => $request->performer,
                    'auditions_id' => $request->audition,
                    'url' => $request->url,
                    'contributors_id' => $this->getUserLogging(),
                    'slot_id' => $request->slot_id,
                ]);
                if (isset($data->id)) {
                    $dataResponse = ['data' => 'Video saved'];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => 'Video not saved'];
                    $code = 406;
                }

            }
            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function deleteVideo(Request $request)
    {
        try {
            $videoRepo = new AuditionVideosRepository(new AuditionVideos());
            $delvideo = $videoRepo->find($request->id);
            $data = $delvideo->delete();
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
            return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function listVideos(Request $request)
    {
        try {
            $videoRepo = new AuditionVideosRepository(new AuditionVideos());
            $data = $videoRepo->findbyparam('auditions_id', $request->id)->get();
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
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }


    public function alertSlotsEmpty($audition){
        try {
            $available = true;
            $repoAppointmenst = new AppointmentRepository(new Appointments());
            $repoUserSlots = new UserSlotsRepository(new UserSlots());

            $slotsAppointment = $repoAppointmenst->findbyparam('auditions_id', $audition);
            $countSlotsAppointment= $slotsAppointment->slot ?? collect([]);
            $userSlots = $repoUserSlots->findbyparam('auditions_id', $audition);
            $countUserSlots = $userSlots ?? collect([]);

            if ($countUserSlots->count() >= $countSlotsAppointment->count()) {
                $available = false;
            }

            return $available;
        }catch (\Throwable $exception){
            $this->log->error($exception->getMessage());
            return false;
        }
        catch (Exception $exception){
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
            return response()->json(['data' => 'Error to process'], 406);
        }
    }

    public function reorderAppointmentTimes(Request $request)
    {
        try {
            $repoApp = new AppointmentRepository(new Appointments());
            $appoiment = $repoApp->find($request->id);
            $this->log->info($request);

            foreach ($appoiment->slot as $slot) {
                $userSlotRepo = new UserSlotsRepository(new  UserSlots);   
                $userSlotRepo->update(['slots_id' => $slot['slot_id']]);
                
            }

            $this->log->info('SLOTS',$appoiment->slot);

            if ($userSlotRepo) {
                $dataResponse =  'success' ;
                $code = 200;
            } else {
                $dataResponse = 'Error';
                $code = 422;
            }

            return response()->json(['data' => $dataResponse], $code);

        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Unprocesable Entity'], 422);
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
                $dataResponse = 'Audition Banned';
                $code = 200;
            } else {
                $dataResponse = 'Error';
                $code = 422;
            }

            return response()->json(['data' => $dataResponse], $code);

        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Error to process'], 406);
        }
    }
    

}
