<?php

namespace App\Http\Controllers;


use App\Http\Repositories\MarketplaceFeaturedListingRepository;

use App\Models\MarketplaceFeaturedListing;
use App\Http\Resources\FeaturedListingResource;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\Debug\Exception\FatalThrowableError;


class MarketplaceFeaturedListingController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function getAllFeaturedListing(Request $request)
    {
        $featuredListingRepo = new MarketplaceFeaturedListingRepository(new MarketplaceFeaturedListing);
        $data = $featuredListingRepo->all();
        $count = count($data);

        if ($count !== 0) {
            $responseData = FeaturedListingResource::collection($data);
            return response()->json(['data' => $responseData], 200);
        } else {
            // return response()->json(['data' => "Not found Data"], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }


    public function store(Request $request)
    {
        try {
            $data = [
                'business_name' => $request->business_name,
                'email' => $request->email
            ];

            $featuredListingRepo = new MarketplaceFeaturedListingRepository(new MarketplaceFeaturedListing);
            $featuredListing = $featuredListingRepo->create($data);

            return response()->json(['data' => new FeaturedListingResource($featuredListing)], 201);
        } catch (QueryException $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Error created Marketplace Featured Listing'], 406);
            return response()->json(['data' => trans('messages.error_created_marketplace_featured_listing')], 406);
        }
    }
}
