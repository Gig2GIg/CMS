<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Marketplace\MarketplaceRepository as MarketplaceRepo;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo ;

use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use App\Http\Resources\Cms\MarketplaceResource;
use App\Http\Requests\Marketplace\MarketplaceRequest;
use App\Http\Requests\Marketplace\MarketplaceEditRequest;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class MarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function getAll()
    {
        $data = new MarketplaceRepo(new Marketplace);
        $count = count($data->all());
        if ($count !== 0)
        {
            $responseData = MarketplaceResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => 'Record Not Found'], 404);
        }
    }

    public function getAllMarketplaceByCategory(MarketplaceCategory $marketplaceCategory)
    {
       $data = new MarketplaceCategoryRepo($marketplaceCategory);
       $count = count($data->allMarketplace());
       if ($count !== 0) {
           $responseData = MarketplaceResource::collection($data->allMarketplace());
           return response()->json(['data' => $responseData], 200);
       } else {
           return response()->json(['data' => "Not found Data"], 404);
       }   
    }


    public function store(MarketplaceRequest $request, MarketplaceCategory $marketplaceCategory)
    {
        if ($request->json())
        {
            $marketplaceData = [
                'title' => $request->title,
                'address' => $request->address,
                'services' => $request->services,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'marketplace_category_id' => $marketplaceCategory->id
            ];

            $marketplace = new MarketplaceRepo(new Marketplace);
            $marketplace_result = $marketplace->create($marketplaceData);
            $marketplace_result->image()->create([
                'url' => $request->image_url,
                'type' => '3'
            ]);
            return response()->json(['data' => new MarketplaceResource($marketplace_result)], 201);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateMarkeplace(MarketplaceEditRequest $request)
    {
        try {
            if ($request->json()) {
                $marketplaceData = [
                    'title' => $request->title,
                    'address' => $request->address,
                    'services' => $request->services,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number
                ];
    
                $marketplace = new MarketplaceRepo(new Marketplace());
                $result =  $marketplace->find(request('id'));
                $result->update($marketplaceData);

                return response()->json(['data' => 'Marketplace  Updated'], 204);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function getMarkeplace(): ?\Illuminate\Http\JsonResponse
    {
        try {
            $marketplace = new MarketplaceRepo(new Marketplace());

            $data = $marketplace->find(request('id'));

            if (! empty($data)) {
                $responseData = new MarketplaceResource($data);
                return response()->json(['data' => $responseData], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function deleteMarkeplace(Request $request)
    {
        try {
            $marketplace = new MarketplaceRepo(new Marketplace());
            $dataMarketplace = $marketplace->find($request->id);
            $dataMarketplace->delete();

            return response()->json(['data' => 'Marketplace  deleted'], 200);
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 422);
        }
    }




}