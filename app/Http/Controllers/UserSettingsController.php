<?php

namespace App\Http\Controllers;

use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\UserSettingsRepository;
use App\Models\UserSettings;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
 public function list(Request $request){
     try{
        $repo = new UserSettingsRepository(new UserSettings());
        $data = $repo->findbyparam('user_id',$this->getUserLogging())->get();
        if($data->count() == 0){
            throw new NotFoundException();
        }

        return response()->json(['data'=>$data],200);
     }catch (\Exception $exception){
         $this->log->error($exception->getMessage());
        //  return response()->json(['data'=>'Not Found Data'],404);
         return response()->json(['data' => trans('messages.data_not_found')], 404);

     }
 }

    public function update(Request $request){
        try{
            $repo = new UserSettingsRepository(new UserSettings());
            $data = $repo->find($request->id);
            $update = $data->update([
                'value'=>$request->value
            ]);
            if(!$update){
                throw new \Exception('Setting not updated');
            }



            // return response()->json(['data'=>'Setting updated'],200);
            return response()->json(['data' => trans('messages.setting_updated')], 200);

        }catch (\Exception $exception){
            $this->log->error($exception->getMessage());
            // return response()->json(['data'=>'Setting not updated'],406);
            return response()->json(['data' => trans('messages.setting_not_updated')], 406);
        }
    }
}
