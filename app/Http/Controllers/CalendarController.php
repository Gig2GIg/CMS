<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CalendarRepository;
use App\Models\Calendar;
use App\Http\Resources\CalendarResource;
use App\Http\Requests\CalendarRequest;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CalendarRequest $request)
    {
        if($request->json()){

            $calendarRepo = new CalendarRepository(new Calendar());

            // Start
            $start_count = count($calendarRepo->betweenFrom($request->start_date,$request->end_date));
            if ($start_count > 0) {
                return response()->json(['error' => "Start date is already occupied"], 422);
            }

            $data = [
                'production_type' => $request->production_type,
                'project_name' => $request->project_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'user_id' => Auth::user()->id,
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
