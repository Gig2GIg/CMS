<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PerformerRepository;
use App\Http\Resources\PerformerResource;
use App\Models\Performers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PerformersController extends Controller
{
    public function add(Request $request){
        $message = null;
        try{
            $repo = new PerformerRepository(new Performers());
            $data =  $repo->findbyparam('uuid',$request->code)->first();

            if($data->director_id == $request->director){
               $message = 'This user exits in your data base';
            }else {
                $register = [
                    'performer_id' => $data->performer_id,
                    'director_id' => $request->director,
                    'uuid' => Str::uuid()->toString(),
                ];
                $repo2 = new PerformerRepository(new Performers());
                $create = $repo->create($register);
                $message = 'Add User OK';
}
           return  response()->json(['data'=>$message]);
        }catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Error add performer'], 406);
        }
    }
    public function shareCode(Request $request){

        try{
            //TODO definir tipo de notificacion
            return  response()->json(['data'=>'Code share']);
        }catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Error send code'], 406);
        }
    }

    public function list(Request $request){
        try{
           $repo = new PerformerRepository(new Performers());
           $data =  $repo->findbyparam('director_id',$request->director)->get();
           if($data->count() == 0){
               throw new \Exception('Not found data');
           }

           $dataResponse = PerformerResource::collection($data);
           return response()->json(['data'=>$dataResponse],200);
        }catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not found data'], 404);
        }
    }
}
