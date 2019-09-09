<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\SendMail;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserUnionMemberRepository;
use App\Http\Resources\PerformerFilterResource;
use App\Http\Resources\PerformerResource;
use App\Models\Performers;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use mysql_xdevapi\Collection;

class PerformersController extends Controller
{
    public function add(Request $request)
    {
        $message = null;
        try {
            $repo = new PerformerRepository(new Performers());
            $data = $repo->findbyparam('uuid', $request->code)->first();

            if ($data->director_id == $this->getUserLogging()) {
                $message = 'This user exits in your data base';
            } else {
                $register = [
                    'performer_id' => $data->performer_id,
                    'director_id' => $this->getUserLogging(),
                    'uuid' => Str::uuid()->toString(),
                ];
                $repo2 = new PerformerRepository(new Performers());
                $create = $repo->create($register);
                $message = 'Add User OK';
            }
            return response()->json(['data' => $message]);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Error add performer'], 406);
        }
    }

    public function shareCode(Request $request)
    {

        try {
            $repoSender = new UserRepository(new User());
            $dataSender = $repoSender->find($this->getUserLogging());
            $repoPerformer = new PerformerRepository(new Performers());
            $dataPerfomer = $repoPerformer->findbyparam('uuid', $request->code)->first();
            $dataReceiver = $repoSender->findbyparam('email', $request->email);
            if (isset($dataReciver->id)) {
                throw new NotFoundException('Not Found User', 404);
            }

            if (is_null($dataPerfomer)) {
                throw new NotFoundException('Shared code not found', 404);
            }
            $sender = sprintf('%s %s', $dataSender->details->first_name ?? '', $dataSender->details->last_name ?? '');
            $performer = sprintf('%s %s', $dataPerfomer->details->first_name ?? '', $dataPerfomer->details->last_name ?? '');
            $data = [
                'sender' => $sender,
                'performer' => $performer,
                'code' => $request->code
            ];
            $response = $this->notificator($dataReceiver, $data);
            if (!$response) {
                throw new \Exception('Error to notification');
            }
            return response()->json(['data' => 'Code share']);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            if ($exception instanceof NotFoundException) {
                return response()->json(['data' => $exception->getMessage()], 404);
            }
            return response()->json(['data' => 'Error send code'], 406);
        }
    }

    public function list(Request $request)
    {
        try {
            $repo = new PerformerRepository(new Performers());
            $data = $repo->findbyparam('director_id', $this->getUserLogging())->get();
            if ($data->count() == 0) {
                throw new \Exception('Not found data');
            }

            $dataResponse = PerformerResource::collection($data);
            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => 'Not found data'], 404);
        }
    }

    public function notificator($user, $data)
    {
        try {
            $email = new SendMail();
            $email->sendCode($user, $data);
            return true;
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return false;
        }
    }

    public function filter(Request $request)
    {
        try {
            if ($request->union!=0 && $request->base) {
                $dataResponse = $this->filterBaseUnion($request->base);
            } else if ($request->base) {
                $dataResponse = $this->filterBase($request->base);
            }


            return response()->json(['data' => PerformerFilterResource::collection($dataResponse)], 200);
        } catch (\Exception $exception) {
            return response()->json(['data' => 'Data not Found'], 404);
        }
    }


    public function filterBase($value)
    {
        try {
            $repo = new PerformerRepository(new Performers());
            $repoPerformer = $repo->findbyparam('director_id', $this->getUserLogging())->get()->pluck('performer_id')->toArray();
            $repoUserDetails = new UserDetailsRepository(new UserDetails());


            $data = $repoUserDetails->all()->whereIn('user_id', $repoPerformer);
            return $data->reject(function ($element) use ($value) {
                return mb_strpos($element->last_name, $value) === false;
            });

        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return collect();
        }
    }

    public function filterBaseUnion($value)
    {
        try {
            $repo = new PerformerRepository(new Performers());
            $repoPerformer = $repo->findbyparam('director_id', $this->getUserLogging())->get()->pluck('performer_id')->toArray();
            $repoUserDetails = new UserDetailsRepository(new UserDetails());
            $idReturn = $repoUserDetails->all()
                ->whereIn('user_id', $repoPerformer);
            $idReturn = $idReturn->reject(function ($element) {
                $repoUnion = new UserUnionMemberRepository(new UserUnionMembers());
                $count = $repoUnion->findbyparam('user_id', $element->user_id)->count();
                return $count === 0;
            });

            return $idReturn->reject(function ($element) use ($value) {
                return mb_strpos($element->last_name, $value) === false;
            });


        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return collect();
        }
    }

    /**
     * @param $value
     * @param UserDetailsRepository $repoUserDetails
     * @param $repoPerformer
     * @return UserDetailsRepository[]|\Illuminate\Database\Eloquent\Collection
     */
    public function filterByString($value, $collect, $idFind)
    {



    }


}
