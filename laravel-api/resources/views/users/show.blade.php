@extends('layouts.app')
@section('title', 'Citizen Detail')
@section('page-title', 'Citizen Profile')

@section('content')

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center py-4">
                <div style="width:72px;height:72px;background:#E8F5E9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <i class="bi bi-person-fill" style="font-size:36px;color:#1A5C38;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <code style="color:#1A5C38;font-size:13px;">{{ $user->username }}</code>
                <div class="mt-3 d-flex justify-content-center gap-2">
                    <span class="badge" style="background:#E8F5E9;color:#1A5C38;font-size:12px;">
                        {{ $user->complaints_count }} Complaints
                    </span>
                    <span class="badge bg-secondary" style="font-size:12px;">Citizen</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <table class="table table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="text-muted ps-0">CNIC</td>
                        <td class="fw-semibold">{{ $user->cnic }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Mobile</td>
                        <td>
                            <a href="tel:{{ $user->mobile }}" style="color:#1A5C38;">
                                {{ $user->mobile }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Email</td>
                        <td>{{ $user->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Joined</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>Back to Citizens
        </a>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-exclamation-circle me-2" style="color:#1A5C38;"></i>
                    Complaint History ({{ $user->complaints_count }})
                </h6>
                <a href="{{ route('admin.complaints.index', ['search' => $user->cnic]) }}"
                   class="btn btn-sm btn-green">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Complaint #</th>
                            <th>Item</th>
                            <th>Shop</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($complaints as $c)
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold" style="color:#1A5C38;font-size:12px;">
                                    {{ $c->complaint_number }}
                                </span>
                            </td>
                            <td style="font-size:13px;">{{ $c->item_name }}</td>
                            <td class="text-muted" style="font-size:12px;">
                                {{ Str::limit($c->shop_name, 20) }}
                            </td>
                            <td>
                                <span class="badge badge-status badge-{{ $c->status }}">
                                    {{ ucfirst(str_replace('_',' ',$c->status)) }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:12px;">
                                {{ $c->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.complaints.show', $c->id) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No complaints filed yet
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
