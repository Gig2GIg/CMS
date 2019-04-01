<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserManagerRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\UserAuditionsResource;
use App\Models\User;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use App\Models\UserManager;
use Exception;
use Illuminate\Http\Request;

class AuditionManagementController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function saveAudition(Request $request)
    {

        try {
            $userAuditions = new UserAuditionsRepository(new UserAuditions());
            $data = [
                'user_id' => $this->getUserLogging(),
                'auditions_id' => $request->auditions,
                'rol_id' => $request->rol,
                'type' => $request->type
            ];

            $data = $userAuditions->create($data);
            if($request->type === 2){
                $user = new UserManagerRepository(new UserManager());
                $userData = new UserRepository(new User());
                $detailData = $userData->find($this->getUserLogging());
                $userDetailname = $detailData->details->first_name." ". $detailData->details->last_name;
                $userManager = $user->findbyparam('user_id',$this->getUserLogging());

                if($userManager->email !== null && $userManager->notifications){
                    $mail = new SendMail();
                    $mail->sendManager($userManager->email,$userDetailname);
                }
            }
            return response()->json(['data' => 'Audition Saved'], 201);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Not Saved'], 500);
        }

    }

    public function getUpcoming(){
        try{
            $userAuditions = new UserAuditionsRepository(new UserAuditions());

            $data = $userAuditions->getByParam('user_id',$this->getUserLogging());

            $dataResponse = $data->where('type','=','1');

            return response()->json(['data'=>UserAuditionsResource::collection($dataResponse)],200);

        }catch (Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function getRequested(){
        try{
            $userAuditions = new UserAuditionsRepository(new UserAuditions());

            $data = $userAuditions->getByParam('user_id',$this->getUserLogging());

            $dataResponse = $data->where('type','=','2');

            return response()->json(['data'=>UserAuditionsResource::collection($dataResponse)],200);

        }catch (Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }
}
