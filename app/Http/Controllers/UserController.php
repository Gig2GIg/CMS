<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Controllers\Utils\SendMail;
use App\Http\Exceptions\NotFoundException;
use App\Http\Exceptions\UpdateException;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserUnionMemberRepository;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    const NOT_FOUND_DATA = "Not found Data";
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => ['store', 'sendPassword']]);
        $this->log = new LogManger();
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


    /**
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Http\Exceptions\CreateException
     */
    public function store(UserRequest $request)
    {


        if ($request->json()) {
            $userData = [
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ];
            $user = new UserRepository(new User());
            $usert = $user->create($userData);
            $userDataDetails = [
                'type' => $request->type,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'birth' => $request->birth,
                'stage_name' => $request->stage_name,
                'image' => $request->image,
                'profesion' => $request->profesion,
                'location' => $request->location,
                'zip' => $request->zip,
                'user_id' => $usert->id,
            ];
            $usert->image()->create(['url' => $request->image, 'type' => 'image']);
            $userDetails = new UserDetailsRepository(new UserDetails());
            $userDetails->create($userDataDetails);

            foreach ($request->union_member as $iValue) {

                $userUnion = new UserUnionMemberRepository(new UserUnionMembers());
                $userUnion->create(['name' => $iValue['name'], 'user_id' => $usert->id]);
            }

            $responseData = ['data' => 'User Created'];
            $code = 201;


        } else {
            $responseData = ['error' => 'Unauthorized'];
            $code = 401;

        }
        return response()->json($responseData, $code);
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
        if ($request->json()) {
            try {
                $user = new UserRepository(new User());
                $dataUser = $user->find($request->id);
                $result = $dataUser->with('details')
                    ->with('memberunions')
                    ->with('image')
                    ->get();

                if ($dataUser->password !== bcrypt($request->password)) {
                    $data = [
                        'password' => Hash::make($request->password),
                    ];
                    $dataUser->update($data);
                }

                $userDataDetails = [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'birth' => $request->birth,
                    'stage_name' => $request->stage_name,
                    'profesion' => $request->profesion,
                    'location' => $request->location,
                    'zip' => $request->zip,
                ];
                $dataUser->image->update(['url' => $request->image]);
                $userDetails = new UserDetailsRepository(new UserDetails());
                $dataUserDetails = $userDetails->find($result[0]['details']['id']);
                $dataUserDetails->update($userDataDetails);

                return response()->json(['data' => 'User updated'], 200);
            } catch (NotFoundException $e) {
                return response()->json(['data' => self::NOT_FOUND_DATA], 404);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
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
            return response()->json(['data' => 'User deleted'], 200);
        } catch (NotFoundException $e) {
            return response()->json(['data' => self::NOT_FOUND_DATA], 404);
        } catch (QueryException $e) {
            return response()->json(['data' => "Unprocesable"], 406);
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
        } catch (QueryException $e) {
            $this->log->error($e);
            throw new UpdateException($e);

        } catch (NotFoundException $e) {
            return response()->json(['data' => "email not found"], 404);
        }


    }


}
