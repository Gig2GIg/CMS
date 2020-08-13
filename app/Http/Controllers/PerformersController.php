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
use App\Models\PerformersComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Hashids\Hashids;
use Illuminate\Support\Facades\Auth;

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
            $user = Auth::user();            
            $repo = new PerformerRepository(new Performers());
            $data = $repo->findbyparam('uuid', $request->code)->first();

            if (!$data) {
                return response()->json(['data' => "This performer does not exist!"], 406);
            }

            //it is to fetch logged in user's invited users data if any
            $userRepo = new User();
            $invitedUserIds = $userRepo->where('invited_by', $this->getUserLogging())->get()->pluck('id');

            //It is to fetch other user's data conidering if logged in user is an invited user
            if($user->invited_by != NULL){
                $allInvitedUsersOfAdminIds = $userRepo->where('invited_by', $user->invited_by)->get()->pluck('id');

                //pushing invited_by ID in array too
                $allInvitedUsersOfAdminIds->push($user->invited_by); 

                $allIdsToInclude = $invitedUserIds->merge($allInvitedUsersOfAdminIds);
            }else{
                $allIdsToInclude = $invitedUserIds;
            }

            //pushing own ID into WHERE IN constraint
            $allIdsToInclude->push($this->getUserLogging()); 

            $count = $data->whereIn('director_id',$allIdsToInclude->unique()->values())->where('performer_id',$data->performer_id);

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

            if(!$request->has('code') || $request->code == '' || $request->code == null){
                return response()->json(['data' => trans('messages.add_to_talent_message')], 400);
            }

            $repoSender = new UserRepository(new User());
            $dataSender = $repoSender->find($this->getUserLogging());
            $repoPerformer = new PerformerRepository(new Performers());
            $dataPerfomer = $repoPerformer->findbyparam('uuid', $request->code)->first();
            $dataReceiver = $repoSender->findbyparam('email', $request->email);

            if (is_null($dataPerfomer)) {
                throw new NotFoundException('Shared code not found', 404);
            }
            
            $lastName = is_null($dataSender->details->last_name) ? '' : $dataSender->details->last_name;
            $sender = sprintf('%s %s', $dataSender->details->first_name ?? '',$lastName );
            $performer = sprintf('%s %s', $dataPerfomer->details->first_name ?? '', $dataPerfomer->details->last_name ?? '');
            
            $data = [
                'sender' => $sender,
                'performer' => $performer,
                'link' => $request->link
            ];

            if (!isset($dataReceiver->id)) {

                $to = $request->email;
                $response = $this->notificator($to, $data, 1);
                
                if (!$response) {
                    throw new \Exception('Error to notification');
                }
                // return response()->json(['data' => 'Code share']);
                return response()->json(['data' => trans('messages.success')]);
            }
  
            $data['code'] = $request->code;

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
            $user = Auth::user();            
            $repo = new PerformerRepository(new Performers());

            //it is to fetch logged in user's invited users data if any
            $userRepo = new User();
            $invitedUserIds = $userRepo->where('invited_by', $this->getUserLogging())->get()->pluck('id');

            //It is to fetch other user's data conidering if logged in user is an invited user
            if($user->invited_by != NULL){
                $allInvitedUsersOfAdminIds = $userRepo->where('invited_by', $user->invited_by)->get()->pluck('id');

                //pushing invited_by ID in array too
                $allInvitedUsersOfAdminIds->push($user->invited_by); 

                $allIdsToInclude = $invitedUserIds->merge($allInvitedUsersOfAdminIds);
            }else{
                $allIdsToInclude = $invitedUserIds;
            }

            //pushing own ID into WHERE IN constraint
            $allIdsToInclude->push($this->getUserLogging()); 

            $data = $repo->findByMultiVals('director_id', $allIdsToInclude->unique()->values())->get();            

            if ($data->count() == 0) {
                throw new \Exception('Not found data');
            }

            $dataResponse = PerformerResource::collection($data);
            $dataResponse->each(function ($value, $key) use($dataResponse) { 
                if(is_null($value->details)){
                    $dataResponse->forget($key);
                }
            });

            return response()->json(['data' => $dataResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Not found data'], 404);
        }   
    }

    public function notificator($user, $data, $type = 0)
    {
        try {
            $email = new SendMail();
            if($type == 1){
                $email->sendTalentDatabaseMail($user, $data);                
            }else{
                $email->sendCode($user, $data);
            }
            return true;
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return false;
        }
    }

    public function filter(Request $request)
    {
        try {
            $user = Auth::user();            
            $repo = new PerformerRepository(new Performers());

            //it is to fetch logged in user's invited users data if any
            $userRepo = new User();
            $invitedUserIds = $userRepo->where('invited_by', $this->getUserLogging())->get()->pluck('id');

            //It is to fetch other user's data conidering if logged in user is an invited user
            if($user->invited_by != NULL){
                $allInvitedUsersOfAdminIds = $userRepo->where('invited_by', $user->invited_by)->get()->pluck('id');

                //pushing invited_by ID in array too
                $allInvitedUsersOfAdminIds->push($user->invited_by); 

                $allIdsToInclude = $invitedUserIds->merge($allInvitedUsersOfAdminIds);
            }else{
                $allIdsToInclude = $invitedUserIds;
            }

            //pushing own ID into WHERE IN constraint
            $allIdsToInclude->push($this->getUserLogging()); 

            $repoPerformer = $repo->findByMultiVals('director_id', $allIdsToInclude->unique()->values())->get()->pluck('performer_id')->toArray();

            $repoUserDetails = new UserDetailsRepository(new UserDetails());
            $collectionFind = $repoUserDetails->all()->whereIn('user_id', $repoPerformer);

            if($request->base != '' && $request->base != null){
                $base = $this->filterBase($request->base, $collectionFind);
                $dataResponse = $base;
            } else {
                $dataResponse = $collectionFind;
            }
            
            if (isset($request->union)) {
                $dataResponse = $this->filterUnion($request->union, $dataResponse);
            }
            if (isset($request->gender)) {
                $dataResponse = $this->filterGender($request->gender, $dataResponse);
            }

            //passing all Ids to collection as an additional param
            $request->request->add(['allIdsToInclude' => $allIdsToInclude]);

            $finalResponse = PerformerFilterResource::collection($dataResponse);
            // dd($finalResponse);
            
            return response()->json(['data' => $finalResponse], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.data_not_found')], 404);
            // return response()->json(['data' => 'Data not Found'], 404);
        }
    }


    public function filterBase($value, $data)
    {
        try {
            $collection = collect();
            
            $name = explode(' ', $value);
            $collectionFind = $data;
            
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
            if ($union == 2) {
                $dataFilter = $userDetails->filter(function ($element) {
                    $repoUnion = new UserUnionMemberRepository(new UserUnionMembers());
                    $count = $repoUnion->findbyparam('user_id', $element->user_id)->count();
                    return $count === 0;
                });
            }

            if ($union == 0) {
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
            if ($gender === 'any' || $gender === 'ANY') {
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
            $user = Auth::user();            
            $dataRepo = new TagsRepository(new Tags());

            //it is to fetch logged in user's invited users data if any
            $userRepo = new User();
            $invitedUserIds = $userRepo->where('invited_by', $this->getUserLogging())->get()->pluck('id');

            //It is to fetch other user's data conidering if logged in user is an invited user
            if($user->invited_by != NULL){
                $allInvitedUsersOfAdminIds = $userRepo->where('invited_by', $user->invited_by)->get()->pluck('id');

                //pushing invited_by ID in array too
                $allInvitedUsersOfAdminIds->push($user->invited_by); 

                $allIdsToInclude = $invitedUserIds->merge($allInvitedUsersOfAdminIds);
            }else{
                $allIdsToInclude = $invitedUserIds;
            }

            //pushing own ID into WHERE IN constraint
            $allIdsToInclude->push($this->getUserLogging()); 

            // $data = $repo->findByMultiVals('director_id', $allIdsToInclude->unique()->values())->get();

            $data = $dataRepo->findByMultiVals('setUser_id', $allIdsToInclude->unique()->values())->where('user_id', $request->user)->get();

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
            $user = Auth::user();         

            $dataRepo = new FeedbackRepository(new Feedbacks());
            $commentModel = new PerformersComment();

            //it is to fetch logged in user's invited users data if any
            $userRepo = new User();
            $invitedUserIds = $userRepo->where('invited_by', $this->getUserLogging())->get()->pluck('id');

            //It is to fetch other user's data conidering if logged in user is an invited user
            if($user->invited_by != NULL){
                $allInvitedUsersOfAdminIds = $userRepo->where('invited_by', $user->invited_by)->get()->pluck('id');

                //pushing invited_by ID in array too
                $allInvitedUsersOfAdminIds->push($user->invited_by); 

                $allIdsToInclude = $invitedUserIds->merge($allInvitedUsersOfAdminIds);
            }else{
                $allIdsToInclude = $invitedUserIds;
            }

            //pushing own ID into WHERE IN constraint
            $allIdsToInclude->push($this->getUserLogging()); 

            // $data = $dataRepo->findByMultiVals('setUser_id', $allIdsToInclude->unique()->values())->where('user_id', $request->user)->get();

            $dataFeedback = $dataRepo->findByMultiVals('evaluator_id', $allIdsToInclude->unique()->values())->where('user_id', $request->user)->whereNotNull('comment')->get();
            $dataComments = $commentModel->whereIn('evaluator_id', $allIdsToInclude->unique()->values())->where('user_id', $request->user)->whereNotNull('comment')->get();
            
            if($dataComments && $dataComments->count() > 0){
                $data = $dataFeedback->merge($dataComments)->SortByDesc('created_at');
            }else{
                $data = $dataFeedback->SortByDesc('created_at');
            }
            
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
