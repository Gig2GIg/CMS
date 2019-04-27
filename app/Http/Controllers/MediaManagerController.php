<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\MediaManagerResource;
use App\Models\Auditions;
use App\Models\Resources;
use App\Models\User;
use App\Models\UserAuditionMedia;
use DemeterChain\A;
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
            $data = $userRepo->find($request->user);
            $media = $data->image()->create([
                'url' => $request->url,
                'name' => $request->name,
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
            return response()->json(['data' => 'Not processable'], 406);
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
            return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function get(Request $request)
    {
        try {
            $media = new Resources();
            $data = $media->where('resource_id','=',$request->id)->where('resource_type','=','App\Models\User')->get();

            if ($data->count() > 0) {
                $filter = $data->filter(function($item){
                    return $item->type !== 'cover';
                });
                $dataResponse = ['data' => MediaManagerResource::collection($filter)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not processable'], 406);
        }
    }

    public function addAuditionMedia(Request $request){
        try{
            $media = new UserAuditionMedia();
            $create = $media->create([
               'user_id' =>$this->getUserLogging(),
                'auditions_id'=>$request->audition
            ]);
            if($create->id) {

                $dataResponse = ['data' => 'Add media'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not Add media'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not processable'], 406);
        }
    }
    public function getbyuser(Request $request)
    {
        $dataArray[]=[];

        try {
            $userauditions = new UserAuditionMedia();
            $dataUserAudi = $userauditions->where('user_id','=',$this->getUserLogging())->get();
            $dataUserAudi->each(function ($element) use ($dataArray){
                $auditions = new AuditionRepository(new Auditions());
                $dataAuditions = $auditions->find($element->id);

                $media = new Resources();
                $data = $media->where('resource_id','=',$element->id)->where('resource_type','=','App\Models\Auditions')->get();
                $filter = $data->filter(function($item){
                    return $item->type !== 'cover';
                });
                $dataArray = [
                    'name'=>$dataAuditions->name,
                    'files'=>$filter
                    ];
            });


            if (count($dataArray) > 0) {

                $dataResponse = ['data' => $dataArray];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not processable'], 406);
        }
    }
}
