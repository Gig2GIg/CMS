<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Repositories\TypeProductsRepository;
use App\Http\Resources\Cms\TypeProductsResource;

use App\Models\TypeProduct;

use App\Http\Requests\TypeProductsRequest;

use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class TypeProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function getAll()
    {
        $data = new TypeProductsRepository(new TypeProduct);
        $count = count($data->all());
        if ($count !== 0)
        {
            $responseData = TypeProductsResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => 'Record Not Found'], 404);
        }
    }

    public function store(TypeProductsRequest $request)
    {
        if ($request->json())
        {
            $typeProductData = [
                'name' => $request->name,
            ];

            $typeProductRepo = new TypeProductsRepository(new TypeProduct);
          
            $typeProductResult = $typeProductRepo->create($typeProductData);

            return response()->json(['data' => new TypeProductsResource($typeProductResult)], 201);
        }else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function update(TypeProductsRequest $request)
    {
        try {
            if ($request->json()) {
                $typeProductData = [
                    'name' => $request->name
                ];
    
                $typeProductRepo= new TypeProductsRepository(new TypeProduct());

                $typeProductResult =  $typeProductRepo->find($request->id);
                $typeProductResult->update($typeProductData);

              return response()->json(['data' => 'Type Product Updated'], 204);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function show(): ?\Illuminate\Http\JsonResponse
    {
        try {
            $typeProductRepo = new TypeProductsRepository(new TypeProduct());

            $data = $typeProductRepo->find(request('id'));

            if (! empty($data)) {
                $responseData = new TypeProductsResource($data);
                return response()->json(['data' => $responseData], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $typeProductRepo = new TypeProductsRepository(new TypeProduct());
            $dataTypeProduct = $typeProductRepo->find($request->id);
            $dataTypeProduct->delete();

            return response()->json(['data' => 'Type Product  deleted'], 204);
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 422);
        }
    }

}
