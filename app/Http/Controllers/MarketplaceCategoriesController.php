<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo;
use App\Http\Repositories\Marketplace\MarketplaceRepository;
use App\Http\Repositories\ResourcesRepository;
use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use App\Http\Resources\Cms\MarketplaceCategoryResource;
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
        try {
            $repo = new MarketplaceRepository(new Marketplace());
            $market = $repo->all()
                ->where('featured', 'yes')
                ->sortByDesc('updated_at')
                ->first();

            $data = new MarketplaceCategoryRepo(new MarketplaceCategory);
            $count = count($data->all());

            if ($count !== 0) {
                $responseData = MarketplaceCategoryResource::collection($data->all());
            }
            if ($market != null && !empty($market)) {
                $market = $market->get();
       
                
                $market_cat =MarketplaceCategory::find($market[0]->marketplace_category_id);
                $market[0]->marketplace_category_name = $market_cat->name;
                $market[0]->marketplace_category_description = $market_cat->description;
            
                $image = $market[0]->image->get()->pluck('url')->first();

                if ($count !== 0) {
                    return response()->json([
                        'featured_image' => $image,
                        'featured' => $market,
                        'data' => $responseData
                    ], 200);
                } else {
                    return response()->json([
                        'data' => $responseData
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => $responseData
                ], 200);
            }
        } catch (\Exception $exception) {
                     // $this->log->error($exception->getMessage());
            return response()->json(['data' => "Not found Data"], 404);
        }
    }
}
