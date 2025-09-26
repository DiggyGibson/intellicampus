{{-- File: resources/views/admin/system-config/email-templates.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1">Email Templates</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.system-config.index') }}">System Configuration</a></li>
                    <li class="breadcrumb-item active">Email Templates</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Templates List --}}
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">System Email Templates</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Template Name</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td><strong>{{ $template->name }}</strong></td>
                                <td>
                                    <span class="badge bg-info">{{ $categories[$template->category] ?? ucfirst($template->category) }}</span>
                                </td>
                                <td>{{ Str::limit($template->subject, 50) }}</td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $template->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.system-config.email-templates.edit', $template->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No email templates found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            {{ $templates->links() }}
        </div>
    </div>
</div>
@endsection