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
use App\Http\Requests\UserTabletEdit;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Models\Notifications\NotificationSetting;
use App\Models\Notifications\NotificationSettingUser;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserSettings;
use App\Models\UserUnionMembers;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    const NOT_FOUND_DATA = "Not found Data";
    protected $log;
    protected $date;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['store', 'sendPassword', 'sendPasswordAdmin', 'forgotPassword', 'resetPassword']]);
        $this->log = new LogManger();
        $this->date = new ManageDates();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(): \Illuminate\Http\JsonResponse
    {
        $data = new UserRepository(new User());
        $count = count($data->all());
        if ($count !== 0) {
            $responseData = ['data' => UserResource::collection($data->all())];
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

            $usert->image()->create(['url' => request('image'), 'type' => 'cover', 'name' => request('resource_name')]);
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
            'address' => $request->address,
            'city' => isset($request->city) ? $request->city : "",
            'state' => $request->state,
            'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
            'agency_name' => $request->agency_name,
            'image' => $request->image,
            'profesion' => $request->profesion,
            //            'location' => $request->location,
            'zip' => $request->zip,
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
            'address' => $request->address,
            'city' => isset($request->city) ? $request->city : "",
            'state' => $request->state,
            'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
            'gender' => $request->gender,
            'stage_name' => $request->stage_name ?? null,
            'image' => $request->image,
            'url' => $request->url ?? null,
            'profesion' => $request->profesion,
            //            'location' => $request->location,
            'zip' => $request->zip,
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
                'address' => $request->address,
                'city' => isset($request->city) ? $request->city : "",
                'state' => $request->state,
                'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
                'gender' => $request->gender,
                'stage_name' => $request->stage_name,
                'profesion' => $request->profesion,
                'url' => $request->url,
                //                    'location' => $request->location,
                'zip' => $request->zip,
            ];
            $dataUser->image->update(['url' => $request->image]);
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
            $dataUser->image->update(['url' => $request->image]);
            $userDetails = new UserDetailsRepository(new UserDetails());
            $dataUserDetails = $userDetails->findbyparam('user_id', $request->id);
            $userDataDetails = [

                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address' => $request->address,
                'city' => isset($request->city) ? $request->city : "",
                'state' => $request->state,
                'birth' => isset($request->birth) ? $this->date->transformDate($request->birth) : null,
                'gender' => $request->gender,
                'agency_name' => $request->agency_name,
                'profesion' => $request->profesion,
                //                    'location' => $request->location,
                'zip' => $request->zip,
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
                    $dataResponse = ['data' => "email send"];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => "email not send"];
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
                    $dataResponse = ['data' => "email send"];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => "email not send"];
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
                $password_reset_token = Str::random(32);
                $userUpdate = $userRepo->find($user->id);
                if ($userUpdate->update(['password_reset_token' => $password_reset_token])) {
                    $response->sendForgotPasswordLink($password_reset_token, $user);
                    $dataResponse = ['data' => "email send"];
                    $code = 200;
                } else {
                    $dataResponse = ['data' => "email not send"];
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

}
