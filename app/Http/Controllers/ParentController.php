<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Http\Resources\ParentResource;
use App\Models\ParentUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', ParentUser::class);

        return ParentResource::collection(ParentUser::with('user')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParentRequest $request): ParentResource
    {

        Gate::authorize('create', ParentUser::class);

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
    public function show(ParentUser $parentUser): ParentResource
    {

        Gate::authorize('view', $parentUser);

        return new ParentResource($parentUser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParentRequest $request, ParentUser $parentUser): ParentResource
    {

        Gate::authorize('update', $parentUser);

        $userDataKeys = ['email', 'phone', 'password'];
        $userData = $request->safe()->only($userDataKeys);
        $parentData = $request->safe()->except($userDataKeys);

        DB::transaction(function () use ($parentUser, $userData, $parentData) {
            $parentUser->update($parentData);
            $parentUser->user()->update($userData);
        });

        return new ParentResource($parentUser);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentUser $parentUser)
    {
        Gate::authorize('delete', $parentUser);

        $parentUser->delete();

        return response()->noContent();
    }
}
