@extends('layouts.app')
@section('title', 'Bulk Price Update')
@section('page-title', 'Bulk Price Update / قیمتیں یکمشت اپ ڈیٹ کریں')

@section('content')

{{-- Info card --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card" style="border-left:4px solid #1A5C38;">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <i class="bi bi-info-circle-fill" style="color:#1A5C38;font-size:22px;"></i>
                <div>
                    <div class="fw-semibold" style="font-size:13.5px;">Bulk Update / Import Prices</div>
                    <div class="text-muted" style="font-size:12.5px;">
                        Edit prices inline below, or <strong>import</strong> from an Excel file.
                        Download the current prices, edit in Excel, then upload.
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('admin.prices.export') }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download me-1"></i> Download All Categories
                    </a>
                    <div class="text-muted" style="font-size:12px;line-height:32px;">
                        <i class="bi bi-clock me-1"></i>{{ $lastUpdated }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin())
{{-- Import section --}}
<div class="card mb-4" style="border:2px dashed #1A5C38;">
    <div class="card-body py-3">
        <form id="importForm" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-5">
                <label class="form-label mb-1 fw-semibold" style="font-size:12px;">Upload Excel File (.xlsx)</label>
                <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required
                       onchange="document.getElementById('previewBtn').click()">
            </div>
            <div class="col-md-2">
                <button type="button" id="previewBtn" class="btn btn-sm btn-green w-100" onclick="previewImport()">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
            </div>
            <div class="col-md-5 text-end">
                <div id="importSummary" class="d-inline-block" style="font-size:12px;"></div>
                <button id="confirmImportBtn" class="btn btn-sm btn-success d-none" onclick="confirmImport()">
                    <i class="bi bi-check-circle me-1"></i> Confirm Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import preview table --}}
<div id="importPreviewArea" style="display:none;">
    <div class="card mb-4">
        <div class="card-header py-2">
            <h6 class="mb-0" style="font-size:13px;"><i class="bi bi-table me-2" style="color:#1A5C38;"></i>Import Preview</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="importPreviewTable" style="font-size:12.5px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Old Price</th>
                            <th>New Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="importPreviewBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Import errors --}}
<div id="importErrorArea" style="display:none;">
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white py-2">
            <h6 class="mb-0 text-white" style="font-size:13px;"><i class="bi bi-exclamation-triangle me-2"></i>Errors</h6>
        </div>
        <div class="card-body p-0">
            <ul id="importErrorList" class="list-group list-group-flush" style="font-size:12.5px;"></ul>
        </div>
    </div>
</div>

{{-- Loading --}}
<div id="importLoading" style="display:none;" class="text-center py-4">
    <div class="spinner-border" style="color:#1A5C38;" role="status"></div>
    <p class="mt-2 text-muted" style="font-size:13px;">Processing...</p>
</div>
@endif

