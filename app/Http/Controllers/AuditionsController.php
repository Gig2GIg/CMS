<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AuditionRepository;
use App\Http\Requests\AuditionRequest;
use App\Models\Auditions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditionsController extends Controller
{
    public function create(AuditionRequest $request)
    {
        if ($request->isJson()) {
            $auditionData = [
                'title' => $request->title,
                'date' => $request->date,
                'time' => $request->time,
                'location' => $request->location,
                'description' => $request->description,
                'url' => $request->url,
                'cover' => $request->cover,
                'union' => $request->union,
                'contract' => $request->contract,
                'production' => $request->production,
                'status' => $request->status,
                'user_id' => Auth::user()->getAuthIdentifier(),

            ];
            $auditRepo = new AuditionRepository(new Auditions());
            $audition = $auditRepo->create($auditionData);
            $auditionDatesData = [];
            $auditionContributirsData = [];
            $auditionRolesData = [];
            $auditionFilesData = [];
            foreach ($request->roles as $roles) {

                $auditionRolesData[] = [
                    'audition_id' => $audition->id,
                    'name' => $roles['name'],
                    'description' => $roles['description'],
                    'cover'=>$roles['cover']
                ];
            }

        }

        return response()->json(['data' => [$auditionData,$auditionRolesData]], 201);
    }
}
