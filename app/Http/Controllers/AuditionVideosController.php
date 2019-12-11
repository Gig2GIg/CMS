<?php

namespace App\Http\Controllers;

// repositories
// use App\Http\Repositories\AppointmentRepository;
// use App\Http\Repositories\AuditionVideosRepository;

// models
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Resources\AppointmentResource;
use App\Models\UserSlots;
use stdClass;

class AuditionVideosController extends Controller
{



    public function getVideos($audition_id, $round_id)
    {
        try {
            $AppointmentIds = DB::table('appointments')
                ->where('auditions_id', $audition_id)
                ->where('round', $round_id)
                ->pluck('appointments.id');

            $AuditionVideos = DB::table('online_media_auditions AS OMA')
                // ->select('OMA.id', 'OMA.name', 'OMA.url', 'OMA.performer_id', 'UD.first_name', 'UD.user_id', 'R.url AS image')
                ->select('OMA.id', 'OMA.name', 'OMA.url', 'UD.first_name', 'UD.user_id', 'R.url AS image', 'US.slots_id', 'US.favorite', 'US.roles_id')
                ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'OMA.performer_id')
                ->leftJoin('resources AS R', 'R.id', '=', 'OMA.performer_id')
                ->leftJoin('user_slots AS US', 'US.user_id', '=', 'OMA.performer_id')
                ->where('OMA.type', 'video')
                ->whereIn('OMA.appointment_id', $AppointmentIds)
                ->get();
        
            /**
             * TODO:
             * Handle an empty array
             * This can be handeled if we use froup by in query
             */
            $result = array();
            $added_user_ids = array();

            foreach ($AuditionVideos as $video) {
                if (!in_array($video->user_id, $added_user_ids)) {
                    $added_user_ids[] = $video->user_id;
                    $video->performer = new stdClass();
                    $video->performer->name = $video->first_name;
                    $video->performer->user_id = $video->user_id;
                    $video->performer->slots_id = $video->slots_id;
                    $video->performer->favorite = $video->favorite;
                    $video->performer->roles_id = $video->roles_id;
                    $video->performer->image = $video->image;
                    $result[] = $video;
                }
            }            

            if ($AuditionVideos->count() == 0) {
                // throw new NotFoundException('Not Found Data');
                return response()->json(['data' => [], 'message' => 'Not Found Data'], 400);
            }
            return response()->json(['data' => $result], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => []], 404);
        }
    }
}
