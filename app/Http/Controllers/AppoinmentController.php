<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ManageDates;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Models\Appointments;
use App\Models\Feedbacks;
use App\Models\AuditionLog;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\UserAuditions;
use App\Models\UserSlots;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppoinmentController extends Controller
{
    /**
     * @var ManageDates
     */
    protected $toDate;

    public function getRounds(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->findbyparam('auditions_id', $request->audition_id)->get();
            if ($data->count() == 0) {
                throw new NotFoundException('Not Found Data');
            }
            return response()->json(['data' => $data->toArray()], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => []], 404);
        }
    }

    public function createRound(Request $request)
    {
        if (!$request->online) {
            return $this->notOnlineSubmision($request);
        }

        return $this->onlineSubmision($request);
    }

    public function reOpenRound(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);

            if($data){
                if($data->status == 1){
                    $res = ['message' => trans('messages.round_opened_already')];
                    $code = 200;    
                } else {
                    $existingStarted = $repo->findbyparams(['auditions_id' => $data->auditions_id, "status" => 1])->first(); 

                    if(!$existingStarted){
                        $update = $data->update([
                            'status' => true,
                            'is_group_open' => 0
                        ]);

                        if($update){
                            $repoUserAuditions = new UserAuditions();
                            $dataUserAuditions = $repoUserAuditions->all()
                                ->where('appointment_id', $request->appointment_id);
                            if ($dataUserAuditions->count() > 0) {
                                $dataUserAuditions->each(function ($element) {
                                    $element->update(['type' => 1]);
                                });
                            }

                            //tracking the audition changes                            
                            AuditionLog::insert([
                                'audition_id' => $data->auditions_id,
                                'edited_by' => $this->getUserLogging(),
                                'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                                'key' => 'Round ' . $data->round,
                                'old_value' => 'Closed',
                                'new_value' => 'Re-Opened'
                            ]);

                            $res = ['message' => trans('messages.round_reopened'), 'data' => $data];
                            $code = 200;    
                        }else{
                            $res = ['message' => trans('messages.something_went_wrong')];
                            $code = 400;    
                        }
                    } else {
                        $res = ['message' => 'To re-open this round please close the active Round ' .$existingStarted->round, 'data' => $existingStarted->round];
                        $code = 400;
                    }
                }
            }else{
                $res = ['message' => trans('messages.data_not_found')];
                $code = 400;
            }

            return response()->json($res, $code);  
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            $this->log->error($exception->getFile());
            return response()->json(['message' => trans('messages.round_not_reopened'), 'data' => []], 406);
        }
    }

    public function updateRound(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);
            $createdNextAuditionRound = $repo->findbyparams(['auditions_id' => $data->auditions_id, "round" => (($data->round) + 1)])->first();
            $update = $data->update([
                'status' => $request->status,
                'is_group_open' => 0
            ]);

            //tracking the audition changes                            
            AuditionLog::insert([
                'audition_id' => $data->auditions_id,
                'edited_by' => $this->getUserLogging(),
                'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                'key' => 'Round ' . $data->round,
                'old_value' => 'Opened',
                'new_value' => 'Closed'
            ]);

            if($createdNextAuditionRound && $createdNextAuditionRound->count() > 0){

                $createdNextAuditionRound->update([
                    'status' => 1
                ]);

                //tracking the audition changes                            
                AuditionLog::insert([
                    'audition_id' => $data->auditions_id,
                    'edited_by' => $this->getUserLogging(),
                    'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                    'key' => 'Round ' . $createdNextAuditionRound->round,
                    'old_value' => 'Closed',
                    'new_value' => 'Opened'
                ]);

                $roleRepo = new RolesRepository(new Roles());
                $roleDataRepo = $roleRepo->findbyparam('auditions_id', $data->auditions_id);

                if ($roleDataRepo->count() > 0) {
                    
                    $roles = $roleDataRepo->all();
                    $auditionRoleId = $roles[0]->id;

                    //Get all users who got starred feedback in previous round
                    $feedbacksRepo = new FeedbackRepository(new Feedbacks());
                    $repoDatafeedbacks = $feedbacksRepo->findbyparams(['appointment_id' => $request->appointment_id, 'favorite' => 1]);

                    //getting those with future kept flag
                    $slotRepo = new UserSlots();
                    $slotData = $slotRepo->where('appointment_id', $request->appointment_id)
                    ->where('future_kept', 1)
                    ->get();

                    if ($repoDatafeedbacks->count() > 0) {
                        $feedbackData = $repoDatafeedbacks->get();

                        foreach ($feedbackData as $feedback) {
                            $dataToInsert = [
                                'user_id' => $feedback->user_id, 
                                'appointment_id' => $createdNextAuditionRound->id, 
                                'rol_id' => $auditionRoleId, 
                                'type' => '1'];
                            $UserAudition = new UserAuditions();
                            $UserAudition->updateOrCreate([
                                'user_id' => $feedback->user_id, 
                                'appointment_id' => $createdNextAuditionRound->id, 
                                'rol_id' => $auditionRoleId],
                                $dataToInsert
                            );
                        }
                    }

                    if ($slotData->count() > 0) {
                        foreach ($slotData as $uslot) {
                            $dataToInsert = [
                                'user_id' => $uslot->user_id, 
                                'appointment_id' => $createdNextAuditionRound->id, 
                                'rol_id' => $auditionRoleId, 
                                'type' => '1'];
                            $UserAudition = new UserAuditions();
                            $UserAudition->updateOrCreate([
                                'user_id' => $uslot->user_id, 
                                'appointment_id' => $createdNextAuditionRound->id, 
                                'rol_id' => $auditionRoleId],
                                $dataToInsert
                            );
                        }
                    }

                    $repoFeeadback = Feedbacks::all()
                        ->where('appointment_id', $request->appointment_id)
                        ->where('favorite', true);
                    if ($repoFeeadback->count() >= 0) {
                        $idsFeedback = $repoFeeadback->pluck('user_id');
                        $repoUserAuditions = new UserAuditions();
                        $dataUserAuditions = $repoUserAuditions->all()->whereNotIn('user_id', $idsFeedback)
                            ->where('appointment_id', $createdNextAuditionRound->id);
                        if ($dataUserAuditions->count() > 0) {
                            $dataUserAuditions->each(function ($element) use($request) {
                                if(UserSlots::where('user_id', $element->user_id)->where('appointment_id', $request->appointment_id)->where('future_kept', 1)->where('status', 'checked')->get()->count() == 0){
                                    if($element->slot_id){
                                        Slots::find($element->slot_id)->update(['status' => 0]);
                                    } 
                                    $element->update(['type' => 3, 'slot_id' => NULL]);
                                    UserSlots::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->where('future_kept', 0)->where('status', 'checked')->delete();
                                    if(Feedbacks::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->get()->count() > 0){
                                        Feedbacks::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->update(['favorite' => 0]);
                                    }
                                } 
                            });
                        }
                    }
                }
            }

            if ($update) {
                $repoFeeadback = Feedbacks::all()
                    ->where('appointment_id', $request->appointment_id)
                    ->where('favorite', true);
                if ($repoFeeadback->count() >= 0) {
                    $idsFeedback = $repoFeeadback->pluck('user_id');
                    $repoUserAuditions = new UserAuditions();
                    $dataUserAuditions = $repoUserAuditions->all()->whereNotIn('user_id', $idsFeedback)
                        ->where('appointment_id', $request->appointment_id);
                    if ($dataUserAuditions->count() > 0) {
                        $dataUserAuditions->each(function ($element) {
                            $element->update(['type' => 3]);
                        });
                    }
                }
                return response()->json(['message' => trans('messages.round_closed_successfully'), 'data' => $data], 200);
                // return response()->json(['message' => 'Round closed successfully', 'data' => $data], 200);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.round_not_close'), 'data' => []], 406);
            // return response()->json(['message' => 'Round not close ', 'data' => []], 406);
        }
    }

    public function dataToSlotsProcess($appointment, $slot): array
    {
        return [
            'appointment_id' => $appointment->id,
            'time' => $slot['time'],
            'number' => $slot['number'] ?? null,
            'status' => false,
            'is_walk' => $slot['is_walk'],
        ];
    }

    public function getSlots(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $repoData = $repo->find($request->appointment_id);
            $data = $repoData->slot->sortBy('time');
            if ($data->count() === 0) {
                throw new NotFoundException('Not found data');
            }
            return response()->json(['message' => trans('messages.list_by_slots'), 'data' => $data], 200);
            // return response()->json(['message' => 'list by slots', 'data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.data_not_found'), 'data' => []], 404);
            // return response()->json(['message' => 'Not found data', 'data' => []], 404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws NotFoundException
     */
    public function notOnlineSubmision(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->log->info("CREATE ROUND::" . $request);
        $repoClosedA = new AppointmentRepository(new Appointments());
        $repoDataA = $repoClosedA->findbyparam('auditions_id', $request->audition_id);
        if ($repoDataA->count() > 0) {
            $repoDataA->update([
                'status' => false,
            ]);
        }
        $lastid = $repoDataA->orderBy('id', 'desc')->first();
        $this->log->info("CREATE ROUND LAST ID APPO");
        $this->log->info($lastid);
        $this->toDate = new ManageDates();
        try {
            $lat = NULL;
            $lng = NULL;
            if($request->has('location')){
                $lat = $request->location['latitude'];
                $lng = $request->location['longitude']; 
            }
            $repo = new AppointmentRepository(new Appointments());
            $appointment = [
                'date' => $this->toDate->transformDate($request->date),
                'time' => $request->time,
                'location' => json_encode($request->location),
                'lat' => $lat,
                'lng' => $lng,
                'slots' => $request->number_slots,
                'type' => $request->type,
                'length' => $request->length,
                'start' => $request->start,
                'end' => $request->end,
                'round' => $request->round,
                'status' => true,
                'auditions_id' => $request->audition_id,
            ];
            if (is_null($request->slots)) {
                throw new \Exception('Not Slots to process');
            }
            $data = $repo->create($appointment);

            //tracking the audition changes                            
            AuditionLog::insert([
                'audition_id' => $request->audition_id,
                'edited_by' => $this->getUserLogging(),
                'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                'key' => 'Round ' . $request->round,
                'old_value' => '--',
                'new_value' => 'Opened'
            ]);

            $newAppointmentId = $data->id;
            // echo($data);die;
            $this->log->info("CREATE ROUUND NEW ROUND DATA");
            $this->log->info($data);
            //            $repoFeeadback = Feedbacks::all()
            //                ->where('appointment_id', $lastid->id)
            //                ->where('favorite', true);
            //
            //            if ($repoFeeadback->count() > 0) {
            //                $repoFeeadback->each(function ($item) use ($data) {
            //                    $item->update(['appointment_id' => $data->id]);
            //                });
            //            }

            // Check is it for next round or not
            if ($request->round > 1) {
                $AuditionId = $request->audition_id;

                $roleRepo = new RolesRepository(new Roles());
                $roleDataRepo = $roleRepo->findbyparam('auditions_id', $AuditionId);

                if ($roleDataRepo->count() > 0) {
                    // dd($roleDataRepo);
                    $roles = $roleDataRepo->all();
                    // dd($roles[0]->id);
                    $auditionRoleId = $roles[0]->id;

                    $repoPreviousAppointments = new AppointmentRepository(new Appointments());
                    $repoDataPreviousAppointments = $repoPreviousAppointments->findbyparams(['auditions_id' => $request->audition_id, 'round' => ($request->round - 1)]);

                    if ($repoDataPreviousAppointments->count() > 0) {
                        $lasApponitment = $repoDataPreviousAppointments->get();

                        $lasApponitmentId = $lasApponitment[0]->id;

                        //Get all users who got feedback in previous round
                        $feedbacksRepo = new FeedbackRepository(new Feedbacks());
                        $repoDatafeedbacks = $feedbacksRepo->findbyparams(['appointment_id' => $lasApponitmentId, 'favorite' => 1]);

                        if ($repoDatafeedbacks->count() > 0) {
                            $feedbackData = $repoDatafeedbacks->get();

                            $allFeddbackUsers = array();
                            foreach ($feedbackData as $feedback) {
                                $allFeddbackUsers[] = $feedback->user_id;

                                $dataToInsert = [
                                    'user_id' => $feedback->user_id, 
                                    'appointment_id' => $newAppointmentId, 
                                    'rol_id' => $auditionRoleId, 
                                    'type' => '1'];
                                $UserAudition = new UserAuditions();
                                // $UserAudition->create($dataToInsert);
                                $UserAudition->updateOrCreate([
                                    'user_id' => $feedback->user_id, 
                                    'appointment_id' => $newAppointmentId, 
                                    'rol_id' => $auditionRoleId],
                                    $dataToInsert
                                );
                                // $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                                // $dataSlotRepo->create([
                                //     'user_id' => $feedback->user_id,
                                //     'appointment_id' => $newAppointmentId,
                                //     'slots_id' => null,
                                //     'roles_id' => $auditionRoleId,
                                //     'status' => 1,
                                // ]);
                            }
                        }

                        //getting those with future kept flag
                        $slotRepo = new UserSlots();
                        $slotData = $slotRepo->where('appointment_id', $lasApponitmentId)
                        ->where('future_kept', 1)
                        ->get();

                        if ($slotData->count() > 0) {
                            foreach ($slotData as $uslot) {
                                $dataToInsert = [
                                    'user_id' => $uslot->user_id, 
                                    'appointment_id' => $newAppointmentId, 
                                    'rol_id' => $auditionRoleId, 
                                    'type' => '1'];
                                $UserAudition = new UserAuditions();
                                $UserAudition->updateOrCreate([
                                    'user_id' => $uslot->user_id, 
                                    'appointment_id' => $newAppointmentId, 
                                    'rol_id' => $auditionRoleId],
                                    $dataToInsert
                                );
                                // $UserAudition->create($dataToInsert);
                            }
                        }

                        $repoFeeadback = Feedbacks::all()
                            ->where('appointment_id', $lasApponitmentId)
                            ->where('favorite', true);
                        if ($repoFeeadback->count() >= 0) {
                            $idsFeedback = $repoFeeadback->pluck('user_id');
                            $repoUserAuditions = new UserAuditions();
                            $dataUserAuditions = $repoUserAuditions->all()->whereNotIn('user_id', $idsFeedback)
                                ->where('appointment_id', $newAppointmentId);
                            if ($dataUserAuditions->count() > 0) {
                                $dataUserAuditions->each(function ($element) use($lasApponitmentId) {
                                    if(UserSlots::where('user_id', $element->user_id)->where('appointment_id', $lasApponitmentId)->where('future_kept', 1)->where('status', 'checked')->get()->count() == 0){
                                        if($element->slot_id){
                                            Slots::find($element->slot_id)->update(['status' => 0]);
                                        }    
                                        $element->update(['type' => 3, 'slot_id' => null]);
                                        UserSlots::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->where('future_kept', 0)->where('status', 'checked')->delete();
                                        if(Feedbacks::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->get()->count() > 0){
                                            Feedbacks::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->update(['favorite' => 0]);
                                        }
                                    } 
                                    // $element->delete();
                                });
                            }
                        }
                    }
                }
            }

            foreach ($request['slots'] as $slot) {
                $dataSlots = $this->dataToSlotsProcess($data, $slot);
                $slotsRepo = new SlotsRepository(new Slots());
                $slotsRepo->create($dataSlots);
            }
            // return response()->json(['message' => 'Round Create', 'data' => $data], 200);
            return response()->json(['message' => trans('messages.round_create'), 'data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => $exception->getMessage(), 'data' => []], 406);
        }
    }

    public function onlineSubmision(Request $request): \Illuminate\Http\JsonResponse
    {
        $repoClosedA = new AppointmentRepository(new Appointments());
        $repoDataA = $repoClosedA->findbyparam('auditions_id', $request->audition_id);
        if ($repoDataA->count() > 0) {
            $repoDataA->update([
                'status' => false,
            ]);
        }
        $lastid = $repoDataA->orderBy('id', 'desc')->first();
        $this->toDate = new ManageDates();
        try {
            $lat = NULL;
            $lng = NULL;
            if($request->has('location')){
                $lat = $request->location['latitude'];
                $lng = $request->location['longitude']; 
            }
            $repo = new AppointmentRepository(new Appointments());
            $appointment = [
                'date' => $this->toDate->transformDate($request->date),
                'time' => $request->time,
                'location' => json_encode($request->location),
                'lat' => $lat,
                'lng' => $lng,
                'slots' => $request->number_slots,
                'type' => $request->type,
                'length' => $request->length,
                'start' => $request->start,
                'end' => $request->end,
                'round' => $request->round,
                'status' => true,
                'auditions_id' => $request->audition_id,
            ];
            $data = $repo->create($appointment);

            //tracking the audition changes                            
            AuditionLog::insert([
                'audition_id' => $request->audition_id,
                'edited_by' => $this->getUserLogging(),
                'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                'key' => 'Round ' . $request->round,
                'old_value' => '--',
                'new_value' => 'Opened'
            ]);

            $testDebug = array();
            $newAppointmentId = $data->id;
            $testDebug['newAppointmentId'] = $newAppointmentId;

            // Check is it for next round or not
            if ($request->round > 1) {
                $testDebug['In Round'] = $request->round;
                $AuditionId = $request->audition_id;

                $roleRepo = new RolesRepository(new Roles());
                $roleDataRepo = $roleRepo->findbyparam('auditions_id', $AuditionId);

                if ($roleDataRepo->count() > 0) {
                    // dd($roleDataRepo);
                    $roles = $roleDataRepo->all();
                    // dd($roles[0]->id);
                    $auditionRoleId = $roles[0]->id;
                    $testDebug['Role Id'] = $auditionRoleId;

                    $repoPreviousAppointments = new AppointmentRepository(new Appointments());
                    $repoDataPreviousAppointments = $repoPreviousAppointments->findbyparams(['auditions_id' => $request->audition_id, 'round' => ($request->round - 1)]);

                    if ($repoDataPreviousAppointments->count() > 0) {
                        $lasApponitment = $repoDataPreviousAppointments->get();

                        $lasApponitmentId = $lasApponitment[0]->id;
                        $testDebug['last appointment Id'] = $lasApponitmentId;

                        //Get all users who got feedback in previous round
                        $feedbacksRepo = new FeedbackRepository(new Feedbacks());
                        $repoDatafeedbacks = $feedbacksRepo->findbyparams(['appointment_id' => $lasApponitmentId, 'favorite' => 1]);

                        if ($repoDatafeedbacks->count() > 0) {
                            $feedbackData = $repoDatafeedbacks->get();

                            $allFeddbackUsers = array();
                            foreach ($feedbackData as $feedback) {
                                $allFeddbackUsers[] = $feedback->user_id;

                                // $testDebug['dataToInsert'] = $dataToInsert;
                                $UserAudition = new UserAuditionsRepository(new UserAuditions());

                                $dataToInsert = [
                                    'user_id' => $feedback->user_id,
                                    'appointment_id' => $newAppointmentId,
                                    'rol_id' => $auditionRoleId,
                                    'type' => '1',
                                ];

                                $exists = $UserAudition->findbyparams([
                                    'user_id' => $feedback->user_id,
                                    'appointment_id' => $newAppointmentId,
                                    'rol_id' => $auditionRoleId
                                ])->get();
                                if($exists->count() == 0){
                                    $UserAudition->create($dataToInsert);

                                    $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                                    $dataSlotRepo->create([
                                        'user_id' => $feedback->user_id,
                                        'appointment_id' => $newAppointmentId,
                                        'slots_id' => factory(Slots::class)->create([
                                            'appointment_id' => $newAppointmentId,
                                            'time' => "00:00",
                                            'status' => false,
                                        ])->id,
                                        'roles_id' => $auditionRoleId,
                                        'status' => 2,
                                    ]);
                                }

                                // $testDebug['dataToInsert'] = $dataToInsert;
                                // $testDebug['after'] = "After";
                                // $testDebug['UserAudition'] = $UserAudition;
                            }
                        }

                        //getting those with future kept flag
                        $slotRepo = new UserSlots();
                        $slotData = $slotRepo->where('appointment_id', $lasApponitmentId)
                        ->where('future_kept', 1)
                        ->get();

                        if ($slotData->count() > 0) {
                            foreach ($slotData as $uslot) {
                                $dataToInsert = [
                                    'user_id' => $uslot->user_id, 
                                    'appointment_id' => $newAppointmentId, 
                                    'rol_id' => $auditionRoleId, 
                                    'type' => '1'];
                                $UserAudition = new UserAuditionsRepository(new UserAuditions());

                                $exists = $UserAudition->findbyparams([
                                    'user_id' => $uslot->user_id, 
                                    'appointment_id' => $newAppointmentId, 
                                    'rol_id' => $auditionRoleId, 
                                ])->get();
                                if($exists->count() == 0){
                                    $UserAudition->create($dataToInsert);

                                    $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                                    $dataSlotRepo->create([
                                        'user_id' => $uslot->user_id,
                                        'appointment_id' => $newAppointmentId,
                                        'slots_id' => factory(Slots::class)->create([
                                            'appointment_id' => $newAppointmentId,
                                            'time' => "00:00",
                                            'status' => false,
                                        ])->id,
                                        'roles_id' => $auditionRoleId,
                                        'status' => 2,
                                    ]);
                                }
                            }
                        }

                        $repoFeeadback = Feedbacks::all()
                            ->where('appointment_id', $lasApponitmentId)
                            ->where('favorite', true);
                        if ($repoFeeadback->count() >= 0) {
                            $idsFeedback = $repoFeeadback->pluck('user_id');
                            $repoUserAuditions = new UserAuditions();
                            $dataUserAuditions = $repoUserAuditions->all()->whereNotIn('user_id', $idsFeedback)
                                ->where('appointment_id', $newAppointmentId);
                            if ($dataUserAuditions->count() > 0) {
                                $dataUserAuditions->each(function ($element) use($lasApponitmentId) {
                                    if(UserSlots::where('user_id', $element->user_id)->where('appointment_id', $lasApponitmentId)->where('future_kept', 0)->where('status', 'checked')->get()->count() == 0){
                                        if($element->slot_id){
                                            Slots::find($element->slot_id)->update(['status' => 0]);
                                        } 
                                        $element->update(['type' => 3, 'slot_id' => NULL]);
                                        UserSlots::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->where('future_kept', 0)->where('status', 'checked')->delete();
                                        if(Feedbacks::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->get()->count() > 0){
                                                Feedbacks::where('user_id', $element->user_id)->where('appointment_id', $element->appointment_id)->update(['favorite' => 0]);
                                            }
                                    }
                                    // $element->delete();
                                });
                            }
                        }
                    }
                }
            }

            //            $repoFeeadback = Feedbacks::all()
            //                ->where('appointment_id', $lastid->id)
            //                ->where('favorite', true);
            //
            //            if ($repoFeeadback->count() > 0) {
            //                $repoFeeadback->each(function ($item) use ($data) {
            //                    $item->update(['appointment_id' => $data->id]);
            //                });
            //            }

            // return response()->json(['message' => 'Round create', 'data' => $data], 200);
            return response()->json(['message' => trans('messages.round_create'), 'data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.round_not_create'), 'data' => []], 406);
            // return response()->json(['message' => 'Round not create ', 'data' => []], 406);
        }
    }

    public function updateOldLocationData(){
        try {
            $data = Appointments::select('id','location')->whereNotNull('location')->where('location',"!=" ,'null')->get();

            foreach($data as $d) {
                echo $d->id;
                echo "<br/>";
                $dd = json_decode(trim($d->location, '"'), true);
                echo "<br/>";
                Appointments::where('id', $d->id)->update(['lat' => $dd['latitude'], 'lng' => $dd['longitude']]);
            }
            
            return response()->json(['data' => $data->toArray()], 200);
        } catch (\Exception $exception) {
            dd($exception);
            $this->log->error($exception->getMessage());
            return response()->json(['data' => []], 404);
        }
    }
}
