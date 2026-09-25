<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;
use App\Http\Resources\AbsenceResource;
use App\Jobs\Absence\SendParentReportedAbsenceNotificationJob;
use App\Models\Absence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Absence::class);

        $user = Auth::user();

        if ($user->isParent()) {
            $absences = $user->parent->absences()->get();

        } else {
            $absences = Absence::all();
        }

        return AbsenceResource::collection($absences);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAbsenceRequest $request): AbsenceResource
    {
        $data = $request->validated();

        Gate::authorize('create', [Absence::class, $data['child_id']]);

        $data['charge_catering'] = Absence::shouldChargeCatering($data['absent_at']);

        $absence = Absence::create($data);

        if (Auth::user()->isParent()) {
            SendParentReportedAbsenceNotificationJob::dispatch(
                $absence->id,
                Auth::user()->parent->id
            );
        }

        return new AbsenceResource($absence);
    }

    /**
     * Display the specified resource.
     */
    public function show(Absence $absence): AbsenceResource
    {

        Gate::authorize('view', $absence);

        return new AbsenceResource($absence);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAbsenceRequest $request, Absence $absence): AbsenceResource
    {
        Gate::authorize('update', $absence);

        $data = $request->validated();

        $absence->update($data);

        return new AbsenceResource($absence->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Absence $absence)
    {
        Gate::authorize('delete', $absence);

        $absence->delete();

        return response()->noContent();
    }
}
