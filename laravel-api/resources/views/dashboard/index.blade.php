@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard / ڈیش بورڈ')

@section('content')

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1A5C38,#2E7D52);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
                <span class="badge bg-white bg-opacity-25 text-white" style="font-size:10px;">Total</span>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label mt-1">Total Complaints</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#E65100,#F57C00);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <span class="badge bg-white bg-opacity-25 text-white" style="font-size:10px;">Pending</span>
            </div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label mt-1">Pending / زیر التوا</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1565C0,#1976D2);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
                <span class="badge bg-white bg-opacity-25 text-white" style="font-size:10px;">Active</span>
            </div>
            <div class="stat-value">{{ $stats['in_progress'] }}</div>
            <div class="stat-label mt-1">In Progress / جاری</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#2E7D32,#388E3C);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <span class="badge bg-white bg-opacity-25 text-white" style="font-size:10px;">Done</span>
            </div>
            <div class="stat-value">{{ $stats['resolved'] }}</div>
            <div class="stat-label mt-1">Resolved / حل شدہ</div>
        </div>
    </div>
</div>

{{-- ── Second row stats ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold" style="font-size:26px;color:#1A5C38;">{{ $stats['today'] }}</div>
            <div class="text-muted" style="font-size:12px;">Today's Complaints</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold" style="font-size:26px;color:#1565C0;">{{ $stats['this_month'] }}</div>
            <div class="text-muted" style="font-size:12px;">This Month</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold" style="font-size:26px;color:#6A1B9A;">{{ $totalUsers }}</div>
            <div class="text-muted" style="font-size:12px;">Registered Citizens</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold" style="font-size:26px;color:#E65100;">{{ $totalPriceItems }}</div>
            <div class="text-muted" style="font-size:12px;">Price Items Listed</div>
        </div>
    </div>
</div>

{{-- ── Charts + Recent ── --}}
<div class="row g-3 mb-4">
    {{-- Monthly chart --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-bar-chart-fill me-2" style="color:#1A5C38;"></i>Monthly Complaints (Last 6 months)</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Status donut --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6><i class="bi bi-pie-chart-fill me-2" style="color:#1A5C38;"></i>Status Breakdown</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="statusChart" style="max-height:200px;"></canvas>
                <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center">
                    <span class="badge badge-pending badge-status">Pending: {{ $stats['pending'] }}</span>
                    <span class="badge badge-in_progress badge-status">In Progress: {{ $stats['in_progress'] }}</span>
                    <span class="badge badge-resolved badge-status">Resolved: {{ $stats['resolved'] }}</span>
                    <span class="badge badge-rejected badge-status">Rejected: {{ $stats['rejected'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Price Update Logs Stats ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold" style="font-size:26px;color:#1A5C38;">{{ $totalUpdates }}</div>
            <div class="text-muted" style="font-size:12px;">Total Price Updates</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold" style="font-size:26px;color:#E65100;">{{ $todayUpdates }}</div>
            <div class="text-muted" style="font-size:12px;">Today's Updates</div>
        </div>
    </div>
    <div class="col-12 col-md-6 text-end">
        <a href="{{ route('admin.price-update-logs.index') }}" class="btn btn-sm btn-green mt-3">
            <i class="bi bi-clock-history me-1"></i>View All Price Update Logs
        </a>
        <a href="{{ route('admin.price-update-logs.export') }}" class="btn btn-sm btn-outline-success mt-3 ms-2">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
</div>

{{-- Recent Price Updates --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-arrow-up-down me-2" style="color:#1A5C38;"></i>Recent Price Updates (Last 10)</h6>
        <a href="{{ route('admin.price-update-logs.index') }}" class="btn btn-sm btn-green">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Item</th>
                    <th>Category</th>
                    <th>Old Price</th>
                    <th>New Price</th>
                    <th>Change</th>
                    <th>Source</th>
                    <th>By</th>
                    <th>Time</th>
                </tr></thead>
                <tbody>
                @forelse($recentPriceUpdates as $log)
                    @php $isUp = $log->change > 0; $isDown = $log->change < 0; @endphp
                    <tr>
                        <td class="ps-3">
                            <span class="fw-semibold" style="font-size:12.5px;">{{ $log->item?->name ?? 'Deleted' }}</span>
                        </td>
                        <td>
                            @if($log->item?->category)
                                <span class="badge" style="background:#E8F5E9;color:#1A5C38;font-size:10px;">
                                    @include('partials.cat-icon', ['icon' => $log->item->category->icon, 'size' => 12]) {{ $log->item->category->name }}
                                </span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px;">Rs. {{ number_format($log->old_price,2) }}</td>
                        <td style="font-size:12px;font-weight:600;">Rs. {{ number_format($log->new_price,2) }}</td>
                        <td>
                            @if($isUp)
                                <span class="price-up fw-semibold" style="font-size:11px;">
                                    <i class="bi bi-arrow-up-short"></i>+{{ number_format($log->change,2) }}
                                    ({{ number_format($log->change_percent,2) }}%)
                                </span>
                            @elseif($isDown)
                                <span class="price-down fw-semibold" style="font-size:11px;">
                                    <i class="bi bi-arrow-down-short"></i>{{ number_format($log->change,2) }}
                                    ({{ number_format($log->change_percent,2) }}%)
                                </span>
                            @else
                                <span class="price-same" style="font-size:11px;">— 0.00%</span>
                            @endif
                        </td>
                        <td>
                            @if($log->source == 'bulk')
                                <span class="badge bg-info text-white" style="font-size:9px;">Bulk</span>
                            @else
                                <span class="badge bg-secondary text-white" style="font-size:9px;">Single</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:11px;">{{ $log->updater?->name ?? 'System' }}</td>
                        <td class="text-muted" style="font-size:11px;">{{ $log->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No price updates yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Recent Complaints + Top Items ── --}}
<div class="row g-3">
    {{-- Recent complaints --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-clock-history me-2" style="color:#1A5C38;"></i>Recent Complaints</h6>
                <a href="{{ route('admin.complaints.index') }}" class="btn btn-sm btn-green">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th class="ps-3">Complaint #</th>
                        <th>Item</th>
                        <th>Shop</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr></thead>
                    <tbody>
                    @forelse($recentComplaints as $c)
                        <tr style="cursor:pointer;"
                            onclick="window.location='{{ route('admin.complaints.show', $c->id) }}'">
                            <td class="ps-3">
                                <span class="fw-semibold" style="color:#1A5C38;font-size:12px;">
                                    {{ $c->complaint_number }}
                                </span>
                            </td>
                            <td>{{ $c->item_name }}</td>
                            <td class="text-muted" style="font-size:12px;">{{ Str::limit($c->shop_name, 18) }}</td>
                            <td>
                                <span class="badge badge-status badge-{{ $c->status }}">
                                    {{ ucfirst(str_replace('_',' ',$c->status)) }}
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:12px;">{{ $c->created_at->format('d M') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No complaints yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top complained items --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h6><i class="bi bi-trophy-fill me-2" style="color:#E65100;"></i>Most Complained Items</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topItems as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <div>
                                <div class="fw-semibold" style="font-size:13.5px;">{{ $item->item_name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $item->total }} complaints</div>
                            </div>
                            <div class="progress" style="width:80px;height:6px;">
                                <div class="progress-bar" style="background:#1A5C38;width:{{ min(100, ($item->total / max($topItems->first()->total, 1)) * 100) }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">No data yet</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Monthly bar chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{
            label: 'Complaints',
            data: @json($monthlyData),
            backgroundColor: 'rgba(26,92,56,.75)',
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0f0f0' },
                 ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

// Status donut
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
        datasets: [{
            data: [
                {{ $stats['pending'] }},
                {{ $stats['in_progress'] }},
                {{ $stats['resolved'] }},
                {{ $stats['rejected'] ?? 0 }}
            ],
            backgroundColor: ['#F57F17','#1565C0','#2E7D32','#C62828'],
            borderWidth: 0,
        }]
    },
    options: {
        cutout: '70%',
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush
