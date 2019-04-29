<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\MonitorRepository;
use App\Models\Monitor;
use App\Models\Auditions;
use App\Models\Notifications\Notification;
use Illuminate\Http\Request;
use App\Http\Repositories\AuditionRepository;

use App\Http\Repositories\Notification\NotificationRepository;
use Illuminate\Support\Str;

class MonitorManagerController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function create(Request $request){
        try {
            $repo = new MonitorRepository(new Monitor());
            $data = $repo->create([
                'auditions_id' => $request->audition,
                'title' => $request->title,
                'time' => $request->time
            ]);
            if ($data->id) {
                $dataResponse = ['data' => 'Update Publised'];
                $code = 201;
                
                $auditionRepo = new AuditionRepository(new Auditions());
                $audition = $auditionRepo->find($request->audition);
               
                $this->createNotification($audition, $request->title);
                
                $this->sendPushNotification(
                    $audition,
                    'custom',
                    null,
                    $request->title
                );
            } else {
                $dataResponse = ['data' => 'Update Not Publised'];
                $code = 406;
            }
       
            return response()->json($dataResponse, $code);
        }catch (\Exception $exception){
            
            $this->log->error($exception->getMessage());
            return response()->json( ['data'=>'Update Not Publised'],406);
        }
    }

    public function list(Request $request){
        try{
        $repo = new MonitorRepository(new Monitor());
        $data = $repo->findbyparam('auditions_id',$request->id)->get();

        if($data->count() > 0){
            $dataResponse = ['data'=>$data];
            $code = 200;
        }else{
            $dataResponse = ['data'=>'Data Not Found'];
            $code = 404;
        }
        return response()->json($dataResponse,$code);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json( ['data'=>'Data Not Found'],404);
        }
    }

    public function listNotificationsCreate(Request $request){
        try{
            $repo = new MonitorRepository(new Monitor());
            $data = $repo->findbyparam('auditions_id',$request->id)->get()->unique('title');

            if($data->count() > 0){
                $dataResponse = ['data'=>$data];
                $code = 200;
            }else{
                $dataResponse = ['data'=>'Data Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse,$code);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json( ['data'=>'Data Not Found'],404);
        }
    }



    public function createNotification($audition, $title): void
    {
        try {    
            $notificationData = [
                'title' => $title,
                'code' => Str::random(12),
                'type' =>  'custom',
                'notificationable_type' =>  'auditions',
                'notificationable_id' => $audition->id
            ];
            
            if ($audition !== null) {

                $notificationRepo = new NotificationRepository(new Notification()); 
                $m = $notificationRepo->create($notificationData);
                
            }
        }catch (NotFoundException $exception){
                $this->log->error($exception->getMessage());
            }

        }
}
