<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\RecommendationsRepository;

use App\Http\Controllers\Utils\LogManger;

use App\Models\Recommendations;
use App\Models\Auditions;

use App\Http\Requests\RecommendationsRequest;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Resources\RecommendationMarketplacesResource;


class RecommendationsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt');
        $this->log = new LogManger();
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
     
       if ($recommendation) {
           $responseData = 'Recommendations save';
           $code = 201;
       } else {
            $responseData = 'Unproccesable';
            $code = 422;
       }   

       return response()->json(['data' =>  $responseData], $code);
    }


    public function list(Auditions $audition, Request $request)
    {
        $data =  $audition->recommendations_marketplaces;

        $data = $audition->recommendations_marketplaces->where('user_id', $this->getUserLogging());
     
       if (count($data) > 0) {
           $data->where('user_id', $this->getUserLogging());
           $responseData = RecommendationMarketplacesResource::collection($data);
           $code = 200;
       } else {
            $responseData = 'Not Found';
            $code = 404;
       }   

       return response()->json(['data' =>  $responseData], $code);
    }


    public function listByUser(Auditions $audition, Request $request)
    {
        $data =  $audition->recommendations_marketplaces;

        $data = $audition->recommendations_marketplaces->where('user_id', $request->user_id);
     
       if (count($data) > 0) {
           $responseData = RecommendationMarketplacesResource::collection($data);
           $code = 200;
       } else {
            $responseData = 'Not Found';
            $code = 404;
       }   

       return response()->json(['data' =>  $responseData], $code);
    }


    public function updateFromArray(Request $request)
    {
        try {
            $repoRecommendation = new RecommendationsRepository(new Recommendations());
           
          
            foreach ($request->marketplaces as $markeplace) {
              
                $recommendation = Recommendations::find($markeplace['id']);

                if (! is_null($recommendation)){   
                   $recommendation->update([
                            'marketplace_id' => $markeplace['marketplace_id']
                    ]);     
                }

                if ( is_null($recommendation) ){
                    $repoRecommendation->create([
                            'marketplace_id' => $markeplace['marketplace_id'],
                            'audition_id' => $markeplace['audition_id'],
                            'user_id' => $markeplace['user_id']
                    ]);
                }
            }
     

            $dataResponse = ['data' => 'Marketplaces updates'];
            $code = 200;
           
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 422);
        }

    }



}

