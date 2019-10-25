<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ManageDates;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Models\Appointments;
use App\Models\Feedbacks;
use App\Models\Slots;
use App\Models\UserAuditions;
use Illuminate\Database\Eloquent\Collection;
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
        if(!$request->online){
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
                if($repoFeeadback->count() >= 0){
                    $idsFeedback = $repoFeeadback->pluck('user_id');
                    $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                    $dataUserAuditions = $repoUserAuditions->all()->whereNotIn('user_id', $idsFeedback)->where('appointment_id',$request->appointment_id);
                    if ($dataUserAuditions->count() > 0) {
                        $dataUserAuditions->each(function ($element) {
                            $element->update(['type' => 3]);
                        });
                    }
                }
                return response()->json(['message' => 'Round close', 'data' => $data], 200);
            }
        }catch(\Exception $exception){
                $this->log->error($exception->getMessage());
                return response()->json(['message' => 'Round not close ', 'data' => []], 406);
            }
    }

    public function dataToSlotsProcess($appointment, $slot): array
    {
        return [
            'appointment_id' => $appointment->id,
            'time' => $slot['time'],
            'number' => $slot['number'] ?? null,
            'status' => $slot['status'],
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
            return response()->json(['message' => 'list by slots', 'data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => 'Not found data', 'data' => []], 404);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws NotFoundException
     */
    public function notOnlineSubmision(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->log->info("CREATE ROUND::". $request);
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
            $this->log->info("CREATE ROUUND NEW ROUND DATA");
             $this->log->info($data);
            $repoFeeadback = Feedbacks::all()
                ->where('appointment_id', $lastid->id)
                ->where('favorite', true);

            if ($repoFeeadback->count() > 0) {
                $repoFeeadback->each(function ($item) use ($data) {
                    $item->update(['appointment_id' => $data->id]);
                });
            }


            foreach ($request['slots'] as $slot) {
                $dataSlots = $this->dataToSlotsProcess($data, $slot);
                $slotsRepo = new SlotsRepository(new Slots());
                $slotsRepo->create($dataSlots);
            }
            return response()->json(['message' => 'Round Create', 'data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => 'Round not create ', 'data' => []], 406);
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
            $repoFeeadback = Feedbacks::all()
                ->where('appointment_id', $lastid->id)
                ->where('favorite', true);

            if ($repoFeeadback->count() > 0) {
                $repoFeeadback->each(function ($item) use ($data) {
                    $item->update(['appointment_id' => $data->id]);
                });
            }

            return response()->json(['message' => 'Round Create', 'data' => $data], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => 'Round not create ', 'data' => []], 406);
        }
    }
}
