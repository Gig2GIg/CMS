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
use App\Models\Roles;
use App\Models\Slots;
use App\Models\UserAuditions;
use App\Models\UserSlots;
use Illuminate\Http\Request;

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

    public function updateRound(Request $request)
    {
        try {
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);
            $update = $data->update(['status' => $request->status]);
            if ($update) {
                $repoFeeadback = Feedbacks::all()
                    ->where('appointment_id', $request->appointment_id)
                    ->where('favorite', true);
                if ($repoFeeadback->count() >= 0) {
                    $idsFeedback = $repoFeeadback->pluck('user_id');
                    $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
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
            $repo = new AppointmentRepository(new Appointments());
            $appointment = [
                'date' => $this->toDate->transformDate($request->date),
                'time' => $request->time,
                'location' => json_encode($request->location),
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

                                $dataToInsert = ['user_id' => $feedback->user_id, 'appointment_id' => $newAppointmentId, 'rol_id' => $auditionRoleId, 'type' => '1'];
                                $UserAudition = new UserAuditionsRepository(new UserAuditions());
                                $UserAudition->create($dataToInsert);

                                $dataSlotRepo = new UserSlotsRepository(new UserSlots());
                                $dataSlotRepo->create([
                                    'user_id' => $feedback->user_id,
                                    'appointment_id' => $newAppointmentId,
                                    'slots_id' => null,
                                    'roles_id' => $auditionRoleId,
                                    'status' => 1,
                                ]);
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
            $repo = new AppointmentRepository(new Appointments());
            $appointment = [
                'date' => $this->toDate->transformDate($request->date),
                'time' => $request->time,
                'location' => json_encode($request->location),
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
            $testDebug = array();
            $newAppointmentId = $data->id;
            $testDebug['newAppointmentId'] = $newAppointmentId;

            // Check is it for next round or not
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

                                // $testDebug['dataToInsert'] = $dataToInsert;
                                // $testDebug['after'] = "After";
                                // $testDebug['UserAudition'] = $UserAudition;
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
}
