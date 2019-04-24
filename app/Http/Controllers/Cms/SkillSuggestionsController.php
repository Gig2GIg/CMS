<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Repositories\SkillSuggestionsRepository;
use App\Http\Resources\Cms\SkillSuggestionsResource;

use App\Models\SkillSuggestion;

use App\Http\Requests\SkillSuggestionsRequest;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class SkillSuggestionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function getAll()
    {
        $data = new SkillSuggestionsRepository(new SkillSuggestion);
        $count = count($data->all());
        if ($count !== 0)
        {
            $responseData = SkillSuggestionsResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => 'Record Not Found'], 404);
        }
    }

    public function store(SkillSuggestionsRequest $request)
    {
        if ($request->json())
        {
            $skillSuggestionData = [
                'name' => $request->name,
            ];

            $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion);
          
            $skillSuggestionResult = $skillSuggestionRepo->create($skillSuggestionData);

            return response()->json(['data' => new SkillSuggestionsResource($skillSuggestionResult)], 201);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function update(SkillSuggestionsRequest $request)
    {
        try {
            if ($request->json()) {
                $skillSuggestionData = [
                    'name' => $request->name
                ];
    
                $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());

                $skillSuggestionResult =  $skillSuggestionRepo->find($request->id);
                $skillSuggestionResult->update($skillSuggestionData);

              return response()->json(['data' => 'Type SkillSuggestion Updated'], 204);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function show(): ?\Illuminate\Http\JsonResponse
    {
        try {
            $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());

            $data = $skillSuggestionRepo->find(request('id'));

            if (! empty($data)) {
                $responseData = new SkillSuggestionsResource($data);
                return response()->json(['data' => $responseData], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $skillSuggestionRepo = new SkillSuggestionsRepository(new SkillSuggestion());
            $dataSkillSuggestion = $skillSuggestionRepo->find($request->id);
            $dataSkillSuggestion->delete();

            return response()->json(['data' => 'SkillSuggestion  deleted'], 204);
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 422);
        }
    }

}
