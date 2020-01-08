<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\Marketplace\MarketplaceCategoryRepository as MarketplaceCategoryRepo;
use App\Http\Repositories\Marketplace\MarketplaceRepository;
use App\Http\Resources\Cms\MarketplaceCategoryResource;
use App\Models\Marketplace;
use App\Models\MarketplaceCategory;
use Illuminate\Support\Collection;

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
        try {
            $repo = new MarketplaceRepository(new Marketplace());
            $market = $repo->all();
            // ->where('featured', 'yes')
            // ->sortByDesc('updated_at');

            $data = new MarketplaceCategoryRepo(new MarketplaceCategory);
            $count = count($data->all());

            if ($count !== 0) {
                $responseData = MarketplaceCategoryResource::collection($data->all());
            }

            if (!empty($market)) {
                $featured_image = '';
                $marketResponse = new Collection();
                foreach ($market as $item) {
                    if ($item->featured == 'yes') {
                        $market_cat = MarketplaceCategory::find($item->marketplace_category_id);
                        $item->marketplace_category_name = $market_cat->name;
                        $item->marketplace_category_description = $market_cat->description;
                        $item->image;
                        $featured_image = $item->image->url;
                    }
                    $marketResponse->push($item);
                }

                // $image = $market[0]->image->get()->pluck('url')->first();

                if ($count !== 0) {
                    return response()->json([
                        'featured_image' => $featured_image,
                        'featured' => $marketResponse,
                        'data' => $responseData,
                    ], 200);
                } else {
                    return response()->json([
                        'data' => $responseData,
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => $responseData,
                ], 200);
            }
        } catch (\Exception $exception) {
            // $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => "Not found Data"], 404);
        }
    }
}
