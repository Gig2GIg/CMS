<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Models\Notifications\NotificationSetting;
use Illuminate\Support\Facades\Log;

class NoficationSettingUserResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
           

        ];
    }
}