{{-- Category tabs --}}
<ul class="nav nav-tabs mb-0" id="catTabs">
    @foreach($categories as $i => $cat)
        <li class="nav-item">
            <button class="nav-link {{ $i === 0 ? 'active' : '' }} d-flex align-items-center gap-1"
                    data-bs-toggle="tab"
                    data-bs-target="#cat-{{ $cat->slug }}"
                    style="font-size:13px;">
                @include('partials.cat-icon', ['icon' => $cat->icon, 'size' => 16]) {{ $cat->name }}
                <span class="badge ms-1" style="background:#E8F5E9;color:#1A5C38;font-size:10px;">
                    {{ $cat->items->count() }}
                </span>
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    @foreach($categories as $i => $cat)
    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="cat-{{ $cat->slug }}">
        <div class="card" style="border-radius:0 14px 14px 14px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    @include('partials.cat-icon', ['icon' => $cat->icon, 'size' => 20])
                    {{ $cat->name }} — {{ $cat->name_urdu }}
                </h6>
                <div class="d-flex gap-2 align-items-center">
                    <span id="changed-count-{{ $cat->slug }}" class="badge bg-warning text-dark d-none">
                        <span class="count">0</span> changed
                    </span>
                    <a href="{{ route('admin.prices.export', ['category_slug' => $cat->slug]) }}"
                       class="btn btn-sm btn-outline-success" title="Export this category">
                        <i class="bi bi-download"></i>
                    </a>
                    @if(Auth::user()->isAdmin())
                    <button class="btn btn-sm btn-green save-btn"
                            data-cat="{{ $cat->slug }}"
                            onclick="saveCategory('{{ $cat->slug }}')" disabled>
                        <i class="bi bi-cloud-upload me-1"></i>Save All Changes
                    </button>
                    <button class="btn btn-sm btn-outline-secondary reset-btn"
                            onclick="resetCategory('{{ $cat->slug }}')">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Item</th>
                            <th>Urdu Name</th>
                            <th>Unit</th>
                            <th>Previous Price</th>
                            <th>Current Price</th>
                            <th>New Price (Rs.)</th>
                            <th>Change</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-{{ $cat->slug }}">
                    @foreach($cat->items->where('is_active', true) as $item)
                        <tr id="bulk-row-{{ $item->id }}" data-original="{{ $item->price }}" data-cat="{{ $cat->slug }}">
                            <td class="ps-3 fw-semibold" style="font-size:13.5px;">{{ $item->name }}</td>
                            <td class="urdu text-muted">{{ $item->name_urdu }}</td>
                            <td style="font-size:12.5px;">{{ $item->unit }}</td>
                            <td class="text-muted" style="font-size:13px;">
                                Rs. {{ number_format($item->previous_price, 2) }}
                            </td>
                            <td style="font-size:13.5px;">
                                <strong>Rs. {{ number_format($item->price, 2) }}</strong>
                            </td>
                            <td style="width:160px;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" style="font-size:12px;">Rs.</span>
                                    <input type="number"
                                           class="form-control bulk-price-input"
                                           id="bulk-{{ $item->id }}"
                                           data-item-id="{{ $item->id }}"
                                           data-cat="{{ $cat->slug }}"
                                           data-original="{{ $item->price }}"
                                           value="{{ $item->price }}"
                                           step="0.5" min="0"
                                           style="font-size:13px;"
                                           oninput="onPriceChange(this)">
                                </div>
                            </td>
                            <td id="change-preview-{{ $item->id }}" style="font-size:12px;min-width:90px;">
                                @if($item->price_change > 0)
                                    <span class="price-up">↑ +{{ number_format($item->price_change,2) }}</span>
                                @elseif($item->price_change < 0)
                                    <span class="price-down">↓ {{ number_format($item->price_change,2) }}</span>
                                @else
                                    <span class="price-same">— 0.00</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
// Track changes per category
const changedItems = {};

function onPriceChange(input) {
    const id      = input.dataset.itemId;
    const cat     = input.dataset.cat;
    const orig    = parseFloat(input.dataset.original);
    const newVal  = parseFloat(input.value);
    const diff    = newVal - orig;
    const pct     = orig > 0 ? ((diff / orig) * 100).toFixed(2) : '0.00';

    // Update change preview
    const preview = document.getElementById(`change-preview-${id}`);
    if (diff > 0) {
        preview.innerHTML = `<span class="price-up">↑ +${diff.toFixed(2)} <small>(${pct}%)</small></span>`;
    } else if (diff < 0) {
        preview.innerHTML = `<span class="price-down">↓ ${diff.toFixed(2)} <small>(${pct}%)</small></span>`;
    } else {
        preview.innerHTML = `<span class="price-same">— 0.00</span>`;
    }

    // Highlight row if changed
    const row = document.getElementById(`bulk-row-${id}`);
    if (diff !== 0) {
        row.style.background = '#FFFDE7';
        if (!changedItems[cat]) changedItems[cat] = new Set();
        changedItems[cat].add(id);
    } else {
        row.style.background = '';
        if (changedItems[cat]) changedItems[cat].delete(id);
    }

    // Update counter badge
    const count = changedItems[cat] ? changedItems[cat].size : 0;
    const badge = document.getElementById(`changed-count-${cat}`);
    const btn   = document.querySelector(`.save-btn[data-cat="${cat}"]`);
    if (count > 0) {
        badge.classList.remove('d-none');
        badge.querySelector('.count').textContent = count;
        btn.disabled = false;
    } else {
        badge.classList.add('d-none');
        btn.disabled = true;
    }
}

