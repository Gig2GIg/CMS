<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\CreditsRepository;
use App\Http\Requests\CreditsRequest;
use App\Http\Resources\CreditsResource;
use App\Models\Credits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditsController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(CreditsRequest $request)
    {
        try {
            $data = [
                'type' => $request->type,
                'rol' => $request->rol,
                'name' => $request->name,
                'production' => $request->production,
                'year' => $request->year,
                'month' => $request->month,
                'user_id' => Auth::user()->getAuthIdentifier(),
            ];
            $repo = new CreditsRepository(new Credits());
            $credits = $repo->create($data);

            $dataResponse = [
                'message'=>'Credits created',
                'data' =>$credits
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
     * @param  \App\Credits $credits
     * @return \Illuminate\Http\Response
     */
    public function show(Credits $credits)
    {
        try {
            $repo = new CreditsRepository(new Credits());
            $data = $repo->find(request('id'));
            $dataResponse = ['data' => new CreditsResource($data)];
            $code = 200;
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Credits $credits
     * @return \Illuminate\Http\Response
     */
    public function getAll(Credits $credits)
    {

            $repo = new CreditsRepository(new Credits());
            $data = $repo->findbyparam('user_id',$this->getUserLogging());
            if ($data->count() > 0) {
                $dataResponse = ['data' => CreditsResource::collection($data)];
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
     * @param  \App\Credits $credits
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Credits $credits)
    {
        try {
            $data = [
                'type' => $request->type,
                'rol' => $request->rol,
                'name' => $request->name,
                'production' => $request->production,
                'year' => $request->year,
                'month' => $request->month,
            ];
            $repo = new CreditsRepository(new Credits());
            $dataCredit = $repo->find(request('id'));
            $result = $dataCredit->update($data);
            if ($result) {
                $dataResponse = ['data' => 'Credit updated'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Credit not updated'];
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
     * @param  \App\Credits $credits
     * @return \Illuminate\Http\Response
     */
    public function delete(Credits $credits)
    {
        try {

            $repo = new CreditsRepository(new Credits());
            $dataCredit = $repo->find(request('id'));
            $result = $dataCredit->delete();
            if ($result) {
                $dataResponse = ['data' => 'Credit removed'];
                $code = 200;
            } else {
                $dataResponse = ['data' => 'Credit not removed'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (NotFoundException $e) {
            return response()->json(['data' => 'Not Found Data'], 404);
        }
    }
}
