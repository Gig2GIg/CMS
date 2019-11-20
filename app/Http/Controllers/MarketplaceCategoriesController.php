<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo;
use App\Http\Repositories\Marketplace\MarketplaceRepository;
use App\Http\Repositories\ResourcesRepository;
use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use App\Http\Resources\Cms\MarketplaceCategoryResource;
use App\Http\Resources\Cms\MarketplaceResource;
use App\Http\Requests\Marketplace\MarketplaceCategoryRequest;
use App\Http\Exceptions\NotFoundException;

use App\Models\Resources;
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

            $repo = new MarketplaceRepository(new Marketplace());
            $marketplaces = $repo->all();
      
            $count = count($marketplaces);
            if ($count !== 0) {
                $responseData = MarketplaceResource::collection($marketplaces);
                return response()->json([
                    'data' => $responseData
                ], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
      
    }

}
