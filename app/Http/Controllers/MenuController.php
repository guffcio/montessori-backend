<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Support\Facades\Gate;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Menu::class);

        return MenuResource::collection(Menu::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request): MenuResource
    {
        Gate::authorize('create', Menu::class);

        $data = $request->validated();

        $menu = Menu::create($data);

        return new MenuResource($menu);
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu): MenuResource
    {
        Gate::authorize('view', $menu);

        return new MenuResource($menu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): MenuResource
    {
        Gate::authorize('update', $menu);

        $menu->update($request->validated());

        return new MenuResource($menu->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        Gate::authorize('delete', $menu);

        $menu->delete();

        return response()->noContent();
    }
}
