@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice me-2"></i>Invoices
            </h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('financial.student-dashboard') }}">Financial Dashboard</a></li>
                    <li class="breadcrumb-item active">Invoices</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="card shadow">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Your Invoices</h6>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Print All
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Term</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                <td>{{ $invoice->term->name ?? 'N/A' }}</td>
                                <td>{{ $invoice->description ?? 'Term Invoice' }}</td>
                                <td class="text-end">
                                    <strong>${{ number_format($invoice->total_amount ?? 0, 2) }}</strong>
                                </td>
                                <td>
                                    @if(($invoice->status ?? 'pending') == 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif(($invoice->status ?? 'pending') == 'partial')
                                        <span class="badge bg-warning">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('financial.invoice.view', $invoice->id) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('financial.invoice.download', $invoice->id) }}" 
                                           class="btn btn-outline-success" title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('financial.invoice.print', $invoice->id) }}" 
                                           class="btn btn-outline-secondary" target="_blank" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No invoices found.
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <h6 class="text-muted">Total Invoiced</h6>
                    <h4>${{ number_format($invoices->sum('total_amount'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow">
                <div class="card-body">
                    <h6 class="text-muted">Paid Invoices</h6>
                    <h4>{{ $invoices->where('status', 'paid')->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning shadow">
                <div class="card-body">
                    <h6 class="text-muted">Pending Invoices</h6>
                    <h4>{{ $invoices->where('status', '!=', 'paid')->count() }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 4px solid #007bff;
}
.border-left-success {
    border-left: 4px solid #28a745;
}
.border-left-warning {
    border-left: 4px solid #ffc107;
}
</style>
@endpush
@endsection