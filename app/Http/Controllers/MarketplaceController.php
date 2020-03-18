<?php

namespace App\Http\Controllers;


use App\Http\Repositories\Marketplace\MarketplaceRepository as MarketplaceRepo;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo;

use App\Http\Requests\Marketplace\MarketplaceRequest;
use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use App\Http\Resources\Cms\MarketplaceResource;

use App\Http\Requests\Marketplace\MarketplaceSearchRequest;


use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\Debug\Exception\FatalThrowableError;


class MarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function getAllMarketplaceByCategory(MarketplaceCategory $marketplaceCategory)
    {
        $data = new MarketplaceCategoryRepo($marketplaceCategory);
        $count = count($data->allMarketplace());
        if ($count !== 0) {
            $responseData = MarketplaceResource::collection($data->allMarketplace());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function search_by_title(MarketplaceSearchRequest $request)
    {
        $data = new MarketplaceRepo(new Marketplace());
        $count = count($data->search_by_title($request->value));


        if ($count !== 0) {
            $responseData = MarketplaceResource::collection($data->search_by_title($request->value));
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function search_by_category_by_title(MarketplaceCategory $marketplaceCategory, MarketplaceSearchRequest $request)
    {
        $data = new MarketplaceCategoryRepo($marketplaceCategory);
        $count = count($data->search_marketplaces_by_category_by_title($request->value));


        if ($count !== 0) {
            $responseData = MarketplaceResource::collection($data->search_marketplaces_by_category_by_title($request->value));
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function store(MarketplaceRequest $request)
    {
        try {
            $marketplaceData = [
                'title' => $request->title,
                'address' => $request->address,
                'services' => $request->services,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'marketplace_category_id' => 1,
                'url_web' => $request->url_web
            ];

            $marketplace = new MarketplaceRepo(new Marketplace);
            $marketplace_result = $marketplace->create($marketplaceData);
            $marketplace_result->image()->create([
                'url' => $request->image_url,
                'thumbnail' => $request->has('thumbnail') ? $request->thumbnail : NULL,
                'name' => $request->image_name,
                'type' => '4'
            ]);
            return response()->json(['data' => new MarketplaceResource($marketplace_result)], 201);
        } catch (QueryException $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.error_created_marketplace')], 406);
            // return response()->json(['data' => 'Error created Marketplace'], 406);
        }

    }

    public function delete(Request $request)
    {
        try {
            $repoMarket= new MarketplaceRepo(new Marketplace());
            $marketplace = $repoMarket->find($request->id);
            

            if ($marketplace->delete()){
                $dataResponse = ['data' => 'Marketplace removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Tag not removed'];
                $code = 404;
            }
      
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }
    }

}
