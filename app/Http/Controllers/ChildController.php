<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChildRequest;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        Gate::authorize('viewAny', Child::class);

        $user = Auth::user();

        if ($user->parent) {
            $children = $user->parent->children()->get();

        } else {
            $children = Child::all();
        }

        return ChildResource::collection($children);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChildRequest $request): ChildResource
    {

        Gate::authorize('create', Child::class);

        $data = $request->safe()->except('parents');
        $parents = $request->safe()->input('parents');

        $child = DB::transaction(function () use ($data, $parents) {
            $child = Child::create($data);

            $child->parents()->sync($parents);

            return $child;
        });

        return new ChildResource($child);
    }

    /**
     * Display the specified resource.
     */
    public function show(Child $child): ChildResource
    {
        Gate::authorize('view', $child);

        return new ChildResource($child);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChildRequest $request, Child $child): ChildResource
    {

        Gate::authorize('update', $child);

        $data = $request->safe()->except('parents');
        $parents = $request->safe()->input('parents');

        $child = DB::transaction(function () use ($child, $data, $parents) {
            $child->update($data);

            $child->parents()->sync($parents);

            return $child;
        });

        return new ChildResource($child);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Child $child)
    {

        Gate::authorize('delete', $child);

        $child->delete();

        return response()->noContent();
    }
}
