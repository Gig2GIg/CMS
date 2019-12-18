<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\TypeProductsRepository;

use App\Models\TypeProduct;
use App\Http\Resources\Cms\TypeProductsResource;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class TypeProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function getAll(TypeProduct $request)
    {
       $data = new TypeProductsRepository($request);
       $count = count($data->all());
       if ($count !== 0) {
           $responseData = TypeProductsResource::collection($data->all());
           return response()->json(['data' => $responseData], 200);
       } else {
        //    return response()->json(['data' => "Not found Data"], 404);
           return response()->json(['data' => trans('messages.data_not_found')], 404);
       }   
    }
}
