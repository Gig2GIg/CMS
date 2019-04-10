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
    
 
   
}
