<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Models\Appointments;
use Illuminate\Http\Request;

class AppoinmentController extends Controller
{
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
}
