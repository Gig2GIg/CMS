<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Controllers\Utils\Notifications as SendNotifications;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionVideosRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserManagerRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Resources\AuditionResponse;
use App\Http\Resources\AuditionsDetResponse;
use App\Http\Resources\AuditionVideosResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserAuditionsResource;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\AuditionVideos;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use App\Models\UserSlots;
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
            $userAuditions = new UserAuditionsRepository(new UserAuditions());
            $data = [
                'user_id' => $this->getUserLogging(),
                'auditions_id' => $request->auditions,
                'rol_id' => $request->rol,
                'type' => $request->type
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

                    if (isset($userManager->email) !== null && isset($userManager->notifications)) {
                        $mail = new SendMail();
                        $mail->sendManager($userManager->email, $userDetailname);
                    }

                    $auditionRepo = new AuditionRepository(new Auditions());
                    $audition = $auditionRepo->find($request->auditions);

                    $this->sendPushNotification(
                        $audition,
                        'upcoming_audition',
                        $detailData
                    );
                }
            }
            return response()->json(['data' => 'Audition Saved'], 201);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Not Saved'], 500);
        }

    }

    public function updateAudition(Request $request)
    {
        try {
            DB::beginTransaction();
            if (isset($request->slot)) {
                $dataRepo = new UserSlotsRepository(new UserSlots());
                $dataRepo->create([
                    'user_id' => $this->getUserLogging(),
                    'auditions_id' => $request->slot['auditions'],
                    'slots_id' => $request->slot['slot'],
                    'roles_id'=>$request->slot['rol']
                ]);
            }
            $dataRepoAuditionUser = new UserAuditionsRepository(new UserAuditions());
            $updateAudi = $dataRepoAuditionUser->find($request->id)->update(['type' => '1']);
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

            $dataResponse = $data->where('type', '=', '1')->sortByDesc('created_at');

            return response()->json(['data' => UserAuditionsResource::collection($dataResponse)], 200);

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
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
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
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
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

            $dataResponse = $data->where('type', '=', '2')->sortByDesc('created_at');

            return response()->json(['data' => UserAuditionsResource::collection($dataResponse)], 200);

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
            $data = $videoRepo->create([
                'user_id' => $request->performer,
                'auditions_id' => $request->audition,
                'url' => $request->url,
                'contributors_id' => $this->getUserLogging(),
            ]);
            if (isset($data->id)) {
                $dataResponse = ['data' => 'Video saved'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Video not saved'];
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
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }
}
