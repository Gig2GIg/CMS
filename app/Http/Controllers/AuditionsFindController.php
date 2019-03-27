<?php
/**
 * Created by PhpStorm.
 * User: alphyon
 * Date: 2019-03-27
 * Time: 11:17
 */

namespace App\Http\Controllers;


use App\Http\Resources\AuditionResourceFind;
use App\Http\Resources\AuditionResponse;
use App\Models\Auditions;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class AuditionsFindController
{
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
        $response = AuditionResourceFind::collection($data2);

        if (count($data2) === 0) {
            $dataResponse = ['error' => 'Not Found'];
            $code = 404;
        } else {
            $dataResponse = ['data' => $response];
            $code = 200;
        }


        return response()->json($dataResponse, $code);

    }

    public function findByProductionAndMulty(Request $request)
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
$response = AuditionResourceFind::collection($elementResponse);

        if (count($elementResponse) === 0) {
            $dataResponse = ['error' => 'Not Found'];
            $code = 404;
        } else {
            $dataResponse = ['data' => $response];
            $code = 200;
        }


        return response()->json($dataResponse, $code);

    }




}
