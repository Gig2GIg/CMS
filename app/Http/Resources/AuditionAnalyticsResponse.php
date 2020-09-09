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

            $gender = new Collection(["male" => 0, "female" => 0, "agender" => 0, "gender diverse" => 0, "gender expansive" => 0, "gender fluid" => 0, "genderqueer" => 0, "intersex" => 0, "non-binary" => 0, "transfemale/transfeminine" => 0, "transmale/transmasculine" => 0, "two-spirit" => 0, 'Prefer not to answer' => 0, 'self describe' => 0]);
            
            $userAuditions->each(function ($uD) use (&$gender) {
                $dataUserDetails = new Collection();
                $dataUserDetails = UserDetails::where('user_id', $uD->user_id)->first();
                
                if($dataUserDetails && $dataUserDetails->gender == "male") {
                    $gender["male"] += 1;
                } else if($dataUserDetails && $dataUserDetails->gender == "female") {
                    $gender["female"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "agender") {
                    $gender["agender"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "gender diverse") {
                    $gender["gender diverse"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "gender expansive") {
                    $gender["gender expansive"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "gender fluid") {
                    $gender["gender fluid"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "genderqueer") {
                    $gender["genderqueer"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "intersex") {
                    $gender["intersex"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "non-binary") {
                    $gender["non-binary"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "transfemale/transfeminine") {
                    $gender["transfemale/transfeminine"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "transmale/transmasculine") {
                    $gender["transmale/transmasculine"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "two-spirit") {
                    $gender["two-spirit"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "self describe") {
                    $gender["self describe"] += 1;
                }  else if($dataUserDetails && $dataUserDetails->gender == "Prefer not to answer") {
                    $gender["Prefer not to answer"] += 1;
                }
            });

            $feedbacksRepo = new FeedbackRepository(new Feedbacks());
            $repoDatafeedbacks = $feedbacksRepo->findbyparams(['appointment_id' => $item->id, 'favorite' => 1]);

            $i->count += 1;
            $csvArray->push([   
                'Round' => (string)$i->count,
                'Total Auditioners' => (string)count($userAuditions),
                'Starred Performers' => (string)$repoDatafeedbacks->count(),
                'Male' =>	$gender["male"],
                'Female' => $gender["female"],
                'Agender' => $gender["agender"],
                'Gender Diverse' => $gender["gender diverse"],
                'Gender Expansive' =>	$gender["gender expansive"],
                'Gender Fluid' =>	$gender["gender fluid"],
                'Genderqueer' => $gender["genderqueer"],
                'Intersex' =>	$gender["intersex"],
                'Non-Binary' => $gender["non-binary"],
                'Transfemale/Transfeminine' => $gender["transfemale/transfeminine"],
                'Transmale/Transmasculine' =>	$gender["transmale/transmasculine"],
                'Two-Spirit' => $gender["two-spirit"],
                'Prefer not to answer' =>	$gender["Prefer not to answer"],
                'Self Describe' => $gender["self describe"]
            ]);
        });
         
        return $csvArray;
    }
}

    

 


