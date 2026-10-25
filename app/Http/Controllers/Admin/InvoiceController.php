<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('order')
            ->when($request->q, function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->q.'%')
                    ->orWhere('cashier_name', 'like', '%'.$request->q.'%');
            })
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items', 'user']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['order.items', 'user']);

        return view('admin.invoices.print', compact('invoice'));
    }
}
