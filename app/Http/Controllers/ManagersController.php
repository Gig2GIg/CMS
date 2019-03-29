<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\UserManagerRepository;
use App\Http\Requests\ManagersRequest;
use App\Http\Resources\ManagerResource;
use App\Models\UserManager;
use Illuminate\Http\Request;

class ManagersController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(ManagersRequest $request)
    {
        try {
            $data = [
                'name'=> $request->name,
                'company'=>  $request->company,
                'type'=>     $request->type,
                'notifications'=>  $request->notifications,
                'email'=>         $request->email,
                'user_id' => $this->getUserLogging(),
            ];
            $repo = new UserManagerRepository(new UserManager());
            $repo->create($data);

            $dataResponse = ['data' => 'Manager created'];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 500);
        }


    }


    public function byUser()
    {
        try {
            $repo = new UserManagerRepository(new UserManager());
            $data = $repo->findbyparam('user_id',$this->getUserLogging());
            $dataResponse = ['data' => new ManagerResource($data)];
            $code = 200;
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    public function update(ManagersRequest $request)
    {
        try {
            $data = [
                'name'=> $request->name,
                'company'=>  $request->company,
                'type'=>     $request->type,
                'notifications'=>  $request->notifications,
                'email'=>         $request->email,

            ];
            $repo = new UserManagerRepository(new UserManager());
            $dataManager = $repo->find(request('id'));
            $result = $dataManager->update($data);
            if ($result) {
                $dataResponse = ['data' => 'Manager updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Manager not updated'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

}
