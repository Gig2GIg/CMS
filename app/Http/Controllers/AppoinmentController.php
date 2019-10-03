<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ManageDates;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Models\Appointments;
use App\Models\Slots;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AppoinmentController extends Controller
{
    /**
     * @var ManageDates
     */
    protected $toDate;



    public function getRounds(Request $request){
      try{
          $repo = new AppointmentRepository(new Appointments());
          $data = $repo->findbyparam('auditions_id',$request->audition_id)->get();
          if($data->count() == 0){
              throw new NotFoundException('Not Found Data');
          }
          return response()->json(['data'=>$data->toArray()],200);
      }catch (\Exception $exception){
          $this->log->error($exception->getMessage());
          return response()->json(['data'=>[]],404);
      }
  }

  public function createRound(Request $request){
        $this->toDate = new ManageDates();
      try{
        $repo = new AppointmentRepository(new Appointments());
        $appointment = [
            'date' => $this->toDate->transformDate($request->date),
            'time' => $request->time,
            'location' => json_encode($request->location),
            'slots'=>$request->number_slots,
            'type'=>$request->type,
            'length'=>$request->length,
            'start'=>$request->start,
            'end'=>$request->end,
            'round'=>$request->round,
            'status'=>true,
            'auditions_id'=>$request->audition_id,
        ];
        if(is_null($request->slots)){
            throw new \Exception('Not Slots to process');
        }
        $data = $repo->create($appointment);

          foreach ($request['slots'] as $slot) {
              $dataSlots = $this->dataToSlotsProcess($data, $slot);
              $slotsRepo = new SlotsRepository(new Slots());
              $slotsRepo->create($dataSlots);
          }
        return response()->json(['message'=>'Round Create','data'=>$data],200);
      }catch (\Exception $exception){
          $this->log->error($exception->getMessage());
          return response()->json(['message'=>'Round not create ','data'=>[]],406);
      }
  }

    public function updateRound(Request $request){
        try{
            $repo = new AppointmentRepository(new Appointments());
            $data = $repo->find($request->appointment_id);
            $update = $data->update(['status'=>$request->status]);
            return response()->json(['message'=>'Round Create','data'=>$data],200);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['message'=>'Round not create ','data'=>[]],406);
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

    public function getSlots(Request $request){
        try{
            $repo = new AppointmentRepository(new Appointments());
            $repoData = $repo->find($request->appointment_id);
            $data= $repoData->slot->sortBy('time');
            if($data->count()===0){
                throw new NotFoundException('Not found data');
            }
           return response()->json(['message'=>'list by slots','data'=>$data],200);
        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            return response()->json(['message'=>'Not found data','data'=>[]],404);
        }
    }
}