function saveCategory(cat) {
    const inputs = document.querySelectorAll(`.bulk-price-input[data-cat="${cat}"]`);
    const items  = [];

    inputs.forEach(inp => {
        const orig = parseFloat(inp.dataset.original);
        const val  = parseFloat(inp.value);
        if (val !== orig) {
            items.push({ id: inp.dataset.itemId, price: val });
        }
    });

    if (items.length === 0) { toastr.info('No changes to save'); return; }

    const btn = document.querySelector(`.save-btn[data-cat="${cat}"]`);
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    $.ajax({
        url: '/admin/prices/bulk-update',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            _token: $('meta[name=csrf-token]').attr('content'),
            items: items
        }),
        success: function(r) {
            if (r.success) {
                toastr.success(r.message || `${items.length} price(s) updated`);
                // Update originals and reset highlights
                items.forEach(item => {
                    const inp = document.getElementById(`bulk-${item.id}`);
                    inp.dataset.original = item.price;
                    document.getElementById(`bulk-row-${item.id}`).style.background = '#E8F5E9';
                    setTimeout(() => {
                        document.getElementById(`bulk-row-${item.id}`).style.background = '';
                    }, 2000);
                });
                if (changedItems[cat]) changedItems[cat].clear();
                document.getElementById(`changed-count-${cat}`).classList.add('d-none');
            }
        },
        error: x => toastr.error('Failed to update prices'),
        complete: () => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Save All Changes';
        }
    });
}

function resetCategory(cat) {
    document.querySelectorAll(`.bulk-price-input[data-cat="${cat}"]`).forEach(inp => {
        inp.value = inp.dataset.original;
        onPriceChange(inp);
    });
    toastr.info('Changes reset');
}

// Keyboard shortcut: Ctrl+S to save active tab
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        const activeTab = document.querySelector('.tab-pane.active');
        if (activeTab) {
            const cat = activeTab.id.replace('cat-', '');
            saveCategory(cat);
        }
    }
});

// ─── Import functionality ───
function previewImport() {
    const form = document.getElementById('importForm');
    const formData = new FormData(form);

    document.getElementById('importLoading').style.display = 'block';
    document.getElementById('importPreviewArea').style.display = 'none';
    document.getElementById('importErrorArea').style.display = 'none';

    fetch('{{ route("admin.prices.import.preview") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById('importLoading').style.display = 'none';

        if (!res.success) {
            document.getElementById('importErrorArea').style.display = 'block';
            const list = document.getElementById('importErrorList');
            list.innerHTML = '';
            (res.errors || []).forEach(e => {
                const li = document.createElement('li');
                li.className = 'list-group-item text-danger';
                li.textContent = e;
                list.appendChild(li);
            });
            if (res.preview) renderImportPreview(res);
            return;
        }

        renderImportPreview(res);
        const btn = document.getElementById('confirmImportBtn');
        if (res.matched > 0 || res.created > 0) btn.classList.remove('d-none'); else btn.classList.add('d-none');
    })
    .catch(err => {
        document.getElementById('importLoading').style.display = 'none';
        document.getElementById('importErrorArea').style.display = 'block';
        document.getElementById('importErrorList').innerHTML =
            '<li class="list-group-item text-danger">Error: ' + err.message + '</li>';
    });
}

