@extends('layouts.admin')
@section('title', 'فاتورة '.$invoice->invoice_number)

@section('content')
@include('partials.invoice', ['invoice' => $invoice, 'showActions' => true])
@endsection
