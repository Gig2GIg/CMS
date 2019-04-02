<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\UserAuditionsRepository;
use App\Models\UserAuditions;
use Exception;
use Illuminate\Http\Request;

class AuditionManagementController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function saveAudition(Request $request)
    {

        try {
            $userAuditions = new UserAuditionsRepository(new UserAuditions());
            $data = [
                'user_id' => $this->getUserLogging(),
                'auditions_id' => $request->auditions,
                'rol_id' => $request->rol,
                'type' => $request->type
            ];
            $userAuditions->create($data);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Not Saved'], 500);
        }

    }
}
