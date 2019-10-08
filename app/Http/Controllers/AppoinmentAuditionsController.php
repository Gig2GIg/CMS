<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\ManageDates;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Repositories\UserSlotsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserAuditionsRepository;


use App\Http\Resources\AppointmentDetailsUserResource;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\AppointmentSlotsResource;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserSlots;
use App\Models\UserAuditions;

use Illuminate\Http\Request;


use App\Http\Exceptions\NotificationException;

use App\Http\Controllers\Utils\Notifications as SendNotifications;


class AppoinmentAuditionsController extends Controller
{
    protected $log;


    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();

    }

    public function preStore(Request $request)
    {
        $this->log->info($request);
        try {
            $dataUserRepo = new UserRepository(new User());
            if (isset($request->email)) {
                $dataUser = $dataUserRepo->findbyparam('email', $request->email);
            } else {
                $dataUser = $dataUserRepo->find($request->user);
            }


            $userAuditionRepo = new UserAuditionsRepository(new UserAuditions());
            $userAuditions = $userAuditionRepo->getByParam('rol_id', $request->role_id);

            $userAudition = $userAuditions->where('user_id', $request->user)->first();

            $userSlots = UserSlots::where('user_id',$request->user );
            $userSlot =   $userSlots->where('roles_id', $request->role_id)->first();

            if (! is_null($userAudition)){
               if ($userAudition->slot_id){
                $slotRepo =  new SlotsRepository(new Slots());
                $slot = $slotRepo->find($userAudition->slot_id);

                $dataResponse = [
                    'id' => $dataUser->id,
                    'image' => $dataUser->image->url,
                    'name' => $dataUser->details->first_name . " " . $dataUser->details->last_name,
                    'hour' => $slot->time,
                    'slot_id' => $slot->id
                ];

                return response()->json(['data' => $dataResponse], 200);
               }

            }
            if  (! is_null($userSlot->slots_id)) {

                $slotRepo =  new SlotsRepository(new Slots());
                $slot = $slotRepo->find($userSlot->slots_id);
                $dataResponse = [
                    'id' => $dataUser->id,
                    'image' => $dataUser->image->url,
                    'name' => $dataUser->details->first_name . " " . $dataUser->details->last_name,
                    'hour' => $slot->time,
                    'slot_id' => $slot->id
                ];

                return response()->json(['data' => $dataResponse], 200);
            }

            $dataResponse = [
                'id' => $dataUser->id,
                'image' => $dataUser->image->url,
                'name' => $dataUser->details->first_name . " " . $dataUser->details->last_name
            ];

            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {

            $iduser = null;
            $data = null;
            if (isset($request->email)) {
                $dataUserRepo = new UserRepository(new User());
                $dataUser = $dataUserRepo->findbyparam('email', $request->email);
                $iduser = $dataUser->id;
            } else {
                $iduser = $request->user;

            }
            $dataSlotUser = new UserSlots();
            $dataCompareExistsRegister = $dataSlotUser->where('user_id', $iduser)
            ->where('roles_id', '=', $request->rol)
            ->where('slots_id', '=', $request->slot)
            ->where('status', '=', 'checked');
            if ($dataCompareExistsRegister->count() > 0) {
                throw new \Exception('You already registered');
            }

            $dataSlotReserved = new UserSlots();
            $dataCompareExists = $dataSlotReserved
                ->where('user_id', '=', $iduser)
                ->where('roles_id', '=', $request->rol)
                ->where('appointment_id', '=', $request->appointment_id)->first();
            $elementcount = $dataCompareExists ??  collect([]);
            if ($elementcount->count() == 0) {
                $dataSave = new UserSlots();
               $data= $dataSave->create([
                    'user_id' => $iduser,
                    'appointment_id' => $request->appointment_id,
                    'slots_id' => $request->slot,
                    'roles_id' => $request->rol,
                    'status' => 2
                ]);
            } else {
                $this->log->info("COMPARE::".$dataCompareExists);
                $this->log->info("COMPARE::".$dataCompareExists->id);
               UserSlots::find($dataCompareExists->id)->update([
                    'user_id' => $iduser,
                    'auditions_id' => $request->auditions,
                    'slots_id' => $request->slot,
                    'roles_id' => $request->rol,
                    'status' => 2
                ]);
                $data = UserSlots::where('id','=',$dataCompareExists->id)->first();

            }

            $slot = new SlotsRepository(new Slots());
            $slot->find($request->slot)->update([
                'status' => '1'
            ]);
            $userRepo = new UserRepository(new User());
            $user = $userRepo->find($iduser);

            $appointmentRepo = new AppointmentRepository(new Appointments());
            $appoinmentData = $appointmentRepo->find($request->appointment_id);

            try {

                $auditionsRepo = new AuditionRepository(new Auditions());
                $audition = $auditionsRepo->find($appoinmentData->auditions_id);

                $this->sendStoreNotificationToUser($user, $audition);
                $this->saveStoreNotificationToUser($user, $audition);       


            } catch (NotificationException $exception) {
                $this->log->error( $exception->getMessage());
            }

            $dataResponse = new AppointmentResource($data);
            $code = 200;

            return response()->json(['data' => $dataResponse], $code);
        } catch (\Exception $exception) {
            $this->log->error("Line:".$exception->getLine()." ".$exception->getMessage()." ".$exception->getFile());
            return response()->json(['data' => 'Appointment not assigned'], 406);
        }
    }

    public function saveStoreNotificationToUser($user, $audition): void
        {
                try {
                    if ($user instanceof User){
                            $history = $user->notification_history()->create([
                            'title' => $audition->title,
                            'code' => 'check_in',
                            'status' => 'unread',
                            'message'=> 'You have been registered for the audition '. $audition->title
                        ]);
                        $this->log->info('saveStoreNotificationToUser:: ', $history);
                    }

                }catch (NotFoundException $exception) {
                    $this->log->error($exception->getMessage());
                }
            }

    public function sendStoreNotificationToUser($user, $audition): void
    {
        try {
                $this->pushNotifications('You have been registered for the audition '. $audition->title, $user);
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function show(Request $request)
    {
        try {
            $dataRepo = new UserSlotsRepository(new UserSlots());
            $data = $dataRepo->findbyparam('appointment_id', $request->audition);
            $res = $data->where('status','=','checked');
            $dataResponse = AppointmentResource::collection($res);
            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function showCms(Request $request)
    {
        try {
            $dataRepo = new UserSlotsRepository(new UserSlots());
            $data = $dataRepo->findbyparam('auditions_id', $request->audition);


            $dataResponse = AppointmentDetailsUserResource::collection($data);
            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    public function deleteUserSlot($id)
    {
        UserSlots::find($id)->delete();

        return response()->json([
            'status' => 'Success',
        ]);
    }

    public function showListNotWalk(Request $request)
    {
        try {
            $dataRepo = new AppointmentRepository(new Appointments());
            $this->log->info($request->id);
            $data = $dataRepo->find($request->id);
            $dataResponse = new AppointmentSlotsResource($data, 1);
            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

    public function showListWalk(Request $request)
    {
        try {
            $dataRepo = new AppointmentRepository(new Appointments());
            $this->log->info($request->id);
            $data = $dataRepo->find($request->id);
            $dataResponse = new AppointmentSlotsResource($data, 2);
            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Data Not Found'], 404);
        }
    }

}
