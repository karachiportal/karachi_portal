@extends('layouts.app')
@section('title', 'Price Update Logs')
@section('page-title', 'Price Update Logs / قیمت اپ ڈیٹ لاگز')

@section('content')

{{-- Filter bar --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.price-update-logs.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\PriceCategory::where('is_active',true)->orderBy('sort_order')->get() as $cat)
                        @php $__ic = $cat->icon; if(\Illuminate\Support\Str::startsWith($__ic ?? '', ['http://', 'https://'])) $__ic = '🖼️'; @endphp
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $__ic ?? '📦' }} {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Source</label>
                <select name="source" class="form-select form-select-sm">
                    <option value="">All Sources</option>
                    <option value="single" {{ request('source') == 'single' ? 'selected' : '' }}>Single Edit</option>
                    <option value="bulk" {{ request('source') == 'bulk' ? 'selected' : '' }}>Bulk Update</option>
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
            <div class="col-12 col-md-2">
                <label class="form-label mb-1" style="font-size:12px;font-weight:600;">Item Name</label>
                <input type="text" name="item_name" class="form-control form-control-sm"
                       placeholder="Search item..." value="{{ request('item_name') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-green flex-fill">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.price-update-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Logs table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-clock-history me-2" style="color:#1A5C38;"></i>
            {{ $logs->total() }} Price Update(s) Recorded</h6>
        <a href="{{ route('admin.price-update-logs.export', request()->all()) }}"
           class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Old Price (Rs.)</th>
                        <th>New Price (Rs.)</th>
                        <th>Change</th>
                        <th>Source</th>
                        <th>Updated By</th>
                        <th>Date/Time</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    @php
                        $isUp = $log->change > 0;
                        $isDown = $log->change < 0;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12px;">{{ $log->id }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:13px;">{{ $log->item?->name ?? 'Deleted' }}</div>
                            @if($log->item?->name_urdu)
                                <div class="urdu text-muted" style="font-size:11px;">{{ $log->item->name_urdu }}</div>
                            @endif
                        </td>
                        <td>
                            @if($log->item?->category)
                                <span class="badge" style="background:#E8F5E9;color:#1A5C38;font-size:11px;">
                                    @include('partials.cat-icon', ['icon' => $log->item->category->icon, 'size' => 13]) {{ $log->item->category->name }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="font-size:13px;">{{ number_format($log->old_price, 2) }}</td>
                        <td style="font-size:13px;font-weight:600;">{{ number_format($log->new_price, 2) }}</td>
                        <td>
                            @if($isUp)
                                <span class="price-up fw-semibold" style="font-size:12px;">
                                    <i class="bi bi-arrow-up-short"></i>+{{ number_format($log->change,2) }}
                                    <span style="font-size:10px;">({{ number_format($log->change_percent,2) }}%)</span>
                                </span>
                            @elseif($isDown)
                                <span class="price-down fw-semibold" style="font-size:12px;">
                                    <i class="bi bi-arrow-down-short"></i>{{ number_format($log->change,2) }}
                                    <span style="font-size:10px;">({{ number_format($log->change_percent,2) }}%)</span>
                                </span>
                            @else
                                <span class="price-same" style="font-size:12px;">— 0.00%</span>
                            @endif
                        </td>
                        <td>
                            @if($log->source == 'bulk')
                                <span class="badge bg-info text-white" style="font-size:10px;">Bulk</span>
                            @else
                                <span class="badge bg-secondary text-white" style="font-size:10px;">Single</span>
                            @endif
                        </td>
                        <td style="font-size:12px;">{{ $log->updater?->name ?? 'System' }}</td>
                        <td class="text-muted" style="font-size:11px;">
                            {{ $log->created_at?->format('d M Y') }}<br>
                            <span style="font-size:10px;">{{ $log->created_at?->format('h:i A') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size:32px;"></i>
                        <div class="mt-2">No price update logs found</div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:12px;">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
        </span>
        {{ $logs->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection
