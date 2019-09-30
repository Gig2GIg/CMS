<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;

use App\Http\Repositories\AppointmentRepository;
use App\Models\Appointments;
use App\Models\Tags;
use App\Models\User;
use App\Models\Auditions;

use Illuminate\Http\Request;
use App\Http\Repositories\TagsRepository;
use App\Http\Repositories\AuditionRepository;

use App\Http\Resources\TagsResource;

class TagsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(Request $request)
    {
        try {
            $data = [
                'title' => $request->title,
                'appointment_id' => $request->appointment_id,
                'user_id' => $request->user_id,
                'setUser_id' => $this->getUserLogging(),
            ];

            $repoTag = new TagsRepository(new Tags());
            $tag = $repoTag->create($data);

            $dataResponse = [
                'message' => 'Tag created',
                'data' => $tag
            ];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }


    public function delete(Request $request)
    {
        try {
            $repoTag = new TagsRepository(new Tags());
            $tag = $repoTag->find($request->id);


            if ($tag->delete()) {
                $dataResponse = ['data' => 'Tag removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Tag not removed'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }


    public function updateFromArray(Request $request)
    {
        try {
            $repoTag = new TagsRepository(new Tags());
            $repoAppointment = new AppointmentRepository(new Appointments());
            $appointment = $repoAppointment->find($request->id);

            if (!is_null($appointment)) {
                foreach ($request->tags as $tag_data) {

                    $t = Tags::find($tag_data['id']);

                    if (!is_null($t)) {
                        $t->update([
                            'title' => $tag_data['title']
                        ]);
                    }

                    if (is_null($t)) {
                        $repoTag->create([
                            'title' => $tag_data['title'],
                            'appointment_id' => $appointment->id,
                            'user_id' => $tag_data['user_id'],
                            'setUser_id' => $this->getUserLogging(),
                        ]);
                    }
                }

                $dataResponse = ['data' => 'Tags updates'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Audition Not Found'];
                $code = 404;
            }


            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }

    public function listByUser(Request $request)
    {
        try {

            $tags = Tags::where('appointment_id', $request->id)->get();

            $userTags = $tags->where('user_id', $request->user_id);

            if (!is_null($userTags)) {
                $dataResponse = ['data' => TagsResource::collection($userTags)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }
}

