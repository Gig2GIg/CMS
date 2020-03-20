<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-27
 * Time: 11:17
 */

namespace App\Http\Controllers;

use App\Http\Resources\AuditionResourceFind;
use App\Models\Auditions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuditionsFindController extends Controller
{
    public function findByTitleAndMulti(Request $request)
    {
        try {

            $data = new Auditions();
            $elementResponse = new Collection();

            if (isset($request->base)) {
                $elementResponse = $data->where('title', 'like', "%{$request->base}%");
            }

            if (isset($request->union) && strtoupper($request->union) != "ANY") {
                $elementResponse->where('union', strtoupper($request->union));
            }

            if (isset($request->contract) && strtoupper($request->contract) != "ANY") {
                $elementResponse->where('contract', strtoupper($request->contract));
            }

            if (isset($request->lat) && isset($request->lng)) {
                $elementResponse->whereHas('appointments', function($q) use($request){
                    $q->whereRaw(
                        "(DEGREES(
                            ACOS(
                             SIN(RADIANS(" . $request->lat . "))
                                * SIN(RADIANS(appointments.lat))
                                + COS(RADIANS(" . $request->lat . "))
                                * COS(RADIANS(appointments.lat))
                                * COS(RADIANS(" . $request->lng . " - appointments.lng))
                                )
                            ) * 60 * 1.1515) <= ?",
                        env("FINDBY_LOCATION_DISTANCE")
                    );
                    $q->where('appointments.status', 1);
                });
            }

            // if (isset($request->union)) {
            //     $elementResponse->where('union', '=', strtoupper($request->union));
            // }

            // if (isset($request->contract)) {
            //     $elementResponse->where('contract', '=', strtoupper($request->contract));
            // }

            if (isset($request->production)) {

                $elementResponse->where('production', 'like', "%{$request->production}%");

            }

            $data2 = $elementResponse->where('status' , "!=", 2)->get()->sortByDesc('created_at');
            $response = AuditionResourceFind::collection($data2);

            if (count($data2) === 0) {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            } else {
                $dataResponse = ['data' => $response];
                $code = 200;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['error' => 'Not Found'], 404);
            return response()->json(['error' => trans('messages.data_not_found')], 404);
        }

    }

    public function findByProductionAndMulty(Request $request)
    {
        try {
            $elementResponse = new Collection();

            if ((isset($request->production) && $request->production != 'ANY') || (isset($request->lat) && isset($request->lng))) {
                if (isset($request->lat) && isset($request->lng)) {

                    $elementResponse = Auditions::whereHas('appointments', function($q) use($request) {
                                $q->whereRaw(
                                    "(DEGREES(
                                        ACOS(
                                         SIN(RADIANS(" . $request->lat . "))
                                            * SIN(RADIANS(appointments.lat))
                                            + COS(RADIANS(" . $request->lat . "))
                                            * COS(RADIANS(appointments.lat))
                                            * COS(RADIANS(" . $request->lng . " - appointments.lng))
                                            )
                                        ) * 60 * 1.1515) <= ?",
                                    env("FINDBY_LOCATION_DISTANCE")
                                );
                                $q->where('appointments.status', 1);
                            })->get();      
                } 

                if(isset($request->production) && $request->production != 'ANY'){
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

            } else {
                $elementResponse = Auditions::all();
            }

            if (isset($request->union) && strtoupper($request->union) != "ANY") {
                $elementResponse = $elementResponse->where('union', strtoupper($request->union));
            }

            if (isset($request->contract) && strtoupper($request->contract) != "ANY") {
                $elementResponse = $elementResponse->where('contract', strtoupper($request->contract));
            }

            $response = AuditionResourceFind::collection($elementResponse->where('status' , "!=", 2)->sortByDesc('created_at'));

            if (count($elementResponse) === 0) {
                $dataResponse = ['error' => 'Not Found'];
                $code = 404;
            } else {
                $dataResponse = ['data' => $response];
                $code = 200;
            }

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['error' => 'Not Found'], 404);
            return response()->json(['error' => trans('messages.data_not_found')], 404);

        }

    }

}
