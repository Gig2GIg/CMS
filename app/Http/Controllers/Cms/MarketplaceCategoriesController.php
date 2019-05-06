<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo ;
use App\Models\MarketplaceCategory;
use App\Http\Resources\Cms\MarketplaceCategoryResource;
use App\Http\Requests\Marketplace\MarketplaceCategoryRequest;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class MarketplaceCategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

     public function getAll()
     {
        $data = new MarketplaceCategoryRepo(new MarketplaceCategory);
        return MarketplaceCategoryResource::collection($data->all());
     }

    public function store(MarketplaceCategoryRequest $request)
    {
        if ($request->json()) {
            $marketplaceCatagoryData = [
                'name' => $request->name,
                'description' => $request->description
            ];

            $marketplaceCatagoryDetails = new MarketplaceCategoryRepo(new MarketplaceCategory());
            $marketplaceCatagoryDetails->create($marketplaceCatagoryData);
            return response()->json(['data' => 'Marketplace Category Created'], 201);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateMarkeplaceCategory(MarketplaceCategoryRequest $request)
    {
        try {
            if ($request->json()) {
                $marketplaceCatagoryData = [
                    'name' => $request->name,
                    'description' => $request->description
                ];

                $marketplaceCategory = new MarketplaceCategoryRepo(new MarketplaceCategory());
                $result =  $marketplaceCategory->find(request('id'));
                $result->update($marketplaceCatagoryData);

                return response()->json(['data' => 'Marketplace Category Updated'], 204);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function getMarkeplaceCategory(): ?\Illuminate\Http\JsonResponse
    {
        try {
            $marketplaceCategory = new MarketplaceCategoryRepo(new MarketplaceCategory());

            $data = $marketplaceCategory->find(request('id'));

            if (! empty($data)) {
                $responseData = new MarketplaceCategoryResource($data);
                return response()->json(['data' => $responseData], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }


    public function deleteMarkeplaceCategory(Request $request)
    {
        try {
            $marketplaceCategory = new MarketplaceCategoryRepo(new MarketplaceCategory());
            $dataMarketplaceCategory = $marketplaceCategory->find($request->id);
            $dataMarketplaceCategory->delete();

            return response()->json(['data' => 'Marketplace Category deleted'], 200);
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 422);
        }
    }


}
