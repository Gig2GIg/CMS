<?php

namespace App\Http\Controllers\User;

use App\Http\Exceptions\User\UserNotFoundException;
use App\Http\Repositories\UserDetailsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\UserUnionMemberRepository;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{


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
                'stage_name'=>$request->stage_name,
                'image'=>$request->image,
                'profesion'=> $request->profesion,
                'location' => $request->location,
                'user_id' => $usert->id,
            ];
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        $user = new UserRepository(new User());
        try {
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



}
