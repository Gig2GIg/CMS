<?php

namespace App\Http\Controllers\Cms;

use App\Models\Skills;
use App\Http\Controllers\Controller;
use App\Http\Repositories\SkillsRepository;
use App\Http\Resources\Cms\SkillResource;
use App\Http\Requests\SkillRequest;
use App\Http\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class SkillsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function getAll()
    {
        $data = new SkillsRepository(new Skills);
        return SkillResource::collection($data->all());
    }

    public function store(SkillRequest $request)
    {
        if ($request->json())
        {
            $skillData = [
                'name' => $request->name,
            ];

            $skillRepo = new SkillsRepository(new Skills);

            $skillResult = $skillRepo->create($skillData);

            return response()->json(['data' => new SkillResource($skillResult)], 201);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function update(SkillRequest $request)
    {
        try {
            if ($request->json()) {
                $skillData = [
                    'name' => $request->name
                ];

                $skillRepo = new SkillsRepository(new Skills());

                $skillResult =  $skillRepo->find($request->id);
                $skillResult->update($skillData);

              return response()->json(['data' => 'Skill Updated'], 204);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $skillRepo = new SkillsRepository(new Skills());
            $dataSkill = $skillRepo->find($request->id);
            $dataSkill->delete();

            return response()->json(['data' => 'Skill deleted'], 204);
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 422);
        }
    }

}
