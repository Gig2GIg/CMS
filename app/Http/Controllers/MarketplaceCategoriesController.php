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
use App\Http\Controllers\Utils\LogManger;
use App\Models\Resources;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class MarketplaceCategoriesController extends Controller
{
 
    protected $model;
    protected $log;

    public function __construct(MarketplaceCategory $makerplace_category)
    {
        $this->middleware('jwt');
        $this->model = $makerplace_category;
        $this->log = new LogManger();
    }


    public function getAll()
    {

        $repo = new MarketplaceRepository(new Marketplace());
        $market_featured = $repo->all()
            ->where('featured', 'yes')
            ->sortByDesc('updated_at')
            ->first
            ->get();

        $data = new MarketplaceCategoryRepo(new MarketplaceCategory);
        $count = count($data->all());

        if ($count !== 0) {
            $responseData = MarketplaceCategoryResource::collection($data->all());
            $vendor = $repo->all()->where('id','!=' )->sortByDesc('updated_at');

            return response()->json([
                'featured_image' => $market_featured == null ?  [] : $market_featured->image->url,
                'vendor_featured' => $market_featured,
                'vendors' => MarketplaceResource::collection($repo->all()),
                'data' => $responseData
            ], 200);
        } else {
            return response()->json(['data' => "Not found Data"], 404);
        }

    }

}

 