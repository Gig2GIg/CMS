<?php

namespace App\Http\Controllers\User;

use App\Http\Exceptions\User\UserNotFoundException;
use App\Http\Exceptions\User\UserUpdateException;
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
use App\Http\Controllers\Controller;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt');
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $data = new UserRepository(new User());
        $count = count($data->all());
        if ($count !== 0) {
            $responseData = UserResource::collection($data->all());
            return response()->json(['data' => $responseData], 200);
        } else {
            return response()->json(['data' => "Not found Data"], 404);
        }

    }


    /**
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Http\Exceptions\UserDetails\UserDetailsCreateException
     * @throws \App\Http\Exceptions\UserUnionMembers\UserUnionCreateException
     * @throws \App\Http\Exceptions\User\UserCreateException
     */
    public function createUser(UserRequest $request)
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
            $usert->image()->create(['url' => $request->image]);
            $userDetails = new UserDetailsRepository(new UserDetails());
            $userDetails->create($userDataDetails);

            foreach ($request->union_member as $iValue) {

                $userUnion = new UserUnionMemberRepository(new UserUnionMembers());
                $userUnion->create(['name' => $iValue['name'], 'user_id' => $usert->id]);
            }


            return response()->json(['message' => 'User Created'], 201);


        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getUser(): ?\Illuminate\Http\JsonResponse
    {
        try {
        $user = new UserRepository(new User());

            $data = $user->find(request('id'));

            if (!empty($data->email)) {
                $responseData = new UserResource($data);
                return response()->json(['data' => $responseData], 200);
            } else {
                return response()->json(['data' => "Not found Data"], 404);
            }
        } catch (UserNotFoundException $e) {
            return response()->json(['data' => "Not found Data"], 404);
        }

    }

    /**
     * @param UserEditRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Http\Exceptions\UserDetails\UserDetailsNotFoundException
     */
    public function updateUser(UserEditRequest $request)
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
                        'password' => bcrypt($request->password),
                    ];
                    $user->update($data);
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
                $dataUser->image->update(['url'=>$request->image]);
                $userDetails = new UserDetailsRepository(new UserDetails());
                $dataUserDetails = $userDetails->find($result[0]['details']['id']);
                $dataUserDetails->update($userDataDetails);

                return response()->json('User updated', 200);
            } catch (UserNotFoundException $e) {
                return response()->json(['data' => "Not found Data"], 404);
            } catch (UserUpdateException $e){
                return response()->json(['data' => "Unprocesable"], 406);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteUser(Request $request){
        try{
            $user = new UserRepository(new User());
            $dataUser = $user->find($request->id);
            $details = new UserDetails();
            $details->where('user_id',$dataUser->id)->delete();
            $mebersUnion = new UserUnionMembers();
            $mebersUnion->where('user_id',$dataUser->id)->delete();
            $dataUser->image()->delete();
            $dataUser->delete();
            return response()->json('User deleted', 200);
        }catch (UserNotFoundException $e){
            return response()->json(['data' => "Not found Data"], 404);
        }catch (QueryException $e){
            return response()->json(['data' => "Unprocesable"], 406);
        }
    }



}
