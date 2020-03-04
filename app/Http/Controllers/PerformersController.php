<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\SendMail;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\FeedbackRepository;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\TagsRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserUnionMemberRepository;
use App\Http\Resources\CommentListResponse;
use App\Http\Resources\PerformerFilterResource;
use App\Http\Resources\PerformerResource;
use App\Models\AuditionContract;
use App\Models\Auditions;
use App\Models\Feedbacks;
use App\Models\Performers;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Hashids\Hashids;

class PerformersController extends Controller
{
    public function add(Request $request)
    {
        $this->log->info("REQUEST ADD PERFORMER FOR CODE");
        $this->log->info($request);
        $message = null;
        $hasid = new Hashids('g2g');
        $dateHash = new \DateTime();
        $dataTime = $dateHash->getTimestamp();
        try {
            $repo = new PerformerRepository(new Performers());
            $data = $repo->findbyparam('uuid', $request->code)->first();

            if (!$data) {
                return response()->json(['data' => "This performer does not exist!"], 406);
            }

            $count = $data->where('director_id',$this->getUserLogging())
                ->where('performer_id',$data->performer_id);

            $this->log->info($data);
            if ($count->count() > 0) {
                $message = 'This user already exits in your data base';
                return response()->json(['data' => $message], 406);
            } else {
                $register = [
                    'performer_id' => $data->performer_id,
                    'director_id' => $this->getUserLogging(),
                    'uuid' => $hasid->encode($data->performer_id, $dataTime),
                ];
                $repo2 = new PerformerRepository(new Performers());
                $create = $repo->create($register);
                $this->log->info($create);
                $message = 'Add User OK';
            }
            return response()->json(['data' => $message]);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());

            return response()->json(['data' => trans('message.error_add_performer')], 406);
            // return response()->json(['data' => 'Error add performer'], 406);
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
            $lastName = is_null($dataSender->details->last_name) ? '' : $dataSender->details->last_name;
            $sender = sprintf('%s %s', $dataSender->details->first_name ?? '',$lastName );
            $performer = sprintf('%s %s', $dataPerfomer->details->first_name ?? '', $dataPerfomer->details->last_name ?? '');
            $data = [
                'sender' => $sender,
                'performer' => $performer,
                'code' => $request->code,
                'link' => $request->link
            ];
            $response = $this->notificator($dataReceiver, $data);
            if (!$response) {
                throw new \Exception('Error to notification');
            }
            // return response()->json(['data' => 'Code share']);
            return response()->json(['data' => trans('messages.code_share')]);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            if ($exception instanceof NotFoundException) {
                return response()->json(['data' => $exception->getMessage()], 404);
            }
            return response()->json(['data' => trans('messages.error_send_code')], 406);
            // return response()->json(['data' => 'Error send code'], 406);
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
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not found data'], 404);
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
            $repo = new PerformerRepository(new Performers());
            $repoPerformer = $repo->findbyparam('director_id', $this->getUserLogging())->get()->pluck('performer_id')->toArray();
            $base = $this->filterBase($request->base, $repoPerformer);
            $dataResponse = $base;
            if (isset($request->union)) {
                $dataResponse = $this->filterUnion($request->union, $dataResponse);
            }
            if (isset($request->gender)) {

                $dataResponse = $this->filterGender($request->gender, $dataResponse);
            }
            return response()->json(['data' => PerformerFilterResource::collection($dataResponse)], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Data not Found'], 404);
        }
    }


    public function filterBase($value, array $data)
    {
        try {
            $collection = collect();
            $name = explode(' ', $value);
            $repoUserDetails = new UserDetailsRepository(new UserDetails());
            $collectionFind = $repoUserDetails->all()->whereIn('user_id', $data);
            if (count($name) == 1) {
                $nameColl = $collectionFind->reject(function ($item) use ($name) {
                    return mb_strripos($item->first_name, $name[0]) === false;
                });

                $nameColl->each(function ($element) use ($collection) {
                    $collection->push($element);
                });
                $apeColl = $collectionFind->reject(function ($item) use ($name) {
                    return mb_strripos($item->last_name, $name[0]) === false;
                });
                $apeColl->each(function ($element) use ($collection) {
                    $collection->push($element);
                });
                return $collection->unique('id');
            } else {
                $filteFirstName = $collectionFind->reject(function ($item) use ($name) {
                    return mb_strripos($item->first_name, $name[0]) === false;
                });

                return $filteFirstName->reject(function ($item) use ($name) {
                    return mb_strripos($item->last_name, $name[1]) === false;
                });
            }


        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return collect();
        }
    }

    public function filterUnion($union, $userDetails)
    {
        try {
            $dataFilter = null;
            if ($union == 1) {
                $dataFilter = $userDetails->reject(function ($element) {
                    $repoUnion = new UserUnionMemberRepository(new UserUnionMembers());
                    $count = $repoUnion->findbyparam('user_id', $element->user_id)->count();
                    return $count === 0;
                });
            }
            if ($union == 0) {
                $dataFilter = $userDetails->filter(function ($element) {
                    $repoUnion = new UserUnionMemberRepository(new UserUnionMembers());
                    $count = $repoUnion->findbyparam('user_id', $element->user_id)->count();
                    return $count === 0;
                });
            }

            if ($union == 2) {
                $dataFilter = $userDetails;
            }

            return $dataFilter;

        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return collect();
        }
    }

    public function filterGender($gender, $userDetails)
    {
        try {
            if ($gender === 'ANY') {
                $dataFilter = $userDetails;
            } else {

                $dataFilter = $userDetails->filter(function ($element) use ($gender) {
                    return $element->gender == $gender;
                });
            }
            return $dataFilter;
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return collect();
        }
    }

    public function getTags(Request $request)
    {
        try {
            $dataRepo = new TagsRepository(new Tags());
            $data = $dataRepo->findbyparam('setUser_id', $this->getUserLogging())->where('user_id', $request->user)->get();

            // return response()->json(['message' => 'tags by user', 'data' => $data], 200);
            return response()->json(['message' => trans('messages.tag_by_user'), 'data' => $data], 200);

        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['message' => 'Data not found', 'data' => ''], 404);
            return response()->json(['message' => trans('messages.data_not_found'), 'data' => ''], 404);
        }
    }

    public function getCommnents(Request $request)
    {
        try {
            $dataRepo = new FeedbackRepository(new Feedbacks());
            $data = $dataRepo->findbyparam('evaluator_id', $this->getUserLogging())->where('user_id', $request->user)->whereNotNull('comment')->get();

            return response()->json(['message' => trans('messages.comment_by_user'), 'data' => CommentListResponse::collection($data)], 200);
            // return response()->json(['message' => 'comment by user', 'data' => CommentListResponse::collection($data)], 200);

        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.data_not_found'), 'data' => ''], 404);
            // return response()->json(['message' => 'Data not found', 'data' => ''], 404);
        }
    }

    public function getContracts(Request $request)
    {

        $this->log->info($this->getUserLogging());
        $this->log->info($request);
        try {
            $dataRepo = new AuditionRepository(new Auditions());

            $dataAuditions = $dataRepo->findbyparam('user_id', $this->getUserLogging())->unique();
            $dataTemp = AuditionContract::all()->whereIn('auditions_id', $dataAuditions->pluck('id'));
            $data = $dataTemp->where('user_id', $request->user);

            return response()->json(['message' => trans('messages.contracts_by_user'), 'data' => $data->toArray()], 200);
            // return response()->json(['message' => 'contracts by user', 'data' => $data->toArray()], 200);

        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['message' => trans('messages.data_not_found'), 'data' => ''], 404);
        }
    }
}
