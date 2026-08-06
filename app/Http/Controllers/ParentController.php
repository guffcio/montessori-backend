<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Http\Resources\ParentResource;
use App\Models\ParentUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $parentData = $request->safe()->except([...$userDataKeys, 'children']);
        $children = $request->safe()->input('children', []);

        $userData['password'] = Hash::make($userData['password']);

        $parent = DB::transaction(function () use ($userData, $parentData, $children) {
            $user = User::create($userData);

            $parent = ParentUser::create([
                ...$parentData,
                'user_id' => $user->id,
            ]);

            $parent->children()->sync($children);

            return $parent;
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

        $isAdmin = Auth::user()->isAdmin();

        $userDataKeys = ['email', 'phone', 'password'];
        $userData = $request->safe()->only($userDataKeys);
        $parentData = $request->safe()->except([...$userDataKeys, 'children']);

        if (! empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }

        $parentUser = DB::transaction(function () use ($parentUser, $userData, $parentData, $request, $isAdmin) {
            $parentUser->update($parentData);
            $parentUser->user()->update($userData);

            if ($isAdmin) {
                $parentUser->children()->sync($request->validated('children', []));
            }

            return $parentUser;
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