function renderImportPreview(res) {
    document.getElementById('importPreviewArea').style.display = 'block';
    const tbody = document.getElementById('importPreviewBody');
    tbody.innerHTML = '';

    const total = res.total_rows || 0;
    const matched = res.matched || 0;
    const created = res.created || 0;
    const notFound = res.not_found || 0;

    document.getElementById('importSummary').innerHTML =
        'Total: <strong>' + total + '</strong> | ' +
        'Matched: <strong class="text-success">' + matched + '</strong> | ' +
        'New: <strong class="text-primary">' + created + '</strong> | ' +
        'Not Found: <strong class="text-warning">' + notFound + '</strong>';

    (res.preview || []).forEach((row, idx) => {
        const tr = document.createElement('tr');
        let badge = '';
            if (row.status === 'ok') badge = '<span class="badge bg-success">Ready</span>';
            else if (row.status === 'new') badge = '<span class="badge bg-primary">New Item</span>';
            else if (row.status === 'not_found') badge = '<span class="badge bg-warning text-dark">Not Found</span>';
            else badge = '<span class="badge bg-danger">Error</span>';

            const oldPrice = row.old_price != null ? parseFloat(row.old_price).toFixed(2) : (row.status === 'new' ? '—' : '—');
            const newPrice = row.price != null ? parseFloat(row.price).toFixed(2) : '—';

            tr.innerHTML =
                '<td>' + row.row + '</td>' +
                '<td>' + (row.name || '') + '</td>' +
                '<td>' + (row.category_slug || '—') + '</td>' +
                '<td>' + oldPrice + '</td>' +
                '<td style="font-weight:600;">' + newPrice + '</td>' +
                '<td>' + badge + (row.message ? '<br><small class="text-danger">' + row.message + '</small>' : '') + '</td>';

            if (row.status === 'ok') tr.style.background = '#F1F8E9';
            else if (row.status === 'new') tr.style.background = '#E3F2FD';
            else if (row.status === 'not_found') tr.style.background = '#FFF8E1';

        tbody.appendChild(tr);
    });
}

function confirmImport() {
    if (!confirm('Are you sure you want to update prices for all matched items?')) return;

    const form = document.getElementById('importForm');
    const formData = new FormData(form);

    document.getElementById('importLoading').style.display = 'block';
    document.getElementById('importPreviewArea').style.display = 'none';
    document.getElementById('confirmImportBtn').classList.add('d-none');

    fetch('{{ route("admin.prices.import.process") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById('importLoading').style.display = 'none';

        if (res.success) {
            let msg = '<span class="text-success fw-semibold">Import completed: ' +
                (res.updated || 0) + ' updated, ' +
                (res.created || 0) + ' created, ' +
                (res.skipped || 0) + ' skipped.</span>';
            document.getElementById('importSummary').innerHTML = msg;

            if (res.warnings && res.warnings.length > 0) {
                let warnHtml = '<div class="mt-2"><ul class="list-unstyled mb-0" style="font-size:12px;">';
                res.warnings.forEach(w => {
                    warnHtml += '<li class="text-warning">⚠️ ' + w + '</li>';
                });
                warnHtml += '</ul></div>';
                document.getElementById('importSummary').insertAdjacentHTML('afterend', warnHtml);
            }
            location.reload();
        } else {
            document.getElementById('importErrorArea').style.display = 'block';
            const list = document.getElementById('importErrorList');
            list.innerHTML = '';
            (res.errors || []).forEach(e => {
                const li = document.createElement('li');
                li.className = 'list-group-item text-danger';
                li.textContent = e;
                list.appendChild(li);
            });
        }
    })
    .catch(err => {
        document.getElementById('importLoading').style.display = 'none';
        document.getElementById('importErrorArea').style.display = 'block';
        document.getElementById('importErrorList').innerHTML =
            '<li class="list-group-item text-danger">Error: ' + err.message + '</li>';
    });
}
</script>
@endpush
