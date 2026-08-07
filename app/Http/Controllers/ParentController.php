<?php

namespace App\Http\Controllers;

use App\Actions\Parent\CreateParentAction;
use App\Actions\Parent\UpdateParentAction;
use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Http\Resources\ParentResource;
use App\Models\ParentUser;
use Illuminate\Support\Facades\Gate;

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
    public function store(StoreParentRequest $request, CreateParentAction $action): ParentResource
    {

        Gate::authorize('create', ParentUser::class);

        $parent = $action->exectute($request->validated());

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
    public function update(UpdateParentRequest $request, ParentUser $parentUser, UpdateParentAction $action): ParentResource
    {

        Gate::authorize('update', $parentUser);

        $action->exectute($parentUser, $request->validated());

        return new ParentResource($parentUser->fresh());

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
