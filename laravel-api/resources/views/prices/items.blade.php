@extends('layouts.app')
@section('title', 'Price Items')
@section('page-title', 'Price Items / قیمت اشیاء')

@section('content')

{{-- Filter bar --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span style="font-size:13px;font-weight:600;color:#555;">Filter by Category:</span>
            <a href="{{ route('admin.prices.items') }}"
               class="btn btn-sm {{ !request('cat') ? 'btn-green' : 'btn-outline-secondary' }}">
                All ({{ $totalItems }})
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('admin.prices.items', ['cat' => $cat->slug]) }}"
                   class="btn btn-sm {{ request('cat') == $cat->slug ? 'btn-green' : 'btn-outline-secondary' }}">
                    @include('partials.cat-icon', ['icon' => $cat->icon, 'size' => 16]) {{ $cat->name }}
                    <span class="ms-1 badge {{ request('cat') == $cat->slug ? 'bg-white text-success' : 'bg-secondary' }}"
                          style="font-size:9px;">{{ $cat->items_count }}</span>
                </a>
            @endforeach

            <div class="ms-auto d-flex gap-2">
                @if(Auth::user()->isAdmin())
                <button class="btn btn-sm btn-green" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-circle me-1"></i>Add Item
                </button>
                <a href="{{ route('admin.prices.bulk') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-pencil-square me-1"></i>Bulk Update
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Items table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-list-ul me-2" style="color:#1A5C38;"></i>
            {{ $items->total() }} Item(s)
            @if(request('cat')) — {{ request('cat') }} @endif
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted" style="font-size:12px;">
                <i class="bi bi-clock me-1"></i>Last updated: {{ $lastUpdated }}
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:40px;">#</th>
                        <th>Item Name</th>
                        <th>Urdu Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Current Price (Rs.)</th>
                        <th>Change</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr id="item-row-{{ $item->id }}">
                        <td class="ps-3 text-muted" style="font-size:12px;">{{ $item->id }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:13.5px;">{{ $item->name }}</div>
                        </td>
                        <td class="urdu text-muted">{{ $item->name_urdu }}</td>
                        <td>
                            <span class="badge" style="background:#E8F5E9;color:#1A5C38;font-size:11px;">
                                @include('partials.cat-icon', ['icon' => $item->category->icon ?? '📦', 'size' => 13]) {{ $item->category->name ?? '—' }}
                            </span>
                        </td>
                        <td style="font-size:13px;">{{ $item->unit }}</td>
                        <td>
                            @if(Auth::user()->isAdmin())
                            {{-- Inline editable price --}}
                            <span class="price-editable" id="price-display-{{ $item->id }}"
                                  onclick="startEdit({{ $item->id }}, {{ $item->price }})">
                                {{ number_format($item->price, 2) }}
                            </span>
                            <div id="price-edit-{{ $item->id }}" class="d-none d-flex align-items-center gap-1">
                                <input type="number" class="price-input"
                                       id="price-input-{{ $item->id }}"
                                       value="{{ $item->price }}"
                                       step="0.5" min="0">
                                <button class="btn btn-sm btn-green px-2"
                                        onclick="savePrice({{ $item->id }})">
                                    <i class="bi bi-check"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary px-2"
                                        onclick="cancelEdit({{ $item->id }}, {{ $item->price }})">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @else
                            <span class="fw-semibold">{{ number_format($item->price, 2) }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $change = $item->price_change;
                                $pct    = $item->change_percent;
                            @endphp
                            @if($change > 0)
                                <span class="price-up fw-semibold" style="font-size:12px;">
                                    <i class="bi bi-arrow-up-short"></i>+{{ number_format($change,2) }}
                                    <span style="font-size:10px;">({{ number_format($pct,2) }}%)</span>
                                </span>
                            @elseif($change < 0)
                                <span class="price-down fw-semibold" style="font-size:12px;">
                                    <i class="bi bi-arrow-down-short"></i>{{ number_format($change,2) }}
                                    <span style="font-size:10px;">({{ number_format($pct,2) }}%)</span>
                                </span>
                            @else
                                <span class="price-same" style="font-size:12px;">— 0.00%</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="badge badge-status badge-resolved">Active</span>
                            @else
                                <span class="badge badge-status badge-rejected">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if(Auth::user()->isAdmin())
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick="editItem({{ $item->id }}, {{ json_encode($item) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:12px;">
            Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}
        </span>
        {{ $items->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- ── ADD ITEM MODAL ── --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#1A5C38;color:white;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Price Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addItemForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Category *</label>
                            <select name="price_category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    @php $__ic = $cat->icon; if(\Illuminate\Support\Str::startsWith($__ic ?? '', ['http://', 'https://'])) $__ic = '🖼️'; @endphp
                                    <option value="{{ $cat->id }}">{{ $__ic ?? '📦' }} {{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Item Name (English) *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Rice (Fine)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Item Name (Urdu)</label>
                            <input type="text" name="name_urdu" class="form-control urdu" placeholder="چاول (باریک)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Unit (English) *</label>
                            <input type="text" name="unit" class="form-control" value="1 Kg" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Unit (Urdu)</label>
                            <input type="text" name="unit_urdu" class="form-control urdu" value="1 کلوگرام">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Current Price (Rs.) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="price" class="form-control" required step="0.5" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-green">
                        <i class="bi bi-plus-circle me-1"></i>Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── EDIT ITEM MODAL ── --}}
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#1565C0;color:white;">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Price Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm">
                @csrf
                <input type="hidden" id="editItemId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Category</label>
                            <select name="price_category_id" id="editItemCat" class="form-select">
                                @foreach($categories as $cat)
                                    @php $__ic = $cat->icon; if(\Illuminate\Support\Str::startsWith($__ic ?? '', ['http://', 'https://'])) $__ic = '🖼️'; @endphp
                                    <option value="{{ $cat->id }}">{{ $__ic ?? '📦' }} {{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Name (English)</label>
                            <input type="text" name="name" id="editItemName" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Name (Urdu)</label>
                            <input type="text" name="name_urdu" id="editItemUrdu" class="form-control urdu">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Unit</label>
                            <input type="text" name="unit" id="editItemUnit" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Unit (Urdu)</label>
                            <input type="text" name="unit_urdu" id="editItemUnitUrdu" class="form-control urdu">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Price (Rs.)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="price" id="editItemPrice" class="form-control" step="0.5" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Sort Order</label>
                            <input type="number" name="sort_order" id="editItemOrder" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:13px;">Status</label>
                            <select name="is_active" id="editItemActive" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#1565C0;color:white;">
                        <i class="bi bi-check-circle me-1"></i>Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Inline price edit ──
function startEdit(id, price) {
    document.getElementById(`price-display-${id}`).classList.add('d-none');
    document.getElementById(`price-edit-${id}`).classList.remove('d-none');
    document.getElementById(`price-input-${id}`).focus();
}

function cancelEdit(id, price) {
    document.getElementById(`price-display-${id}`).classList.remove('d-none');
    document.getElementById(`price-edit-${id}`).classList.add('d-none');
}

function savePrice(id) {
    const newPrice = document.getElementById(`price-input-${id}`).value;
    $.ajax({
        url: `/admin/prices/items/${id}`,
        method: 'POST',
        data: { _method: 'PUT', _token: $('meta[name=csrf-token]').attr('content'), price: newPrice },
        success: function(r) {
            if (r.success) {
                document.getElementById(`price-display-${id}`).textContent = parseFloat(newPrice).toFixed(2);
                cancelEdit(id, newPrice);
                toastr.success(`Price updated to Rs. ${newPrice}`);
                // Update change badge
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: x => toastr.error('Failed to update price')
    });
}

// Enter key on price input
document.querySelectorAll('.price-input').forEach(inp => {
    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const id = this.id.replace('price-input-', '');
            savePrice(id);
        }
        if (e.key === 'Escape') {
            const id = this.id.replace('price-input-', '');
            cancelEdit(id, this.defaultValue);
        }
    });
});

// ── Edit item modal ──
function editItem(id, item) {
    document.getElementById('editItemId').value = id;
    document.getElementById('editItemCat').value = item.price_category_id;
    document.getElementById('editItemName').value = item.name;
    document.getElementById('editItemUrdu').value = item.name_urdu || '';
    document.getElementById('editItemUnit').value = item.unit;
    document.getElementById('editItemUnitUrdu').value = item.unit_urdu || '';
    document.getElementById('editItemPrice').value = item.price;
    document.getElementById('editItemOrder').value = item.sort_order;
    document.getElementById('editItemActive').value = item.is_active ? 1 : 0;
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function deleteItem(id, name) {
    if (!confirm(`Delete "${name}"? This will also delete its price history.`)) return;
    $.ajax({
        url: `/admin/prices/items/${id}`,
        method: 'POST',
        data: { _method: 'DELETE', _token: $('meta[name=csrf-token]').attr('content') },
        success: function(r) {
            if (r.success) {
                document.getElementById(`item-row-${id}`).remove();
                toastr.success('Item deleted');
            }
        },
        error: x => toastr.error('Failed to delete')
    });
}

// ── Add item form ──
$('#addItemForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/admin/prices/items', $(this).serialize(), function(r) {
        if (r.success) { toastr.success('Item added'); location.reload(); }
    }).fail(x => toastr.error(x.responseJSON?.message || 'Error'));
});

// ── Edit item form ──
$('#editItemForm').on('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editItemId').value;
    $.ajax({
        url: `/admin/prices/items/${id}`,
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        success: r => { if (r.success) { toastr.success('Updated'); location.reload(); } },
        error: x => toastr.error(x.responseJSON?.message || 'Error')
    });
});
</script>
@endpush
