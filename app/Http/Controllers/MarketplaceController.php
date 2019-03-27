<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Marketplace\MarketplaceRepository as MarketplaceRepo;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo ;

use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use App\Http\Resources\Cms\MarketplaceResource;
use App\Http\Requests\Marketplace\MarketplaceRequest;
use App\Http\Requests\Marketplace\MarketplaceEditRequest;
use App\Http\Requests\Marketplace\MarketplaceSearchRequest;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


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
           return response()->json(['data' => "Not found Data"], 404);
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
           return response()->json(['data' => "Not found Data"], 404);
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
           return response()->json(['data' => "Not found Data"], 404);
       }   
    }
   
}
