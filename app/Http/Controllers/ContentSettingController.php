<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\ContentSettingRepository;
use App\Models\ContentSetting;
use App\Http\Resources\ContentSettingResource;
use App\Http\Exceptions\NotFoundException;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;


class ContentSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt');
    }

    public function getAllContentSetting(ContentSetting $request)
    {
        $data = new ContentSettingRepository($request);

        $count = count($data->all());
        if ($count > 0) {
            $responseData = ContentSettingResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            // return response()->json(['data' => "Not found Data"], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }
}
