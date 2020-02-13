<?php

namespace App\Http\Resources;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Models\Appointments;
use App\Models\Feedbacks;
use App\Models\UserDetails;
use App\Models\UserAuditions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\PHP;

class AuditionAnalyticsResponse extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $appointments = $this->appointments;
        $csvArray = new Collection();
        $i = new Collection();
        $i->count = 0;
        $appointments->each(function ($item)  use ($csvArray, $i) {

            $userAuditionRepo = new UserAuditionsRepository(new UserAuditions());
            $userAuditions = $userAuditionRepo->all()->where('appointment_id', $item->id);

            $gender = new Collection(["male" => 0, "female" => 0, "other" => 0]);

            $userAuditions->each(function ($uD) use ($gender) {
                $userDetails = new UserDetailsRepository(new UserDetails());
                $dataUserDetails = $userDetails->findbyparam('user_id', $uD->user_id)->first();

                if($dataUserDetails->ge && $dataUserDetails->gender == "male") {
                    $gender["male"] += 1;
                } else if($dataUserDetails && $dataUserDetails->gender == "female") {
                    $gender["female"] += 1;
                } else {
                    $gender["other"] += 1;
                }
            });

            $feedbacksRepo = new FeedbackRepository(new Feedbacks());
            $repoDatafeedbacks = $feedbacksRepo->findbyparams(['appointment_id' => $item->id, 'favorite' => 1]);

            $i->count += 1;
            $csvArray->push([(string)$i->count, (string)count($userAuditions), implode(":", $gender->toArray()), (string)$repoDatafeedbacks->count()]);

        });
        return $csvArray;
    }
}
