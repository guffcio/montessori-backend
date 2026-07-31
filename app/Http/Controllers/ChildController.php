<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChildRequest;
use App\Http\Requests\UpdateChildRequest;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use App\UserRole;
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
    public function store(StoreChildRequest $request): ChildResource
    {

        Gate::authorize('create', Child::class);

        $data = $request->safe()->except('parents');
        $parents = $request->safe()->input('parents');
        $allergens = $request->safe()->input('allergens');

        $child = DB::transaction(function () use ($data, $parents, $allergens) {
            $child = Child::create($data);

            $child->parents()->sync($parents);

            $child->allergens()->sync($allergens);

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
    public function update(UpdateChildRequest $request, Child $child): ChildResource
    {
        Gate::authorize('update', $child);

        $isAdmin = Auth::user()->role === UserRole::ADMIN;

        $data = $isAdmin
            ? $request->safe()->except('parents')
            : $request->safe()->only([
                'first_name',
                'last_name',
                'birth_date',
                'pesel',
            ]);

        DB::transaction(function () use ($child, $data, $request, $isAdmin) {

            $child->update($data);

            if ($isAdmin) {
                $child->parents()->sync(
                    $request->validated('parents', [])
                );
            }

            $child->allergens()->sync($request->validated('allergens', []));
        });

        return new ChildResource($child->fresh());
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
