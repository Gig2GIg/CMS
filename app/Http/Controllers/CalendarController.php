<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CalendarRepository;
use App\Models\Calendar;
use App\Http\Resources\CalendarResource;
use App\Http\Requests\CalendarRequest;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $data = new CalendarRepository(new Calendar());
        $count = count($data->all());
        if ($count !== 0) {
            $responsei = $data->orderBy('start_date','DESC');
            $responseData = CalendarResource::collection($responsei);
            
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
        if($request->json()){

            $calendarRepo = new CalendarRepository(new Calendar());

            // obtain year
            $now = Carbon::now();
            $year = $now->year;
            $dt = $now->toDateString();

            $start_date = $year . "-" . $request->start_date;
            $end_date = $year . "-" . $request->end_date;
            $user_id = Auth::user()->id;

            if($start_date < $dt ||  $end_date < $dt){
                return response()->json(['error' => "Can't use past dates"], 422);
              // End date must be greater than start date
            } else if($end_date < $start_date){
                return response()->json(['error' => "End date must be greater than start date"], 422);
            }

            // Verify if the range of dates is available
            $count = $calendarRepo->betweenDates($start_date,$end_date,$user_id);
            if ($count > 0) {
                return response()->json(['error' => "Start date is already occupied"], 422);
            }

            $data = [
                'production_type' => $request->production_type,
                'project_name' => $request->project_name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'user_id' => $user_id,
            ];
    
            $calendar = $calendarRepo->create($data);

            $responseData = ['data' => new CalendarResource($calendar)];
            $code = 201;
        }else{
            $responseData = ['error' => 'Unauthorized'];
            $code = 401;
        }

        return response()->json($responseData, $code);
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
            return response()->json(['error' => 'Not Found'], 404);

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
