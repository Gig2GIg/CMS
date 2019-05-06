<?php

namespace App\Http\Controllers\Cms;

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
           return response()->json(['data' => "Not found Data"], 404);
       }
    }

    public function update(Request $request)
    {
        $contentRepo = new ContentSettingRepository(new ContentSetting);
        $data = [
            'term_of_use' => $request->term_of_use,
            'privacy_policy' => $request->privacy_policy,
            'app_info' => $request->app_info,
            // 'contact_us' => $request->contact_us,
            'help' => $request->help

        ];
        $conten_setting = $contentRepo->first();

        $result = $conten_setting->update($data);

        if ($result){
            return response()->json([''], 204);
        }else{
            return response()->json(['data' => "Not update Data"], 422);
        }

    }
}
