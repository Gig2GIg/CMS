<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SkillsRepository;
use App\Http\Resources\SkillsResource;
use App\Models\Skills;
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    public function list(){
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

    public function byUser(){

    }

    public function addToUser(){

    }

    public function deleteToUser(){

    }


}
