<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\EducationsRepository;
use App\Http\Requests\EducationsRequest;
use App\Http\Resources\EducationsResource;
use App\Models\Educations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(EducationsRequest $request)
    {
        try {
            $data = [
                'school'=>$request->school,
                'degree'=>$request->degree,
                'instructor'=>$request->instructor,
                'location'=>$request->location,
                'year'=>$request->year,
                'user_id' => $this->getUserLogging(),
            ];
            $repo = new EducationsRepository(new Educations());
            $education = $repo->create($data);

            $dataResponse = [
                'message' =>'Educations created',
                'data' => $education
            ];
            $code = 201;
            return response()->json($dataResponse, $code);
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage());
            return response()->json(['error' => 'ERROR'], 500);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Educations $credits
     * @return \Illuminate\Http\Response
     */
    public function show(Educations $credits)
    {
        try {
            $repo = new EducationsRepository(new Educations());
            $data = $repo->find(request('id'));
            $dataResponse = ['data' => new EducationsResource($data)];
            $code = 200;
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Educations $credits
     * @return \Illuminate\Http\Response
     */
    public function getAll(Educations $educations)
    {

        $repo = new EducationsRepository(new Educations());
        $data = $repo->findbyparam('user_id',$this->getUserLogging());
        if ($data->count() > 0) {
            $dataResponse = ['data' => EducationsResource::collection($data)];
            $code = 200;
        } else {
            $dataResponse = ['data' => 'Not Found Data'];
            $code = 404;
        }

        return response()->json($dataResponse, $code);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Educations $credits
     * @return \Illuminate\Http\Response
     */
    public function update(EducationsRequest $request, Educations $educations)
    {
        try {
            $data = [
                'school'=>$request->school,
                'degree'=>$request->degree,
                'instructor'=>$request->instructor,
                'location'=>$request->location,
                'year'=>$request->year,
            ];
            $repo = new EducationsRepository(new Educations());
            $dataEducation = $repo->find(request('id'));
            $result = $dataEducation->update($data);
            if ($result) {
                $dataResponse = ['data' => 'Education updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Education not updated'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Educations $credits
     * @return \Illuminate\Http\Response
     */
    public function delete(Educations $educations)
    {
        try {

            $repo = new EducationsRepository(new Educations());
            $dataCredit = $repo->find(request('id'));
            $result = $dataCredit->delete();
            if ($result) {
                $dataResponse = ['data' => 'Education removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Education not removed'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }
}
