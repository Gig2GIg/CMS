<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\SkillsRepository;
use App\Http\Resources\Cms\SkillResource;
use App\Http\Requests\SkillRequest;
use App\Http\Repositories\UserSkillsRepository;
use App\Http\Resources\SkillsResource;
use App\Models\Skills;
use App\Models\UserSkills;
use Illuminate\Http\Request;


class SkillsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }
    public function list()
    {
        $repo = new SkillsRepository(new Skills());
        $data = $repo->all();

        if ($data->count() > 0) {
            $dataResponse = ['data' => SkillsResource::collection($data)];
            $code = 200;
        } else {
            $dataResponse = ['data' => 'Not Found Data'];
            $code = 404;
        }

        return response()->json($dataResponse, $code);

    }

    public function byUser()
    {
        $repo = new UserSkillsRepository(new UserSkills());
        try {
            $data = $repo->findbyparam('user_id', $this->getUserLogging());
            if (count($data) > 0) {
                $dataResponse = ['data' => SkillsResource::collection($data)];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Not Found Data'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }


    public function store(SkillRequest $request)
    {
      
        try {
            $data = [
                'name' => $request->name
            ];

            $skillRepo = new SkillsRepository(new Skills);
            $skillResult = $skillRepo->create($data);

            $data_skill = [
                'skills_id' => $skillResult->id,
                'user_id' => $this->getUserLogging(),
            ];
            $repo = new UserSkillsRepository(new UserSkills());
            $repo->create($data_skill);


            $dataResponse = ['data' => 'Skill created'];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 500);
        }

    }


    public function addToUser(Request $request)
    {
        try {
            $data = [
                'skills_id' => $request->skills_id,
                'user_id' => $this->getUserLogging(),
            ];
            $repo = new UserSkillsRepository(new UserSkills());
            $repo->create($data);

            $dataResponse = ['data' => 'Skill add'];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 500);
        }
    }

    public function deleteToUser(Request $request)
    {
        try {
            $repo = new UserSkillsRepository(new UserSkills());
            $data = $repo->find($request->id)->delete();

            if ($data) {
                $dataResponse = ['data' => 'Skill removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Skill not removed'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }


}
