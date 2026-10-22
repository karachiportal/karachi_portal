{{-- ══════════════════════════════════════════
     complaints/index.blade.php
═══════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'All Complaints')
@section('page-title', 'Complaints / شکایات')

@section('content')

{{-- Filter bar --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.complaints.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['pending','in_progress','resolved','rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_',' ',$s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Search</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Name, CNIC, Item, Shop, Complaint#"
                       value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-green flex-fill">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.complaints.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Status tab pills --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    @php
        $statuses = [''=>'All','pending'=>'Pending','in_progress'=>'In Progress','resolved'=>'Resolved','rejected'=>'Rejected'];
    @endphp
    @foreach($statuses as $val => $label)
        <a href="{{ route('admin.complaints.index', array_merge(request()->except('status','page'), ['status'=>$val])) }}"
           class="btn btn-sm {{ request('status', '') === $val ? 'btn-green' : 'btn-outline-secondary' }}">
            {{ $label }}
            @if($val === '') <span class="ms-1 badge bg-secondary">{{ $totalCount }}</span> @endif
        </a>
    @endforeach
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-list-ul me-2" style="color:#1A5C38;"></i>
            {{ $complaints->total() }} Complaint(s) found</h6>
        <a href="{{ route('admin.complaints.export', request()->all()) }}"
           class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Complaint No.</th>
                        <th>Citizen</th>
                        <th>Item / Shop</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($complaints as $c)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12px;">{{ $c->id }}</td>
                        <td>
                            <span class="fw-semibold" style="color:#1A5C38;font-size:12.5px;">
                                {{ $c->complaint_number }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:13px;">{{ $c->full_name }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $c->mobile }}</div>
                        </td>
                        <td>
                            <div style="font-size:13px;">{{ $c->item_name }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ Str::limit($c->shop_name,22) }}</div>
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ Str::limit($c->location_address,25) }}</td>
                        <td>
                            <span class="badge badge-status badge-{{ $c->status }}">
                                {{ ucfirst(str_replace('_',' ',$c->status)) }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:12px;">
                            {{ $c->created_at->format('d M Y') }}<br>
                            <span style="font-size:10px;">{{ $c->created_at->format('h:i A') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.complaints.show', $c->id) }}"
                               class="btn btn-sm btn-green">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size:32px;"></i>
                        <div class="mt-2">No complaints found</div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:12px;">
            Showing {{ $complaints->firstItem() }}–{{ $complaints->lastItem() }} of {{ $complaints->total() }}
        </span>
        {{ $complaints->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection
