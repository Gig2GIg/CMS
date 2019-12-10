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
                ->select('OMA.id', 'OMA.name', 'OMA.url', 'OMA.performer_id')
                // ->select('OMA.id', 'OMA.name', 'OMA.url','UD.first_name','UD.user_id','R.url AS image','US.slots_id','US.favorite','US.roles_id')
                ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'OMA.performer_id')
                ->leftJoin('resources AS R', 'R.id', '=', 'OMA.performer_id')
                // ->leftJoin('user_slots AS US', 'US.user_id', '=', 'OMA.performer_id')
                ->where('OMA.type', 'video')
                ->whereIn('OMA.appointment_id', $AppointmentIds)
                ->get();

            $userData = DB::table('online_media_auditions AS OMA')
                ->select('UD.first_name', 'UD.user_id', 'R.url AS image')
                // ->select('OMA.id', 'OMA.name', 'OMA.url','UD.first_name','UD.user_id','R.url AS image','US.slots_id','US.favorite','US.roles_id')
                ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'OMA.performer_id')
                ->leftJoin('resources AS R', 'R.id', '=', 'OMA.performer_id')
                // ->leftJoin('user_slots AS US', 'US.user_id', '=', 'OMA.performer_id')
                ->where('OMA.type', 'video')
                ->whereIn('OMA.appointment_id', $AppointmentIds)
                ->get();

            // $AuditionVideos = DB::table('online_media_auditions')
            //     ->select('id', 'name', 'url','performer_id')
            //     ->where('type', 'video')
            //     ->whereIn('appointment_id', $AppointmentIds)
            //     ->get();

            $AuditionVideos->map(function ($performer) use ($AuditionVideos, $userData) {
                $performer->performer =  $userData;
                return $performer;
            });

            // print_r($AuditionVideos);
            // print_r($userData);
            // die;

            $userSlots = DB::table('user_slots')
                ->select('slots_id', 'favorite', 'roles_id')
                ->where('user_id', $this->getUserLogging())
                ->get()->first();

            $performerData =  DB::table('user_details')
                ->select('first_name as name', 'user_id')
                ->where('user_id', $this->getUserLogging())
                ->get()->first();

            // print_r($this->getUserLogging());
            // die;
            $image =  DB::table('resources')
            ->select('url as image')
            ->find($this->getUserLogging());

            $AuditionVideos->map(function ($performer) use ($performerData, $userSlots,$image) {
                $performer->performer = (object) array_merge(
                    (array) $performerData,
                    (array) $userSlots,
                    (array) $image
                );
                return $performer;
            });


            // print_r($userSlots);
            // die;
     
            // print_r($userData);
            // print_r($userSlots);
            // die;

            // $AuditionVideos->map(function ($performer) use ($userData, $userSlots) {
            //     $performer->performer = (object) array_merge(
            //         (array) $userData,
            //         (array) $userSlots
            //     );
            //     return $performer;
            // });

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

    // //getRoundsWiseVideoList
    // public function getVideos($audition_id, $round_id)
    // {
    //     try {

    //         $AppointmentIds = DB::table('appointments')
    //             ->where('auditions_id', $audition_id)
    //             ->where('round', $round_id)
    //             ->pluck('appointments.id');
    //         // var_dump($AppointmentIds);
    //         // foreach($AppointmentIds as $k){
    //         //     echo $k;
    //         // }
    //         // die;

    //         // $AuditionVideos = DB::table('online_media_auditions')
    //         // // ->select('OMA.id', 'OMA.name', 'OMA.url', 'UD.first_name', 'UD.user_id', 'R.url AS image')
    //         // ->select('online_media_auditions.name','online_media_auditions.appointment_id')
    //         // ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'online_media_auditions.performer_id')
    //         // ->leftJoin('resources AS R', 'R.id', '=', 'online_media_auditions.performer_id')
    //         // ->leftJoin('user_slots AS US', 'US.user_id', '=', 'online_media_auditions.performer_id')
    //         // // ->whereIn('US.appointment_id', $AppointmentIds)
    //         // ->where('online_media_auditions.type', 'video')
    //         // ->whereIn('online_media_auditions.appointment_id', $AppointmentIds)
    //         // // ->where('US.appointment_id', $audition_id)
    //         // // ->where('US.user_id', 'OMA.performer_id')
    //         // ->groupBy('appointment_id')
    //         // // ->toSql();
    //         // // ->distinct('OMA.appointment_id')
    //         // ->get();


    //         $AuditionVideos = DB::table('online_media_auditions AS OMA')
    //             // ->select('OMA.id', 'OMA.name', 'OMA.url', 'UD.first_name', 'UD.user_id', 'R.url AS image')
    //             ->select('OMA.appointment_id','OMA.name', 'OMA.url', 'UD.first_name', 'UD.user_id', 'R.url AS image', 'US.slots_id', 'US.favorite', 'US.roles_id')
    //             ->leftJoin('user_details AS UD', 'UD.user_id', '=', 'OMA.performer_id')
    //             ->leftJoin('resources AS R', 'R.id', '=', 'OMA.performer_id')
    //             ->leftJoin('user_slots AS US', 'US.user_id', '=', 'OMA.performer_id')
    //             // ->whereIn('US.appointment_id', $AppointmentIds)
    //             ->where('OMA.type', 'video')
    //             ->whereIn('OMA.appointment_id', $AppointmentIds)
    //             // ->where('US.appointment_id', $audition_id)
    //             // ->where('US.user_id', 'OMA.performer_id')
    //             ->groupBy('OMA.appointment_id')

    //             // ->toSql();
    //             // ->distinct('OMA.appointment_id')

    //             ->get();

    //         // $AuditionVideos = DB::table('online_media_auditions')
    //         //     ->select('id', 'name', 'url','performer_id')
    //         //     ->where('type', 'video')
    //         //     ->whereIn('appointment_id', $AppointmentIds)
    //         //     ->get();

    //         // print_r($AuditionVideos);
    //         // die;


    //         if ($AuditionVideos->count() == 0) {
    //             // throw new NotFoundException('Not Found Data');
    //             return response()->json(['data' => [], 'message' => 'Not Found Data'], 400);
    //         }
    //         return response()->json(['data' => $AuditionVideos->toArray()], 200);
    //     } catch (\Exception $exception) {
    //         $this->log->error($exception->getMessage());
    //         return response()->json(['data' => []], 404);
    //     }
    // }
}
