<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\MediaManagerResource;
use App\Http\Requests\MediaRenameRequest;
use App\Http\Requests\UpdateAuditionVideoName;
use App\Models\Auditions;
use App\Models\Resources;
use App\Models\AuditionVideos;
use App\Models\OnlineMediaAudition;
use App\Models\User;
use App\Models\UserAuditionMedia;
use Illuminate\Http\Request;

class MediaManagerController extends Controller
{
    protected $log;
    protected $dataArray;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(Request $request)
    {
        try {
            $userRepo = new UserRepository(new User());
            $data = $userRepo->find($this->getUserLogging());
            $media = $data->image()->create([
                'url' => $request->url,
                'name' => $request->name,
                'thumbnail' => $request->has('thumbnail') ? $request->thumbnail : NULL,
                'type' => $request->type
            ]);
            if (isset($media->id)) {
                $dataResponse = ['data' => 'Media saved'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Media not saved'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function delete(Request $request)
    {
        try {
            $media = new Resources();
            $data = $media->find($request->id);
            $del = $data->delete();
            if ($del) {
                $dataResponse = ['data' => 'Media deleted'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Media not deleted'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function get(Request $request)
    {
        try {
            $media = new Resources();
            $data = $media->where('resource_id','=',$this->getUserLogging())
                ->where('resource_type','=','App\Models\User')
                ->get();

            if ($data->count() > 0) {
                $filter = $data->filter(function($item){
                    return $item->type !== 'cover';
                });

                $dataResponse = ['data' => $filter->groupBy('type')];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }


    public function getByType(Request $request)
    {
        try {
            $media = new Resources();
            $data = $media->where('resource_id','=',$this->getUserLogging())
                ->where('resource_type','=','App\Models\User')
                ->where('type','=',$request->type)
                ->get();

            if ($data->count() > 0) {
                $dataResponse = ['data' => $data];
                $code = 200;
            } else {
                $dataResponse = ['data' => []];
                $code = 200;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function addAuditionMedia(Request $request){
        try{
            $media = new UserAuditionMedia();
            $data = $media->where('user_id','=',$this->getUserLogging())->where('auditions_id','=',$request->audition);

            if($data->count() >0 ){
                $dataResponse = ['data' => 'You already add this media'];
                $code = 406;
            }else {
                $create = $media->create([
                    'user_id' => $this->getUserLogging(),
                    'auditions_id' => $request->audition
                ]);
                if ($create->id) {

                    $dataResponse = ['data' => 'Add media'];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => 'Not Add media'];
                    $code = 406;
                }
            }
            return response()->json($dataResponse, $code);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function getbyuser(Request $request)
    {
        try {
            $userauditions = new UserAuditionMedia();
            $dataUserAudi = $userauditions->where('user_id','=',$this->getUserLogging())->get();
            $dataUserAudi->each(function ($element){
                $auditions = new AuditionRepository(new Auditions());
                $dataAuditions = $auditions->find($element->auditions_id);

                $media = $dataAuditions->resources()->where('resource_type','=','App\Models\Auditions')->get();
                $cover = $dataAuditions->resources()
                    ->where('resource_type','=','App\Models\Auditions')
                    ->where('type','=','cover')
                    ->first()['url'];
                $filter = $media->filter(function($item){
                    return $item->type !== 'cover';
                });
                $this->dataArray[] = [
                    'name'=>$dataAuditions->title,
                    'cover'=>$cover ?? null,
                    'files'=>$filter
                    ];
            });


            if (isset($this->dataArray) && count($this->dataArray) > 0) {

                $dataResponse = ['data' => $this->dataArray];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function updateResourceName(MediaRenameRequest $request)
    {
        try {
            $media = new Resources();
            $data = $media->find($request->id);
            $update = $data->update(['name' => $request->name]);
            if ($update) {
                $dataResponse = ['data' => 'Media Updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Media not updated'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function updateAuditionVideoName(UpdateAuditionVideoName $request)
    {
        try {

            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->audition_id);

            if($data->online){
                $media = new OnlineMediaAudition();
            }else{
                $media = new AuditionVideos();
            }

            $data = $media->find($request->id);
            $update = $data->update(['name' => $request->name]);
            
            if ($update) {
                $dataResponse = ['data' => 'Media Updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Media not updated'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => 'Not processable'], 406);
        }
    }
}
