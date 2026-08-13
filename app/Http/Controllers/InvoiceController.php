<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Actions\Invoice\ReissueInvoiceAction;
use App\Actions\Invoice\UpdateInvoicePaymentStatusAction;
use App\Data\Invoice\CreateInvoiceData;
use App\Data\Invoice\ReissueInvoiceData;
use App\Exceptions\Invoice\InvoiceCannotBeDeletedException;
use App\Exceptions\Invoice\InvoiceCannotBeRestoredException;
use App\Http\Requests\ReissueInvoiceRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoicePaymentStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\InvoicePaymentStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Invoice::class);

        $invoices = Invoice::all();

        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request, CreateInvoiceAction $action): InvoiceResource
    {
        Gate::authorize('create', Invoice::class);

        $dto = CreateInvoiceData::fromArray($request->validated());

        $invoice = $action->execute($dto);

        return new InvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): InvoiceResource
    {

        Gate::authorize('view', $invoice);

        return new InvoiceResource($invoice);
    }

    /**
     * Display the specified resource as PDF.
     */
    public function showPdf(Invoice $invoice)
    {

        Gate::authorize('view', $invoice);

        // TODO: GENERATE PDF AND SHOW IT

    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePaymentStatus(
        UpdateInvoicePaymentStatusRequest $request,
        Invoice $invoice,
        UpdateInvoicePaymentStatusAction $action
    ): InvoiceResource {

        Gate::authorize('update', $invoice);

        $updatedInvoice = $action->execute($invoice, InvoicePaymentStatus::from($request->validated('payment_status')));

        return new InvoiceResource($updatedInvoice);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        if (! $invoice->payment_status->canBeDeleted()) {
            throw new InvoiceCannotBeDeletedException;
        }

        $invoice->delete();

        return response()->noContent();
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Invoice $invoice)
    {
        Gate::authorize('restore', $invoice);

        if (! $invoice->canBeRestored()) {
            throw new InvoiceCannotBeRestoredException;
        }

        $invoice->restore();

        return response()->noContent();
    }

    /**
     * Reissue the specified resource from storage.
     */
    public function reissue(ReissueInvoiceRequest $request, Invoice $invoice, ReissueInvoiceAction $action): InvoiceResource
    {
        Gate::authorize('create', Invoice::class);

        $dto = ReissueInvoiceData::fromArray($request->validated());

        $reissuedInvoice = $action->execute($invoice, $dto);

        return new InvoiceResource($reissuedInvoice);
    }
}
