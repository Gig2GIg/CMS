<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\NotFoundException;
use Illuminate\Database\QueryException;

use App\Http\Controllers\Utils\LogManger;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Notification\NotificationHistoryRepository;
use App\Models\Notifications\NotificationHistory;
use App\Models\User;
use App\Http\Requests\NotificationPushKeyRequest;
use App\Http\Resources\NoficationsResource;
use Illuminate\Http\Request;
use App\Http\Repositories\UserRepository;

class NotificationsController extends Controller
{
    protected $log;
    protected $collection;

    public function __construct()
    {
        $this->middleware('jwt');
    }


    public function getHistory(Request $request)
    {
        try {

            $userRepo = new UserRepository(new User());
            $user = $userRepo->find($this->getUserLogging());

            $count = count($user->notification_history);
            if ($count > 0) {
                $responseData = NoficationsResource::collection($user->notification_history->sortByDesc('created_at'));
                return response()->json(['data' => $responseData], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function readHistory(Request $request)
    {
        try {

            $userRepo = new UserRepository(new User());
            $user = $userRepo->find($this->getUserLogging());

            $count = count($user->notification_history);

            foreach ($user->notification_history as $notification) {
                $notification->update(['status' => 'read']);
            }

            $responseData = NoficationsResource::collection($user->notification_history->sortByDesc('created_at'));

            return response()->json(['data' => 'Success'], 204);

        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }



    public function update(NotificationPushKeyRequest $request)
    {
        try {
            $data = [
                'pushkey' => $request->pushkey
            ];

            $userRepo = new UserRepository(new User());
            $userResult = $userRepo->find($this->getUserLogging());

            if ($userResult->update($data)) {
                return response()->json([], 204);
            } else {
                return response()->json(['data' => "Record not  created"], 422);
            }

        } catch (NotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }
    }

    public function delete(Request $request)
    {
        try {
            $repo = new NotificationHistoryRepository(new NotificationHistory());

            $data = $repo->find($request->id)->delete();

            if ($data) {
                $dataResponse = ['data' => 'Notification removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Notification not removed'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }


}

