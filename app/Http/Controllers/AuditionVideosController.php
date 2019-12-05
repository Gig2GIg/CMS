<?php

namespace App\Http\Controllers;

// repositories
// use App\Http\Repositories\AppointmentRepository;
// use App\Http\Repositories\AuditionVideosRepository;

// models
use Illuminate\Support\Facades\DB;

class AuditionVideosController extends Controller
{

    //getRoundsWiseVideoList
    public function getVideos($audition_id, $round_id)
    {
        try {
            $AppointmentIds = DB::table('appointments')
                ->where('auditions_id', $audition_id)
                ->where('round', $round_id)
                ->pluck('appointments.id');

            $AuditionVideos = DB::table('online_media_auditions')
                ->select('id','name', 'url')
                ->where('type','video')
                ->whereIn('appointment_id', $AppointmentIds)
                ->get();

            if ($AuditionVideos->count() == 0) {
                // throw new NotFoundException('Not Found Data');
                return response()->json(['data' => [], 'message' => 'Not Found Data'], 400);
            }
            return response()->json(['data' => $AuditionVideos->toArray()], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => []], 404);
        }
    }
}
