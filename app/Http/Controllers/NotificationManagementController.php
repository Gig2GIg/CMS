<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;

use App\Http\Controllers\Utils\LogManger;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Notification\NotificationSettingUserRepository;
use App\Models\Notifications\NotificationSettingUser;
use App\Http\Requests\Notification\NotificationSettingUserRequest;
use Illuminate\Http\Request;


class NotificationManagementController extends Controller
{
    protected $log;
    protected $collection;

    public function __construct()
    {
        $this->middleware('jwt');
    }
    
    public function update(NotificationSettingUserRequest $request)
    {
        try {
            $data = [
                'status' => $request->status
            ];
            $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
            $notificationSettingUserResult = $notificationSettingUserRepo->find($request->id);
    
            if ($notificationSettingUserResult->update($data)) {
                return response()->json([], 204);
            } else {
                return response()->json(['data' => "Not found Data"], 422);
            }   
        
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);  
        }
    }

}
