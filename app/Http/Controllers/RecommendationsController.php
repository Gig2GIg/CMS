<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\RecommendationsRepository;

use App\Models\Recommendations;

use App\Http\Requests\RecommendationsRequest;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class RecommendationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function store(RecommendationsRequest $request)
    {
       $recommendationsRepo = new RecommendationsRepository(new Recommendations());
       $data = [
            'marketplace_id'=> $request->marketplace_id,
            'user_id'=> $request->user_id,
            'audition_id'=> $request->audition_id,
       ];
       $recommendation = $recommendationsRepo->create($data);
     
       if (recommendation) {
           $responseData = 'Recommendations save';
           $code = 200;
       } else {
            $responseData = 'Unproccesable';
            $code = 422;
       }   

       return response()->json(['data' =>  $responseData], $code);
    }

}
