<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionContributorsRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\AuditionsDatesRepository;
use App\Http\Repositories\RolesRepository;
use App\Http\Repositories\SlotsRepository;
use App\Http\Requests\AuditionEditRequest;
use App\Http\Requests\AuditionRequest;
use App\Http\Requests\MediaRequest;
use App\Http\Resources\AuditionFullResponse;
use App\Http\Resources\AuditionResponse;
use App\Models\Appointments;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\Roles;
use App\Models\Slots;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditionsController extends Controller
{
    public const DESCRIPTION = 'description';
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    /**
     * @param AuditionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AuditionRequest $request)
    {
        try {
            DB::beginTransaction();
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
                    $audition->dates()->create($this->dataDatesToProcess($date));
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
                DB::commit();
                $responseData = ['data' => ['message' => 'Auditions create']];
                $code = 201;
            } else {
                $responseData = ['error' => 'Unauthorized'];
                $code = 404;
            }
            return response()->json($responseData, $code);
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->log->error($exception->getMessage());
            return response()->json(['error' => 'Unprocessable '], 406);
        }
    }


    /**
     * @param $request
     * @return array
     */
    public function dataAuditionToProcess($request): array
    {
        return [
            'title' => $request->title,
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            self::DESCRIPTION => $request->description,
            'url' => $request->url,
            'union' => $request->union,
            'contract' => $request->contract,
            'production' => $request->production,
            'status' => $request->status,
            'user_id' => Auth::user()->getAuthIdentifier(),

        ];

    }

    /**
     * @param $date
     * @param $audition
     * @return array
     */
    public function dataDatesToProcess($date): array
    {
        return [
            'to' => $date['to'],
            'from' => $date['from'],
            'type' => $date['type'],

        ];

    }

    /**
     * @param $audition
     * @param $roles
     * @return array
     */
    public function dataRolesToProcess($audition, $roles): array
    {
        return [
            'auditions_id' => $audition->id,
            'name' => $roles['name'],
            self::DESCRIPTION => $roles[self::DESCRIPTION],
        ];

    }

    /**
     * @param AuditionRequest $request
     * @param $audition
     * @return array
     */
    public function dataToAppointmentProcess($request, $audition): array
    {
        return [
            'auditions_id' => $audition->id,
            'slots' => $request['appointment']['spaces'],
            'type' => $request['appointment']['type'],
            'length' => $request['appointment']['length'],
            'start' => $request['appointment']['start'],
            'end' => $request['appointment']['end'],
        ];

    }

    /**
     * @param Appointments $appointment
     * @param $slot
     * @return array
     */
    public function dataToSlotsProcess($appointment, $slot): array
    {
        return [
            'appointment_id' => $appointment->id,
            'time' => $slot['time'],
            'number' => $slot['number'] ?? null,
            'status' => $slot['status'],
        ];

    }

    /**
     * @param $contrib
     * @param $audition
     * @return array
     */
    public function dataToContributorsProcess($contrib, $audition): array
    {
        return [
            'user_id' => $contrib['user_id'],
            'auditions_id' => $audition->id,
            'status' => false
        ];

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getall()
    {
        $data = new AuditionRepository(new Auditions());
        $count = count($data->all());
        if ($count !== 0) {
            $responseData = AuditionResponse::collection($data->all());
            $dataResponse = ['data' => $responseData];
            $code = 200;

        } else {
            $dataResponse = ['data' => "Not found Data"];
            $code = 404;
        }
        return response()->json($dataResponse, $code);
    }

    public function getFullData(Request $request)
    {
        try {
            $data = new AuditionRepository(new Auditions());
            $count = count($data->all());
            if ($count !== 0) {
                $responseData = AuditionFullResponse::collection($data->all());
                $dataResponse = ['data' => $responseData];
                $code = 200;

            } else {
                $dataResponse = ['data' => "Not found Data"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);

        } catch (NotFoundException $exception) {
            return response()->json(['error' => 'Not Found'], 404);

        }

    }

    public function get(Request $request)
    {
        try {
            $audition = new AuditionRepository(new Auditions());
            $data = $audition->find($request->id);

            if (isset($data->id)) {
                $responseData = new AuditionFullResponse($data);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);

        } catch (NotFoundException $exception) {
            return response()->json(['error' => 'Not Found'], 404);

        }

    }

    public function update(AuditionEditRequest $request)
    {
        try {
            foreach ($request['media'] as $file) {
                $auditionFilesData[] = [
                    'url' => $file['url'],
                    'type' => $file['type'],
                ];
            }

            $auditionRepo = new AuditionRepository(new Auditions());
            $audition = $auditionRepo->find($request->id);

            if (isset($audition->id)) {
                DB::beginTransaction();
                $updateRepo = new AuditionRepository($audition);
                $auditionData = $this->dataAuditionToProcess($request);
                $updateRepo->update($auditionData);
                $audition->media->update(['url' => $request->url]);
                foreach ($auditionFilesData as $file) {
                    $audition->media()->update(['url' => $file['url'], 'type' => $file['type']]);
                }
                foreach ($request['dates'] as $date) {
                    $audition->dates()->update($this->dataDatesToProcess($date));
                }
                foreach ($request->roles as $roles) {
                    $roldata = $this->dataRolesToProcess($audition, $roles);
                    $rolesRepo = new RolesRepository(new Roles());
                    $rol = $rolesRepo->find($roles['id']);
                    $rol->image()->update(['url' => $roles['image']['url']]);
                    $rol->update($roldata);
                }

                foreach ($request->appointment[0]['slots'] as $slot) {

                    $dataSlots = [
                        'time' => $slot['time'],
                        'number' => $slot['number'] ?? null,
                        'status' => $slot['status'],
                    ];
                    $slotsRepo = new SlotsRepository(new Slots());
                    $slotsRepo->find($slot['id'])->update($dataSlots);
                }
                DB::commit();

                $dataResponse = ['data' => 'Data Updated'];
                $code = 200;


            } else {
                $dataResponse = ['data' => 'Data Not Found'];
                $code = 404;
            }

            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            return response()->json(['data' => 'Data Not Found'], 404);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            DB::rollBack();
            return response()->json(['data' => 'Data Not Update'], 406);
        }
    }

    public function findby(Request $request)
    {
        if (isset($request->base)) {
            return $this->findByTitleAndMulti($request);
        } else {
            return $this->findByMulty($request);
        }
    }

    public function findByTitleAndMulti(Request $request)
    {

        $data = new Auditions();
        $elementResponse = new Collection();

        if (isset($request->base)) {
            $elementResponse = $data->where('title', 'like', "%{$request->base}%");
        }


        if (isset($request->union)) {
            $elementResponse->where('union', '=', $request->union);
        }

        if (isset($request->contract)) {
            $elementResponse->where('contract', '=', $request->contract);

        }

        if (isset($request->production)) {

            $elementResponse->where('production', 'like', "%{$request->production}%");

        }


        $data2 = $elementResponse->get();

        if (count($data2) === 0) {
            $dataResponse = ['error' => 'Not Found'];
            $code = 404;
        } else {
            $dataResponse = ['data' => $data2];
            $code = 200;
        }


        return response()->json($dataResponse, $code);

    }

    public function findByMulty(Request $request)
    {
        $elementResponse = new Collection();


        if (isset($request->production)) {

            $split_elements = explode(',', $request->production);
            foreach ($split_elements as $item) {
                $query = DB::table('auditions')
                    ->whereRaw('FIND_IN_SET(?,production)', [$item])
                    ->get();
                foreach ($query as $items) {
                    $elementResponse->push($items);
                }

            }

        }
        if (isset($request->union)) {
            $elementResponse = $elementResponse->where('union', '=', $request->union);
        }

        if (isset($request->contract)) {
            $elementResponse = $elementResponse->where('contract', '=', $request->contract);
        }


        if (count($elementResponse) === 0) {
            $dataResponse = ['error' => 'Not Found'];
            $code = 404;
        } else {
            $dataResponse = ['data' => $elementResponse];
            $code = 200;
        }


        return response()->json($dataResponse, $code);

    }

    public function media(MediaRequest $request, Auditions $auditions)
    {
        $repository = new AuditionRepository($auditions);
        $data = $repository->findMediaByParams($request->type);

        return response()->json(['data' => $data]);

    }
}
