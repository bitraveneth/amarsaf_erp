{{-- Kept for older bookmarks. Print and PDF use the shared V3 sheet. --}}
@php
    $company = $company ?? \App\Support\Documents\CompanyDocumentContext::make();
    $payload = $payload ?? \App\Support\Documents\DocumentDataFactory::build('invoice', $invoice);
    $type = $type ?? 'invoice';
    $id = $id ?? $invoice->id;
    $backUrl = $backUrl ?? route('admin.finance.show', $invoice);
@endphp
@include('documents.layouts.print')
