<?php

namespace App\Http\Controllers;

// repositories
// use App\Http\Repositories\AppointmentRepository;
// use App\Http\Repositories\AuditionVideosRepository;

// models
use Illuminate\Support\Facades\DB;
use stdClass;

class AuditionVideosController extends Controller
{
    public function getVideos($audition_id, $round_id)
    {
        try {

            $isOnline = DB::table('auditions')
                ->where('id', $audition_id)
                ->value('online');

            $AppointmentIds = DB::table('appointments')
                ->where('auditions_id', $audition_id)
                ->where('round', $round_id)
                ->pluck('appointments.id');

            if ($isOnline) {
                $AuditionVideos = DB::table('online_media_auditions AS OMA')
                    ->select('OMA.id', 'OMA.name', 'OMA.url', 'UD.first_name', 'UD.user_id', 'R.url AS image', 'US.slots_id', 'US.favorite', 'US.roles_id')
                    ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'OMA.performer_id')
                    ->leftJoin('resources AS R', 'R.resource_id', '=', 'OMA.performer_id')
                    ->leftJoin('user_slots AS US', 'US.user_id', '=', 'OMA.performer_id')
                    ->where('OMA.type', 'video')
                    ->where('R.type', 'cover')
                    ->where('R.resource_type', '=', 'App\Models\User')
                    ->whereIn('OMA.appointment_id', $AppointmentIds)
                    ->get();
            } else {
                $AuditionVideos = DB::table('audition_videos AS AV')
                    ->select('AV.id', 'AV.name', 'AV.url', 'UD.first_name', 'UD.user_id', 'R.url AS image', 'AV.slot_id AS slots_id', 'US.favorite', 'US.roles_id')
                // ->select('AV.id', 'AV.url', 'UD.first_name', 'UD.user_id', 'R.url AS image',  'US.slots_id',   'US.favorite', 'US.roles_id', 'AV.appointment_id', 'AV.contributors_id', 'AV.slot_id')
                    ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'AV.user_id')
                    ->leftJoin('resources AS R', 'R.resource_id', '=', 'AV.user_id')
                    ->leftJoin('user_slots AS US', function ($join) {
                        $join->on('US.user_id', '=', 'AV.user_id')
                            ->on('US.slots_id', '=', 'AV.slot_id');
                    })
                    ->where('R.type', 'cover')
                    ->where('R.resource_type', '=', 'App\Models\User')
                    ->whereIn('AV.appointment_id', $AppointmentIds)
                    ->get();
            }

            // $data = $AuditionVideos->unique(['slots_id', 'user_id']);
            $data = $AuditionVideos;

            foreach ($data as $video) {
                $video->performer = new stdClass();
                $video->performer->name = $video->first_name;
                $video->performer->user_id = $video->user_id;
                $video->performer->slots_id = $video->slots_id;
                $video->performer->favorite = $video->favorite;
                $video->performer->roles_id = $video->roles_id;
                $video->performer->image = $video->image;
            }

            if ($AuditionVideos->count() == 0) {
                // throw new NotFoundException('Not Found Data');
                // return response()->json(['data' => [], 'message' => 'Not Found Data'], 400);
                return response()->json(['data' => [], 'message' => trans('messages.data_not_found')], 400);
            }
            return response()->json(['data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => []], 404);
        }
    }
}
