<?php

namespace App\Http\Controllers;

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
        $count = count($data->all());
        if ($count !== 0) {
            $responseData = MarketplaceCategoryResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => "Not found Data"], 404);
        }   
     }

}
