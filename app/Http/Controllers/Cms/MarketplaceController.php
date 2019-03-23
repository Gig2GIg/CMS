<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Marketplace\MarketplaceRepository as MarketplaceRepo ;
use App\Models\Marketplace;
use App\Http\Resources\Cms\MarketplaceResource;
use App\Http\Requests\Marketplace\MarketplaceRequest;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class MarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function getAll()
    {
        $data = new MarketplaceRepo(new Marketplace);
        $count = count($data->all());
        if ($count !== 0)
        {
            $responseData = MarketplaceResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => 'Record Not Found'], 404);
        }
    }

    public function store(MarketplaceRequest $request)
    {
        if ($request->json())
        {
            $marketplaceData = [
                'title' => $request->title,
                'address' => $request->address,
                'services' => $request->services,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ];

            $marketplace = new MarketplaceRepo(new Marketplace);
            $marketplace_result = $marketplace->create($marketplaceData);
            $marketplace_result->image()->create([
                'url' => $request->image_url,
                'type' => '3'
            ]);
            return response()->json(['data' => new MarketplaceResource($marketplace_result)], 201);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
