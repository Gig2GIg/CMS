<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\RolesRepository;
use App\Http\Resources\RolesResource;
use App\Models\Roles;
use Illuminate\Http\Request;
use App\Models\User;

class RolesController extends Controller
{

    public function createRole(Request $request)
    {
        try
        {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'auditions_id' => $request->auditions_id
            ];

            $rolesRepository =  new RolesRepository(new Roles);
            $roles = $rolesRepository->create($data);

            $response = ['data' => 'Role created'];
            $code = 201;

            return response()->json($response, $code);
        }
        catch(\Exception $ex) 
        {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 500);
            return response()->json(['error' => trans('messages.error')], 500);
        }
    }

    public function getRoles(Roles $roles)
    {
        $rolesRepository = new RolesRepository(new Roles);
        $data = $rolesRepository->all();

        if ($data->count() > 0) 
        {
            $dataResponse = ['data' => RolesResource::collection($data)];
            $code = 200;
        } 
        else 
        {
            $dataResponse = ['data' => 'Data Not Found'];
            $code = 404;
        }

        return response()->json($dataResponse, $code);
    }

    public function deleteRole(Request $request)
    {
        try {
            $rolesRepository = new RolesRepository(new Roles());
            $role = $rolesRepository->find($request->id);

            if ($role->delete()) 
            {
                $dataResponse = ['data' => 'Role removed'];
                $code = 200;
            } 
            else 
            {
                $dataResponse = ['data' => 'Role not removed'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } 
        catch (\Exception $ex) 
        {
            $this->log->error($ex->getMessage());
            // return response()->json(['error' => 'ERROR'], 422);
            return response()->json(['error' => trans('messages.error')], 422);
        }

    }
    
}
