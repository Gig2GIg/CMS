<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\ManageDates;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\Notification\NotificationSettingUserRepository;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserSettingsRepository;
use App\Http\Repositories\UserUnionMemberRepository;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserStatusRequest;
use App\Http\Requests\UserTabletEdit;
use App\Http\Requests\InAppSuccessRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\InvitedUserResource;
use App\Http\Requests\SubscribeRequest;
use App\Http\Requests\InviteCasterRequest;
use App\Models\Admin;
use App\Models\Notifications\NotificationSetting;
use App\Models\Notifications\NotificationSettingUser;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserBillingDetails;
use App\Models\UserSettings;
use App\Models\UserUnionMembers;
use App\Models\UserSubscription;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Traits\StipeTraits;

class UserController extends Controller
{
    use StipeTraits;

    const NOT_FOUND_DATA = "Not found Data";
    protected $log;
    protected $date;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['store', 'sendPassword', 'sendPasswordAdmin', 'forgotPassword', 'resetPassword', 'listSubscriptionPlans']]);
        $this->log = new LogManger();
        $this->date = new ManageDates();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = new User();
        if($request->has('type') && $request->type != null){
            $type = $request->type;
            $typeArray = explode(',',$request->type);
            
            $allData = $data->whereHas('details', function ($query) use ($typeArray) {
            $query->whereIn('type', $typeArray);
            })->get();
        }else{
            $allData = $data->all();
        }
        
        $count = count($allData);
        if ($count !== 0) {
            $responseData = ['data' => UserResource::collection($allData)];
            $code = 200;
        } else {
            $responseData = ['data' => self::NOT_FOUND_DATA];
            $code = 404;
        }
        return response()->json($responseData, $code);
    }

    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();
            $userData = [
                'email' => request('email'),
                'password' => bcrypt(request('password')),
            ];

            $user = new UserRepository(new User());
            $usert = $user->create($userData);
            $customer = $this->createCustomer($usert);

            $usert->image()->create(['url' => request('image'), 'thumbnail' => $request->has('thumbnail') ? $request->thumbnail : NULL, 'type' => 'cover', 'name' => request('resource_name')]);
            if ($request->type === '1') {
                $this->storeTablet($request, $usert->id);
            } else {
                $this->storeApp($request, $usert->id);
            }

            $responseData = ['data' => 'User created'];
            $code = 201;

            DB::commit();

            return response()->json($responseData, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            DB::rollback();
            // return response()->json(['error' => 'ERROR'], 500);
            return response()->json(['error' => trans('messages.error')], 500);
        }
    }

    public function storeTablet(UserRequest $request, $id)
    {
        $dataName = explode(" ", $request->name);
        $userDataDetails = [
            'type' => $request->type,
            'first_name' => $request->first_name, //$dataName[0] ?? "null",
            'last_name' => $request->last_name, //$dataName[1] ?? "",
            'address' => isset($request->address) ? $request->address : null,
            'city' => isset($request->city) ? $request->city : "",
            'state' => isset($request->state) ? $request->state : null,
            'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
            'agency_name' => isset($request->agency_name) ? $request->agency_name : NULL,
            'image' => $request->image,
            'profesion' => isset($request->profesion) ? $request->profesion : NULL,
            'country' => $request->country,
            //            'location' => $request->location,
            'zip' => isset($request->zip) ? $request->zip : null,
            'user_id' => $id,
        ];
        $userDetails = new UserDetailsRepository(new UserDetails());

        try {

            $userDetails->create($userDataDetails);
            $this->create_setting(['AUDITIONS', 'CONTRIBUTORS'], $id);
            return true;
        } catch (CreateException $e) {
            $this->log->error($e->getMessage());
            return false;
        }
    }

    /**
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Http\Exceptions\CreateException
     */
    public function storeApp(UserRequest $request, $id)
    {

        $userDataDetails = [
            'type' => $request->type,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => isset($request->address) ? $request->address : null,
            'city' => isset($request->city) ? $request->city : "",
            'state' => isset($request->state) ? $request->state : null,
            'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
            'gender' => isset($request->gender) ? $request->gender : NULL,
            'stage_name' => $request->stage_name ?? null,
            'image' => $request->image,
            'url' => $request->url ?? null,
            'profesion' => isset($request->profesion) ? $request->profesion : NULL,
            'country' => $request->country,
            //            'location' => $request->location,
            'zip' => isset($request->zip) ? $request->zip : null,
            'user_id' => $id,
        ];
        try {
            $userDetails = new UserDetailsRepository(new UserDetails());
            $user = $userDetails->create($userDataDetails);

            foreach ($request->union_member as $iValue) {
                $userUnion = new UserUnionMemberRepository(new UserUnionMembers());
                $userUnion->create(['name' => $iValue['name'], 'user_id' => $id]);
            }
            $this->create_setting(['FEEDBACK', 'RECOMMENDATION'], $id);
            //CREATED DEFAULT NOTIFICATION SETTING
            $this->createNotificationSetting($user);

            return true;
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            return false;
        }
    }

    public function changeStatus(UserStatusRequest $request){
        try{
            $user = new UserRepository(new User());
            $userData = $user->find(request('id'));
            $updateData = array();
            $status = $request->status == 1 ? 1 : 0;
            $userData->update(['is_active' => $status]);

            return response()->json(['data' => trans('messages.success')], 200);

        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                $code = 404;
                $message = ['data' => 'Not Found Data'];
            } else {
                $code = 406;
                $message = ['data' => 'Unprocessable'];
            }
            return response()->json($message, $code);
        }
    }

    public function createNotificationSetting($user): void
    {
        try {
            DB::beginTransaction();
            $notificationSetting = NotificationSetting::where('status', 1)->get();

            foreach ($notificationSetting as $iValue) {
                $notificationSettingUserRepo = new NotificationSettingUserRepository(new NotificationSettingUser());
                $noti = $notificationSettingUserRepo->create([
                    'notification_setting_id' => $iValue['id'],
                    'user_id' => $user->user_id,
                    'code' => $iValue['code'],
                ]);
                $this->log->info($noti);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function show(): ?\Illuminate\Http\JsonResponse
    {
        try {
            $user = new UserRepository(new User());

            $data = $user->find(request('id'));

            if (!empty($data->email)) {
                $data = new UserResource($data);
                $responseData = ['data' => $data];
                $code = 200;
            } else {
                $responseData = ['data' => self::NOT_FOUND_DATA];
                $code = 404;
            }
            return response()->json($responseData, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => self::NOT_FOUND_DATA], 404);
        }
    }

    /**
     * @param UserEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserEditRequest $request)
    {

        try {
            $user = new UserRepository(new User());
            $this->log->info($request->id);
            $dataUser = $user->find($request->id);

            $data['email'] = $request->email;
            if (isset($request->password) && $dataUser->password !== bcrypt($request->password)) {
                $data['password'] = Hash::make($request->password);
            }
            $dataUser->update($data);
            $userDataDetails = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address' => isset($request->address) ? $request->address : null,
                'city' => isset($request->city) ? $request->city : "",
                'state' => isset($request->state) ? $request->state : null,
                'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
                'gender' => $request->gender,
                'stage_name' => $request->stage_name,
                'profesion' => $request->profesion,
                'url' => $request->url,
                'country' => isset($request->country) ? $request->country : null,
                //'location' => $request->location,
                'zip' => isset($request->zip) ? $request->zip : null,
            ];
            
            if($request->has('image') && $request->image != null){
                $dataUser->image->update(['url' => $request->image, 'thumbnail' => $request->has('thumbnail') ? $request->thumbnail : NULL, 'name' => $request->has('file_name') ? $request->file_name : NULL]);
            }
            $userDetails = new UserDetailsRepository(new UserDetails());
            $dataUserDetails = $userDetails->findbyparam('user_id', $request->id);
            $dat = $dataUserDetails->update($userDataDetails);
            if ($dat) {
                $responseUserRepo = new UserRepository(new User());
                $dataResponseUser = $responseUserRepo->find($request->id);
                $responseOut = ['data' => new UserResource($dataResponseUser)];
                $code = 200;
            } else {
                $responseOut = ['data' => 'Not updated'];
                $code = 406;
            }

            return response()->json($responseOut, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                $code = 404;
                $message = ['data' => 'Not Found Data'];
            } else {
                $code = 406;
                $message = ['data' => 'Unprocessable'];
            }
            return response()->json($message, $code);
        }
    }

    public function updateTablet(UserTabletEdit $request)
    {

        try {
            $user = new UserRepository(new User());
            $this->log->info($request->id);
            $dataUser = $user->find($request->id);
            $data['email'] = $request->email;
            if (isset($request->password) && $dataUser->password !== bcrypt($request->password)) {
                $data['password'] = Hash::make($request->password);
            }
            $dataUser->update($data);
            $name = explode(' ', $request->name);
            $dataUser->image->update(['url' => $request->image, 'thumbnail' => $request->has('thumbnail') ? $request->thumbnail : NULL, 'name' => $request->has('file_name') ? $request->file_name : NULL]);
            $userDetails = new UserDetailsRepository(new UserDetails());
            $dataUserDetails = $userDetails->findbyparam('user_id', $request->id);
            $userDataDetails = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address' => isset($request->address) ? $request->address : null,
                'city' => isset($request->city) ? $request->city : "",
                'state' => isset($request->state) ? $request->state : null,
                'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
                'gender' => $request->gender,
                'agency_name' => $request->agency_name,
                'profesion' => $request->profesion,
                'country' => isset($request->country) ? $request->country : null,
                //'location' => $request->location,
                'zip' => isset($request->zip) ? $request->zip : null,
            ];
            $dat = $dataUserDetails->update($userDataDetails);
            if ($dat) {
                $responseUserRepo = new UserRepository(new User());
                $dataResponseUser = $responseUserRepo->find($request->id);
                $responseOut = ['data' => new UserResource($dataResponseUser)];
                $code = 200;
            } else {
                $responseOut = ['data' => 'Not updated'];
                $code = 406;
            }
            return response()->json($responseOut, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                $code = 404;
                $message = ['data' => 'Not Found Data'];
            } else {
                $code = 406;
                $message = ['data' => 'Unprocessable'];
            }
            return response()->json($message, $code);
        }
    }

    public function delete(Request $request)
    {
        try {
            $user = new UserRepository(new User());
            $dataUser = $user->find($request->id);
            $details = new UserDetails();
            $details->where('user_id', $dataUser->id)->delete();
            $mebersUnion = new UserUnionMembers();
            $mebersUnion->where('user_id', $dataUser->id)->delete();
            $dataUser->image()->delete();
            $dataUser->delete();
            // return response()->json(['data' => 'User deleted'], 200);
            return response()->json(['data' => trans('messages.user_deleted')], 200);

        } catch (NotFoundException $e) {
            return response()->json(['data' => self::NOT_FOUND_DATA], 404);
        } catch (QueryException $e) {
            // return response()->json(['data' => "Unprocesable"], 406);
            return response()->json(['data' => trans('messages.not_processable')], 406);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws UpdateException
     */
    public function sendPassword(Request $request)
    {
        $dataResponse = null;
        $code = null;
        try {

            $response = new SendMail();
            $user = new UserRepository(new User());
            $data = $user->findbyparam('email', $request->email);
            $userUpdate = new UserRepository(new User());
            if (isset($data->id)) {
                $userUpdate->find($data->id);
                $faker = \Faker\Factory::create();
                $password = Str::random(4) . '' . $faker->numberBetween(2345, 4565);
                if ($data->update(['password' => Hash::make($password)])) {
                    $response->send($password, $data->email);
                    $dataResponse = ['data' => "email sent"];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => "email not sent"];
                    $code = 406;
                }
            } else {
                $dataResponse = ['data' => "email not found"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (QueryException $e) {
            $this->log->error($e);
            throw new UpdateException($e);
        } catch (NotFoundException $e) {
            // return response()->json(['data' => "email not found"], 404);
            return response()->json(['data' => trans('messages.email_not_found')], 404);

        }
    }

    public function sendPasswordAdmin(Request $request)
    {
        $dataResponse = null;
        $code = null;
        try {

            $response = new SendMail();
            $user = new Admin();
            $data = $user->where('email', $request->email)->first();
            if (isset($data->id)) {
                $faker = \Faker\Factory::create();
                $password = $faker->word . '' . $faker->numberBetween(2345, 4565);
                if ($data->update(['password' => Hash::make($password)])) {
                    $response->send($password, $data->email);
                    $dataResponse = ['data' => "email sent"];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => "email not sent"];
                    $code = 406;
                }
            } else {
                $dataResponse = ['data' => "email not found"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $e) {
            $this->log->error($e);
            // return response()->json(['data' => "email not found"], 404);
            return response()->json(['data' => trans('messages.email_not_found')], 404);
        }
    }

    public function updateMemberships(Request $request)
    {
        try {
            $repo = new UserUnionMemberRepository(new UserUnionMembers());
            $data = $repo->findbyparam('user_id', $this->getUserLogging());
            $data->each(function ($element) {
                $element->delete();
            });

            foreach ($request->data as $item) {
                $dataNew = new UserUnionMemberRepository(new UserUnionMembers());
                $dataNew->create([
                    'user_id' => $this->getUserLogging(),
                    'name' => $item['name'],
                ]);
            }
            // return response()->json(['data' => 'Unions update'], 200);
            return response()->json(['data' => trans('messages.unions_update')], 200);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Error to process'], 406);
            return response()->json(['data' => trans('messages.not_processable')], 406);
        }
    }

    public function listMemberships(Request $request)
    {
        $repo = new UserUnionMemberRepository(new UserUnionMembers());
        $data = $repo->findbyparam('user_id', $this->getUserLogging());
        if ($data->count() > 0) {
            $responseData = ['data' => $data];
            $code = 200;
        } else {
            $responseData = ['data' => self::NOT_FOUND_DATA];
            $code = 404;
        }
        return response()->json($responseData, $code);
    }

    public function create_setting(array $settings, $id)
    {
        foreach ($settings as $setting) {
            $repo = new UserSettingsRepository(new UserSettings());
            $repo->create([
                'user_id' => $id,
                'setting' => $setting,
                'value' => true,
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws UpdateException
     */
    public function forgotPassword(Request $request)
    {
        $dataResponse = null;
        $code = null;
        try {
            $response = new SendMail();
            $userRepo = new UserRepository(new User());
            $user = $userRepo->findbyparam('email', $request->email);

            if (isset($user->id)) {
                if($user->is_active){
                    $password_reset_token = Str::random(32);
                    $userUpdate = $userRepo->find($user->id);
                    if ($userUpdate->update(['password_reset_token' => $password_reset_token])) {
                        $response->sendForgotPasswordLink($password_reset_token, $user);
                        $dataResponse = ['data' => "email sent"];
                        $code = 200;
                    } else {
                        $dataResponse = ['data' => "email not sent"];
                        $code = 406;
                    }
                }else{
                    $dataResponse = ['data' => trans('messages.account_deactivated')];
                    $code = 403;
                }
            } else {
                $dataResponse = ['data' => "email not found"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (QueryException $e) {
            $this->log->error($e);
            throw new UpdateException($e);
        } catch (NotFoundException $e) {
            // return response()->json(['data' => "email not found"], 404);
            return response()->json(['data' => trans('messages.email_not_found')], 404);

        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws UpdateException
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $dataResponse = null;
        $code = null;
        try {
            $response = new SendMail();
            $userRepo = new UserRepository(new User());
            $user = $userRepo->findbyparam('password_reset_token', $request->token);
            if (isset($user->id)) {
                if ($user->update(['password_reset_token' => null, 'password' => Hash::make($request->password)])) {
                    $dataResponse = ['data' => "Password changed successfully"];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => "Password not changed"];
                    $code = 406;
                }
            } else {
                $dataResponse = ['data' => "Your one time link has been expired!"];
                $code = 404;
            }
            return response()->json($dataResponse, $code);
        } catch (QueryException $e) {
            $this->log->error($e);
            throw new UpdateException($e);
        } catch (NotFoundException $e) {
            // return response()->json(['data' => "email not found"], 404);
            return response()->json(['data' => trans('messages.email_not_found')], 404);

        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSocialLinks(Request $request)
    {
        try {
            $storeData = array();
            if($request->has('twitter')){
                $storeData['twitter'] = $request->twitter;
            }
            if($request->has('instagram')){
                $storeData['instagram'] = $request->instagram;
            }
            if($request->has('facebook')){
                $storeData['facebook'] = $request->facebook;
            }
            if($request->has('linkedin')){
                $storeData['linkedin'] = $request->linkedin;
            }
            
            if ($userDetails = UserDetails::where('user_id', $request->user_id)->first()) {
                $userDetails->update($storeData);
                $responseOut = ['data' => trans('messages.success')];
                $code = 200;
            } else {
                $responseOut = ['data' => self::NOT_FOUND_DATA];
                $code = 406;
            }

            return response()->json($responseOut, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                return response()->json(['data' => self::NOT_FOUND_DATA], 404);
            } else {
                return response()->json(['data' => trans('not_processable')], 406);
            }
        }
    }

    public function subscribe(SubscribeRequest $request)
    {
        try {
            $userRepo = new UserRepository(new User());
            $user = $userRepo->find($request->user_id); 

            // creating stripe customer 
            if($user->stripe_id == null || $user->stripe_id == ''){
                $customer = $this->createCustomer($user);
            }

            $cardData = array();
            $cardData['exp_year'] = $request->exp_year;
            $cardData['exp_month'] = $request->exp_month;
            $cardData['cvc'] = $request->cvc;
            $cardData['number'] = $request->number;
            $cardData['name_on_card'] = $request->name_on_card;

            if(!$user->subscribed($request->stripe_plan_name) && $user->is_premium != 1)
            {
                $cardToken = $this->createCardToken($cardData);
                $this->updateDefaultSrc($user, $cardToken);

                $paymentMethod = $user->defaultPaymentMethod();

                $planData = array();
                $planData['stripe_plan_id'] = $request->stripe_plan_id;
                $planData['stripe_plan_name'] = $request->stripe_plan_name;

                if ($response = $this->subscribeUser($user, $planData, $paymentMethod)) {
                    $user->update(array('is_premium' => 1));
                    $userBillingDetails = new UserBillingDetails();
                    $billingDetails = [
                        'user_id' => $user->id,
                        'address' => isset($request->address) ? $request->address : null,
                        'city' => isset($request->city) ? $request->city : null,
                        'state' => isset($request->state) ? $request->state : null,
                        'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
                        'country' => isset($request->country) ? $request->country : null,
                        'zip' => isset($request->zip) ? $request->zip : null,
                    ];
                    $userBillingDetails->create($billingDetails);
                    $responseOut = ['message' => trans('messages.subscribe_success')];
                    $code = 200;
                } else {
                    $responseOut = ['message' => trans('messages.subscribe_failed')];
                    $code = 406;
                }    
            }else{
                $responseOut = ['message' => trans('messages.subscribed_already')];
                $code = 406;
            }
            
            return response()->json($responseOut, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                return response()->json(['message' => self::NOT_FOUND_DATA], 404);
            } else {
                return response()->json(['message' => $e->getMessage()], 406);
            }
        }
    }

    public function listSubscriptionPlans(Request $request)
    {
        try {

            $plans = collect($this->listAllPlans());
                        
            $plans['data'] = collect($plans['data'])->sortBy('amount')->values()->map(function ($item, $key) {
                $item['amount'] = $item['amount'] / 100;
                $item['name'] = 'Tier ' . ($key + 1);
                return $item;
            });

            $responseData = ['data' => $plans];
            $code = 200;
            
            return response()->json($responseData, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                return response()->json(['message' => self::NOT_FOUND_DATA], 404);
            } else {
                return response()->json(['message' => $e->getMessage()], 406);
            }
        }
    }

    public function subscriptionDetails(Request $request)
    {
        try {
            $user = Auth::user();
            if($user->is_premium == 1 && $user->stripe_id != null && $user->invited_by == null)
            {
                $subscriptionData = $user->subscriptions()->first();
                $subscriptionData->card_brand = $user->card_brand;
                $subscriptionData->card_last_four = $user->card_last_four;

                $invitedUsers = InvitedUserResource::collection(User::where('invited_by', $user->id)->get());
                
                if ($subscriptionData)
                {
                    $response = (object)[
                        'subscription' => $subscriptionData,
                        'invitedUsers' => $invitedUsers
                    ];
                    $responseData = ['data' => $response];
                    $code = 200;
                }else {
                    $responseData = ['message' => self::NOT_FOUND_DATA];
                    $code = 404;
                }
            }else {
                $responseData = ['message' => self::NOT_FOUND_DATA];
                $code = 404;
            }
            
            return response()->json($responseData, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                return response()->json(['message' => self::NOT_FOUND_DATA], 404);
            } else {
                return response()->json(['message' => trans('not_processable')], 406);
            }
        }
    }

    public function inviteCaster(InviteCasterRequest $request)
    {
        try {
            $userRepo = new UserRepository(new User());
            $user = $userRepo->find($request->user_id);

            if($user->is_premium == 1 && $user->stripe_id != null && $user->invited_by == null)
            {
                $data = collect($request->data);
                foreach ($data as $item) {
                    //check if user_email exists in system
                    $exist = $userRepo->findbyparam('email', $item['email']);
                    if(!isset($exist->id))
                    {
                        // storing user data
                        $password = str_random(8); //random password for invited users

                        $userData = [
                            'email' => $item['email'],
                            'password' => bcrypt($password),
                            'invited_by' => $user->id,
                            'is_premium' => 1
                        ];

                        $cuser = new UserRepository(new User());
                        $usert = $cuser->create($userData);
                        $customer = $this->createCustomer($usert);    

                        $usert->image()->create(['url' => url('/images/roles.jpg'), 'thumbnail' => url('/images/roles.jpg'), 'type' => 'cover', 'name' => 'user_default.jpg']);

                        //storing user_details
                        $dataName = explode(" ", $item['name']);
                        $userDataDetails = [
                            'type' => 1,
                            'first_name' => $dataName[0] ?? null,
                            'last_name' => $dataName[1] ?? null,
                            'user_id' => $usert->id,
                        ];
                        $userDetails = new UserDetailsRepository(new UserDetails());

                        try {
                            $userDetails->create($userDataDetails);
                            $this->create_setting(['AUDITIONS', 'CONTRIBUTORS'], $usert->id);

                            $mail = new SendMail(); 
                            $emailData = array();
                            $emailData['name'] = $user->first_name . ' ' . $user->last_name; 
                            if(!$mail->sendInvitedCaster($password, $item['email'], $emailData)){
                                $responseData = ['message' => 'Something went wrong with sending email'];
                                $code = 400;
                                return response()->json($responseData, $code);
                            }
                        } catch (CreateException $e) {
                            $this->log->error($e->getMessage());
                            $responseData = ['message' => trans('something_went_wrong')];
                            $code = 400;
                            return response()->json($responseData, $code);
                        }
                    }else {
                        $responseData = ['message' => 'Sorry! The email '. $item['email'] .' is already registered with us'];
                        $code = 400;
                        return response()->json($responseData, $code);
                    }                    
                };

                $responseData = ['message' => trans('messages.success')];
                $code = 200;
            }else {
                $responseData = ['message' => trans('not_processable')];
                $code = 400;
            }
            
            return response()->json($responseData, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                return response()->json(['message' => self::NOT_FOUND_DATA], 404);
            } else {
                return response()->json(['message' => trans('not_processable')], 406);
            }
        }
    }

    public function inAppSuccess(InAppSuccessRequest $request)
    {
        try {
            $userRepo = new UserRepository(new User());
            $user = $userRepo->find($request->user_id);

            $subscription = new UserSubscription;
            $insertData = array();
            $insertData['user_id'] = $request->user_id;
            $insertData['name'] = $request->name;
            $insertData['product_id'] = $request->product_id;
            $insertData['current_transaction'] = $request->current_transaction;
            $insertData['purchase_platform'] = $request->purchase_platform;
            $insertData['purchased_at'] = $request->purchased_at;
            $insertData['stripe_status'] = 'active';
            if($request->has('ends_at')){
                $insertData['ends_at'] = $request->ends_at;
            }
            if($request->has('original_transaction')){
                $insertData['original_transaction'] = $request->original_transaction;
            }

            if($subscription->create($insertData) && $user->update(array('is_premium' => 1)))
            {
                $responseOut = ['message' => trans('messages.success')];
                $code = 200;    
            }else{
                $responseOut = ['message' => trans('something_went_wrong')];
                $code = 400;
            }
            
            return response()->json($responseOut, $code);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            if ($e instanceof NotFoundException) {
                return response()->json(['message' => self::NOT_FOUND_DATA], 404);
            } else {
                return response()->json(['message' => trans('not_processable')], 406);
            }
        }
    }
}
