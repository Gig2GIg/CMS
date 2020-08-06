<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CalendarRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Calendar;
use App\Http\Resources\CalendarResource;
use App\Http\Requests\CalendarRequest;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['getAll']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user_repo = new UserRepository(Auth::user());

        $count = count($user_repo->calendars());

        if ($count !== 0) {
            $data = $user_repo->calendars();
            $responseData = CalendarResource::collection($data);

            $dataResponse = ['data' => $responseData];
            $code = 200;
        } else {
            $dataResponse = ['data' => "Not found Data"];
            $code = 404;
        }
        return response()->json($dataResponse, $code);
    }

    /**
     * Display a listing of events by user id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $calendar_repo = new CalendarRepository(new Calendar());
        $user_id = $request->id;
        $count = count($calendar_repo->findbyuser($user_id));

        if ($count !== 0) {
            $data = $calendar_repo->findbyuser($user_id);
            $responseData = CalendarResource::collection($data);

            $dataResponse = ['data' => $responseData];
            $code = 200;
        } else {
            $dataResponse = ['data' => "Not found Data"];
            $code = 404;
        }
        return response()->json($dataResponse, $code);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CalendarRequest $request)
    {
        try {

            $calendarRepo = new CalendarRepository(new Calendar());

            // obtain year
            $now = Carbon::now();
            $year = $now->year;
            $dt = $now->toDateString();

            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $user_id = Auth::user()->id;


            if ($start_date < $dt || $end_date < $dt) {
                return response()->json(['error' => trans('messages.cant_use_past_dates')], 422);
                // return response()->json(['error' => "Can't use past dates"], 422);
                // End date must be greater than start date
            } else if ($end_date < $start_date) {
                // return response()->json(['error' => "End date must be greater than start date"], 422);
                return response()->json(['error' => trans('messages.end_date_must_be_greater_than_start_date')], 422);
            }

            // Verify if the range of dates is available
            $count = $calendarRepo->betweenDates($start_date, $end_date, $user_id);
            if ($count > 0) {
                // return response()->json(['error' => "Date range is occupied"], 422);
                return response()->json(['error' => trans('messages.date_range_is_occupied')], 422);
            }

            $data = [
                'production_type' => $request->production_type,
                'project_name' => $request->project_name,
                'event_type' => $request->event_type ?? null,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'user_id' => $user_id,
            ];

            $calendar = $calendarRepo->create($data);

            $responseData = ['data' => new CalendarResource($calendar)];
            $code = 201;


            return response()->json($responseData, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception);
            return response()->json(['data' => trans('messages.error_process_event')], 422);
            // return response()->json(['data' => 'Error process event'], 422);
        }
    }

    /**
     * Display the specified resource.
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function show($id)
    {
        try {
            $calendar = new CalendarRepository(new Calendar());
            $data = $calendar->find(request('id'));

            if (isset($data->id)) {
                $responseData = new CalendarResource($data);
                $dataResponse = ['data' => $responseData];
                $code = 200;
            } else {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $exception) {
            // return response()->json(['error' => 'Not Found'], 404);
            return response()->json(['error' => trans('messages.data_not_found')], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CalendarRequest $request)
    {
        try {
            if ($request->json()) {

                $event_id = request('id');

                $calendarRepo = new CalendarRepository(new Calendar());
                $calendar = $calendarRepo->find($event_id);

                // obtain year
                $now = Carbon::now();
                $year = $now->year;
                $dt = $now->toDateString();

                $start_date = $request->start_date;
                $end_date = $request->end_date;
                $user_id = Auth::user()->id;

                if ($calendar->start_date != $start_date || $calendar->end_date != $end_date) {
                    if ($start_date < $dt || $end_date < $dt) {
                        // return response()->json(['error' => "Can't use past dates"], 422);
                        return response()->json(['error' => trans('messages.cant_use_past_dates')], 422);
                        
                    }
                }

                // End date must be greater than start date
                if ($end_date < $start_date) {
                    return response()->json(['error' => trans('messages.end_date_must_be_greater_than_start_date')], 422);
                    // return response()->json(['error' => "End date must be greater than start date"], 422);
                }

                if ($calendar->start_date != $start_date || $calendar->end_date != $end_date) {
                    $count = $calendarRepo->betweenDates($start_date, $end_date, $user_id, $event_id);
                    if ($count > 0) {
                        return response()->json(['error' => trans('messages.date_range_is_occupied')], 422);
                        // return response()->json(['error' => "Date range is occupied"], 422);
                    }
                }

                $data = [
                    'production_type' => $request->production_type,
                    'project_name' => $request->project_name,
                    'event_type' => $request->event_typ ?? null,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ];

                // Update data
                $calendar->update($data);

                $responseData = ['data' => 'Data Updated'];
                $code = 200;
            } else {
                $responseData = ['error' => 'Unauthorized'];
                $code = 401;
            }

            return response()->json($responseData, $code);
        } catch (NotFoundException $e) {
            // return response()->json(['data' => "Data Not Found"], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $calendar = new CalendarRepository(new Calendar());
            $datac = $calendar->find($request->id);
            $datac->delete();

            // return response()->json(['data' => 'Event deleted'], 200);
            return response()->json(['data' => trans('messages.event_deleted')], 200);
        } catch (NotFoundException $e) {
            // return response()->json(['data' => "Not found Data"], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => trans('messages.not_processable')], 406);
            // return response()->json(['data' => "Unprocesable"], 406);
        }
    }
}
