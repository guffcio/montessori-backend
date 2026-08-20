<?php

namespace App\Http\Controllers;

use App\Actions\InvoicePayment\CreateInvoicePaymentAction;
use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class InvoicePaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoicePaymentRequest $request, Invoice $invoice, CreateInvoicePaymentAction $action): JsonResponse
    {
        Gate::authorize('create', $invoice);

        $redirectUrl = $action->execute($request->validated(), $invoice);

        return response()->json([
            'data' => [
                'redirectUrl' => $redirectUrl,
            ],
        ]);

    }
}
