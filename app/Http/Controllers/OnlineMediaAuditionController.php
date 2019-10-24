<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\MediaOnlineRepository;
use App\Models\OnlineMediaAudition;
use Illuminate\Http\Request;

class OnlineMediaAuditionController extends Controller
{
    public function create(Request $request){
       try{
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $data=[
                'type'=>$request->type,
                'url'=>$request->url,
                'thumbnail'=>$request->thumbnail,
                'name'=>$request->name,
                'appointment_id'=>$request->appointment_id,
                'performer_id'=>$this->getUserLogging()
            ];
            $res = $repo->create($data);
            if(is_null($res->id)){
                throw new CreateException('media not created');
            }

            return response()->json([
                'message'=>'Media created',
                'data'=>$res
            ],201);

       }catch (\Exception $exception){
           $this->log->error("ONLINEMEDIA:: ".$exception->getMessage());
           $this->log->error("ONLINEMEDIA:: ".$exception->getLine());
           return response()->json([
               'message'=>'Media not created',
               'data'=>[]
           ], 405);
       }
    }

    public function listByUser(Request $request){
        try{
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $dataUser = $repo->findbyparam('performer_id',$request->performer_id)->get();
            $data = $dataUser->where('appointment_id',$request->appointment_id);
            if($data->count() == 0){
                throw new NotFoundException('media not found');
            }

            return response()->json([
                'message'=>'Media for user: ' .$request->performer_id,
                'data'=>$data
            ],200);

        }catch (\Exception $exception){
            $this->log->error("ONLINEMEDIA:: ".$exception->getMessage());
            $this->log->error("ONLINEMEDIA:: ".$exception->getLine());
            return response()->json([
                'message'=>'Media not found',
                'data'=>[]
            ], 404);
        }
    }

    public function listByRound(Request $request){
        try{
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $data = $repo->findbyparam('appointment_id',$request->appointment_id)->get();

            if($data->count() == 0){
                throw new NotFoundException('media not found');
            }

            return response()->json([
                'message'=>'Media for round: ' .$request->appointment_id,
                'data'=>$data
            ],200);

        }catch (\Exception $exception){
            $this->log->error("ONLINEMEDIA:: ".$exception->getMessage());
            $this->log->error("ONLINEMEDIA:: ".$exception->getLine());
            return response()->json([
                'message'=>'Media not found',
                'data'=>[]
            ], 404);
        }
    }

    public function get(Request $request){
        try{
            $repo = new MediaOnlineRepository(new OnlineMediaAudition());
            $data = $repo->find($request->id);

            if($data->count() == 0){
                throw new NotFoundException('media not found');
            }

            return response()->json([
                'message'=>'Media for id: ' .$request->id,
                'data'=>$data
            ],200);

        }catch (\Exception $exception){
            $this->log->error("ONLINEMEDIA:: ".$exception->getMessage());
            $this->log->error("ONLINEMEDIA:: ".$exception->getLine());
            return response()->json([
                'message'=>'Media not found',
                'data'=>[]
            ], 404);
        }
    }
}
