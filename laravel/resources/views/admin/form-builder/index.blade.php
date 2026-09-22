@extends('layouts.app')

@section('title', 'Dynamic Form Builder')
@section('header-title', 'Dynamic Transaction Form Builder')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Transaction Form Templates</h4>
            <p class="text-muted mb-0">Customize dynamic fields captured during scale weighment operations.</p>
        </div>
        <a href="{{ route('form-builder.create') }}" class="btn btn-primary fw-bold">
            <i class="bi bi-plus-lg me-1"></i> Build New Form Template
        </a>
    </div>

    <div class="row g-4">
        @forelse($forms as $form)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 position-relative {{ $form->is_active ? 'border-start border-primary border-4' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title fw-bold mb-0 text-truncate">{{ $form->name }}</h5>
                        @if($form->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">ACTIVE FORM</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">INACTIVE</span>
                        @endif
                    </div>
                    <p class="text-muted fs-7 mb-3">
                        <i class="bi bi-list-task me-1"></i> {{ $form->fields_count }} Dynamic Fields Configured
                    </p>
                    <div class="fs-7 text-muted mb-4">
                        Updated: {{ $form->updated_at ? $form->updated_at->diffForHumans() : 'N/A' }}
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('form-builder.edit', $form->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-pencil-square me-1"></i> Edit Builder
                        </a>
                        @if(!$form->is_active)
                            <form action="{{ route('form-builder.activate', $form->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Active Form">
                                    <i class="bi bi-check2-circle"></i> Activate
                                </button>
                            </form>
                            <form action="{{ route('form-builder.destroy', $form->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this form template?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Form">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-ui-checks-grid display-4 text-muted"></i>
            <h5 class="mt-3 text-muted">No form templates found</h5>
            <a href="{{ route('form-builder.create') }}" class="btn btn-primary mt-2">Create First Form Template</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
