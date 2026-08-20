<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Actions\Invoice\MarkAsPaidInvoiceAction;
use App\Actions\Invoice\MarkAsUnpaidInvoiceAction;
use App\Actions\Invoice\PreviewInvoiceAction;
use App\Actions\Invoice\ReissueInvoiceAction;
use App\Data\Invoice\CreateInvoiceData;
use App\Data\Invoice\ReissueInvoiceData;
use App\Exceptions\Invoice\InvoiceCannotBeDeletedException;
use App\Exceptions\Invoice\InvoiceCannotBeRestoredException;
use App\Http\Requests\MarkAsPaidInvoiceRequest;
use App\Http\Requests\ReissueInvoiceRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\InvoicePaymentMethod;
use App\Models\Invoice;
use App\Services\Invoice\InvoicePdfGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\PdfBuilder;

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
     * Preview a newly created resource.
     */
    public function preview(StoreInvoiceRequest $request, PreviewInvoiceAction $action, InvoicePdfGenerator $invoicePdfGenerator): PdfBuilder
    {
        Gate::authorize('create', Invoice::class);

        $dto = CreateInvoiceData::fromArray($request->validated());

        $invoice = $action->execute($dto);

        return $invoicePdfGenerator->build($invoice);
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

        $path = $invoice->pdf_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Faktura VAT '.$invoice->number.'.pdf"',
        ]);

    }

    /**
     * Update the payment status in storage.
     */
    public function markAsPaid(
        MarkAsPaidInvoiceRequest $request,
        Invoice $invoice,
        MarkAsPaidInvoiceAction $action
    ): InvoiceResource {

        Gate::authorize('update', $invoice);

        $updatedInvoice = $action->execute($invoice, InvoicePaymentMethod::from($request->validated('payment_method')));

        return new InvoiceResource($updatedInvoice);

    }

    /**
     * Update the payment status in storage.
     */
    public function markAsUnpaid(
        $request,
        Invoice $invoice,
        MarkAsUnpaidInvoiceAction $action
    ): InvoiceResource {

        Gate::authorize('update', $invoice);

        $updatedInvoice = $action->execute($invoice);

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
