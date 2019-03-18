<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditionsController extends Controller
{
    public function create(AuditionRequest $request){
        if($request->isJson()){
            $auditionData=[];
            $auditionDatesData=[];
            $auditionContributirsData=[];
            $auditionRolesData=[];
            $auditionFilesData=[];
        }

        return response()->json("", 400);
    }
}
