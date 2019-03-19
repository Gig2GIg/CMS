<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionsDatesRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Requests\AuditionRequest;
use App\Http\Resources\AuditionResponse;
use App\Models\Appointments;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\AuditionsDate;
use App\Models\Roles;
use App\Models\Slots;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditionsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['get']]);
        $this->log = new LogManger();
    }

    /**
     * @param AuditionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(AuditionRequest $request)
    {
        try {
            if ($request->isJson()) {
                $auditionData = $this->dataAuditionToProcess($request);
                foreach ($request['media'] as $file) {
                    $auditionFilesData[] = [
                        'url' => $file['url'],
                        'type' => $file['type'],
                    ];
                }
                $auditionFilesData[] = [
                    'url' => $request->cover,
                    'type' => 4,
                ];
                $auditRepo = new AuditionRepository(new Auditions());
                $audition = $auditRepo->create($auditionData);

                foreach ($auditionFilesData as $file) {
                    $audition->media()->create(['url' => $file['url'], 'type' => $file['type']]);
                }
                foreach ($request['dates'] as $date) {
                    $auditionDatesData = $this->dataDatesToProcess($date, $audition);
                    $datesRepo = new AuditionsDatesRepository(new AuditionsDate());
                    $datesRepo->create($auditionDatesData);
                }
                foreach ($request->roles as $roles) {
                    $roldata = $this->dataRolesToProcess($audition, $roles);
                    $rolesRepo = new RolesRepository(new Roles());
                    $rol = $rolesRepo->create($roldata);
                    $rol->image()->create(['type' => 4, 'url' => $roles['cover']]);
                }
                $dataAppoinment = $this->dataToAppointmentProcess($request, $audition);
                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appointment = $appointmentRepo->create($dataAppoinment);
                foreach ($request['appointment']['slots'] as $slot) {
                    $dataSlots = $this->dataToSlotsProcess($appointment, $slot);
                    $slotsRepo = new SlotsRepository(new Slots());
                    $slotsRepo->create($dataSlots);
                }
                foreach ($request['contributors'] as $contrib) {
                    $auditionContributorsData = $this->dataToContributorsProcess($contrib, $audition);
                    $contributorRepo = new AuditionContributorsRepository(new AuditionContributors());
                    $contributorRepo->create($auditionContributorsData);
                }
                return response()->json(['data' => ['message' => 'Auditions create']], 201);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Unprocessable '], 406);
        }
    }

    public function get(Request $request){
        try{
            $audition =  new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);

            if(isset($data->id)){
                $responseData = new AuditionResponse($data);
                return response()->json(['data' => $responseData], 200);
            }else{
                return response()->json(['error' => 'Not Found'], 404);
            }
        }catch (NotFoundException $exception){
            return response()->json(['error' => 'Not Found'], 404);

        }

    }


    /**
     * @param AuditionRequest $request
     * @return array
     */
    public function dataAuditionToProcess(AuditionRequest $request): array
    {
        $auditionData = [
            'title' => $request->title,
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'description' => $request->description,
            'url' => $request->url,
            'union' => $request->union,
            'contract' => $request->contract,
            'production' => $request->production,
            'status' => $request->status,
            'user_id' => Auth::user()->getAuthIdentifier(),

        ];
        return $auditionData;
    }

    /**
     * @param $date
     * @param $audition
     * @return array
     */
    public function dataDatesToProcess($date, $audition): array
    {
        $auditionDatesData = [
            'to' => $date['to'],
            'from' => $date['from'],
            'type' => $date['type'],
            'auditions_id' => $audition->id
        ];
        return $auditionDatesData;
    }

    /**
     * @param $audition
     * @param $roles
     * @return array
     */
    public function dataRolesToProcess($audition, $roles): array
    {
        $roldata = [
            'auditions_id' => $audition->id,
            'name' => $roles['name'],
            'description' => $roles['description'],
        ];
        return $roldata;
    }

    /**
     * @param AuditionRequest $request
     * @param $audition
     * @return array
     */
    public function dataToAppointmentProcess(AuditionRequest $request, $audition): array
    {
        $dataAppoinment = [
            'auditions_id' => $audition->id,
            'slots' => $request['appointment']['spaces'],
            'type' => $request['appointment']['type'],
            'length' => $request['appointment']['length'],
            'start' => $request['appointment']['start'],
            'end' => $request['appointment']['end'],
        ];
        return $dataAppoinment;
    }

    /**
     * @param Appointments $appointment
     * @param $slot
     * @return array
     */
    public function dataToSlotsProcess(Appointments $appointment, $slot): array
    {
        $dataSlots = [
            'appointment_id' => $appointment->id,
            'time' => $slot['time'],
            'status' => $slot['status'],
        ];
        return $dataSlots;
    }

    /**
     * @param $contrib
     * @param $audition
     * @return array
     */
    public function dataToContributorsProcess($contrib, $audition): array
    {
        $auditionContributorsData = [
            'user_id' => $contrib['user_id'],
            'auditions_id' => $audition->id,
            'status' => false
        ];
        return $auditionContributorsData;
    }
}
