<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\MonitorRepository;
use App\Models\Monitor;
use Illuminate\Http\Request;

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
                $this->sendNotification();
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

    public function sendNotification(){
        $this->log->info("ENVIAR NOTIFICACION A REGISTRADOS ");
    }
}
