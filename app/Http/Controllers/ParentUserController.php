<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParentRequest;
use App\Http\Resources\ParentResource;
use App\Models\ParentUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParentRequest $request): ParentResource
    {

        $userDataKeys = ['email', 'phone', 'password'];

        $userData = $request->safe()->only($userDataKeys);
        $parentData = $request->safe()->except($userDataKeys);

        $userData['password'] = Hash::make($userData['password']);

        $parent = DB::transaction(function () use ($userData, $parentData) {
            $user = User::create($userData);

            return ParentUser::create([
                ...$parentData,
                'user_id' => $user->id,
            ]);
        });

        return new ParentResource($parent);
    }

    /**
     * Display the specified resource.
     */
    public function show(ParentUser $parentUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParentUser $parentUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentUser $parentUser)
    {
        //
    }
}
