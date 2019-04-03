<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\ManageDates;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Resources\AppointmentResource;
use App\Models\UserSlots;
use Illuminate\Http\Request;

class AppoinmentAuditionsController extends Controller
{
    protected $log;


    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();

    }

    public function store(Request $request){
        try {
            $dataRepo = new UserSlotsRepository(new UserSlots());
           $createData=  $dataRepo->create([
                'user_id' => $request->user,
                'auditions_id'=>$request->auditions,
                'slots_id' => $request->slot,
            ]);

            $this->sendPushNotification($request->user_id);
            $dataResponse = new AppointmentResource($createData);
            return response()->json(['data'=>$dataResponse],200);
        }catch (\Exception $exception){
            return response()->json(['data'=>'Appointment not assigned'],406);
        }
    }

    public function show(Request $request){
        try {
            $dataRepo = new UserSlotsRepository(new UserSlots());
            $data = $dataRepo->findbyparam('auditions_id',$request->audition);


            $dataResponse = AppointmentResource::collection($data);
            return response()->json(['data'=>$dataResponse],200);
        }catch (\Exception $exception){
            return response()->json(['data'=>'Data Not Found'],404);
        }
    }



    public function sendPushNotification($user_id)
    {
        $this->log->info("ENVIAR PUSH A USER" . $user_id);
    }
}
