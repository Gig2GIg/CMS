<?php

namespace App\Http\Controllers;

use App\Http\Repositories\FinalCastRepository;
use App\Http\Resources\FinalCastResource;
use App\Models\FinalCast;
use Illuminate\Http\Request;
use Exception;

class FinalCastController extends Controller
{
    public function add(Request $request){
        try{
          $repo = new FinalCastRepository(new FinalCast());
          $data = [
            'performer_id'=>$request->performer_id,
            'audition_id'=>$request->audition_id,
            'rol_id'=>$request->rol_id
          ];
          $create = $repo->create($data);
          if($create->id === null){
              throw  new Exception('NOT CREATED REGISTER FOR FINAL CAST');
          }
            return response()->json(['data'=>'Add performer to final cast'],201);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data'=>'fail to add performer'],406);
        }
    }

    public function list(Request $request){
        try{
            $repo = new FinalCastRepository(new FinalCast());
            $data = $repo->findbyparam('audition_id',$request->audition_id)->get();
            if($data->count() == 0){
                throw new Exception('Data Not Found');
            }
            return response()->json(['data'=>FinalCastResource::collection($data)],200);
        }catch (Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data'=>'Data Not Found'],404);
        }
    }

    public function update(Request $request){
        try{
            $repo = new FinalCastRepository(new FinalCast());
            $data = $repo->find($request->id);

            $update = $data->update(['user_id'=>$request->performer_id]);
           if(!$update){
               throw new Exception('performer in final cast list not updated');
           }
            return response()->json(['data'=>true],200);
        }catch (Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data'=>false],406);
        }
    }

    public function delete(Request $request){
        try{
            $repo = new FinalCastRepository(new FinalCast());
            $data = $repo->find($request->id);
            $delete = $data->delete();
            if(!$delete){
                throw new Exception('performer in final cast list not updated');
            }
            return response()->json(['data'=>true],200);
        }catch (Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data'=>false],406);
        }
    }
}
