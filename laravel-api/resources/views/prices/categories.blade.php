@extends('layouts.app')
@section('title', 'Price Categories')
@section('page-title', 'Price Categories / قیمت کی اقسام')

@section('content')

<div class="row g-3">
    {{-- Category list --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="bi bi-tags-fill me-2" style="color:#1A5C38;"></i>All Categories</h6>
                @if(Auth::user()->isAdmin())
                <button class="btn btn-sm btn-green" data-bs-toggle="modal" data-bs-target="#addCatModal">
                    <i class="bi bi-plus-circle me-1"></i>Add Category
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="catTable">
                    <thead>
                        <tr>
                            <th class="ps-3">Icon</th>
                            <th>Name</th>
                            <th>Urdu Name</th>
                            <th>Slug</th>
                            <th>Items</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $cat)
                        <tr id="cat-row-{{ $cat->id }}">
                            <td class="ps-3">
                                @php $isImg = $cat->icon && (\Str::startsWith($cat->icon, 'http://') || \Str::startsWith($cat->icon, 'https://')); @endphp
                                @if($isImg)
                                    <img src="{{ $cat->icon }}" style="width:28px;height:28px;border-radius:4px;" alt="icon">
                                @else
                                    <span style="font-size:22px;">{{ $cat->icon ?? '📦' }}</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $cat->name }}</td>
                            <td class="urdu">{{ $cat->name_urdu }}</td>
                            <td><code style="font-size:11px;color:#1A5C38;">{{ $cat->slug }}</code></td>
                            <td>
                                <span class="badge" style="background:#E8F5E9;color:#1A5C38;">
                                    {{ $cat->items_count }} items
                                </span>
                            </td>
                            <td>{{ $cat->sort_order }}</td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge badge-status badge-resolved">Active</span>
                                @else
                                    <span class="badge badge-status badge-rejected">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if(Auth::user()->isAdmin())
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick='editCategory({{ $cat->id }}, {!! json_encode($cat->name, JSON_HEX_APOS) !!}, {!! json_encode($cat->name_urdu, JSON_HEX_APOS) !!}, {!! json_encode($cat->icon, JSON_HEX_APOS) !!}, {{ $cat->sort_order }}, {{ $cat->is_active ? 1 : 0 }})'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($cat->items_count == 0)
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick='deleteCategory({{ $cat->id }}, {!! json_encode($cat->name, JSON_HEX_APOS) !!})'>
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick guide --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6><i class="bi bi-info-circle me-2" style="color:#1A5C38;"></i>Guide</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#1A5C38;"></i>Categories group price items</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#1A5C38;"></i>Slug is used in API (cannot change after creation)</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#1A5C38;"></i>Sort Order controls display sequence</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#1A5C38;"></i>Categories with items cannot be deleted</li>
                    <li><i class="bi bi-info-circle me-2" style="color:#1565C0;"></i>Icons: emoji (e.g. 🌾 🥦 🍗) or image URL (e.g. https://...png)</li>
                </ul>
                <hr>
                <a href="{{ route('admin.prices.items') }}" class="btn btn-green w-100 btn-sm">
                    <i class="bi bi-list-ul me-1"></i>Manage Price Items
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── ADD CATEGORY MODAL ── --}}
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1A5C38;color:white;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCatForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Name (English) *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Vegetables">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Name (Urdu)</label>
                            <input type="text" name="name_urdu" class="form-control urdu" placeholder="سبزیاں">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Slug * <small class="text-muted">(no spaces)</small></label>
                            <input type="text" name="slug" class="form-control" required placeholder="vegetables"
                                   pattern="[a-z0-9_]+" title="Lowercase letters, numbers, underscores only">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Icon</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="addIconType" id="addIconEmojiRadio" value="emoji" checked>
                                    <label class="form-check-label" for="addIconEmojiRadio">Emoji</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="addIconType" id="addIconUrlRadio" value="url">
                                    <label class="form-check-label" for="addIconUrlRadio">Image URL</label>
                                </div>
                            </div>
                            <input type="hidden" name="icon" id="addIconValue">
                            <div class="d-flex align-items-center gap-2" id="addIconEmojiGroup">
                                <input type="text" id="addIconEmoji" class="form-control text-center"
                                       placeholder="🥦" style="font-size:20px;width:80px;" maxlength="4">
                                <span id="addIconEmojiPreview" style="font-size:26px;line-height:1;">🥦</span>
                            </div>
                            <div class="d-flex align-items-center gap-2" id="addIconUrlGroup" style="display:none;">
                                <input type="text" id="addIconUrl" class="form-control" placeholder="https://example.com/icon.png" style="font-size:13px;">
                                <span id="addIconUrlPreview"></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-green">
                        <i class="bi bi-plus-circle me-1"></i>Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── EDIT CATEGORY MODAL ── --}}
<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1565C0;color:white;">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCatForm">
                @csrf @method('PUT')
                <input type="hidden" id="editCatId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Name (English)</label>
                            <input type="text" name="name" id="editCatName" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Name (Urdu)</label>
                            <input type="text" name="name_urdu" id="editCatUrdu" class="form-control urdu">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Icon</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="editIconType" id="editIconEmojiRadio" value="emoji" checked>
                                    <label class="form-check-label" for="editIconEmojiRadio">Emoji</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="editIconType" id="editIconUrlRadio" value="url">
                                    <label class="form-check-label" for="editIconUrlRadio">Image URL</label>
                                </div>
                            </div>
                            <input type="hidden" name="icon" id="editIconValue">
                            <div class="d-flex align-items-center gap-2" id="editIconEmojiGroup">
                                <input type="text" id="editIconEmoji" class="form-control text-center"
                                       style="font-size:20px;width:80px;" maxlength="4" placeholder="🥦">
                                <span id="editIconEmojiPreview" style="font-size:26px;line-height:1;"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2" id="editIconUrlGroup" style="display:none;">
                                <input type="text" id="editIconUrl" class="form-control" placeholder="https://example.com/icon.png" style="font-size:13px;">
                                <span id="editIconUrlPreview"></span>
                            </div>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-semibold" style="font-size:13px;">Sort Order</label>
                            <input type="number" name="sort_order" id="editCatOrder" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Status</label>
                            <select name="is_active" id="editCatActive" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#1565C0;color:white;">
                        <i class="bi bi-check-circle me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─── Helpers ──────────────────────────────────────
function syncAddIcon() {
    const isEmoji = document.querySelector('input[name="addIconType"]:checked').value === 'emoji';
    if (isEmoji) {
        const v = document.getElementById('addIconEmoji').value;
        document.getElementById('addIconValue').value = v;
        document.getElementById('addIconEmojiPreview').textContent = v || '🥦';
    } else {
        const v = document.getElementById('addIconUrl').value;
        document.getElementById('addIconValue').value = v;
        document.getElementById('addIconUrlPreview').innerHTML = v
            ? '<img src="'+v+'" style="width:28px;height:28px;border-radius:4px;">'
            : '';
    }
}
function syncEditIcon() {
    const isEmoji = document.querySelector('input[name="editIconType"]:checked').value === 'emoji';
    if (isEmoji) {
        const v = document.getElementById('editIconEmoji').value;
        document.getElementById('editIconValue').value = v;
        document.getElementById('editIconEmojiPreview').textContent = v || '📦';
    } else {
        const v = document.getElementById('editIconUrl').value;
        document.getElementById('editIconValue').value = v;
        document.getElementById('editIconUrlPreview').innerHTML = v
            ? '<img src="'+v+'" style="width:28px;height:28px;border-radius:4px;">'
            : '';
    }
}
function toggleIconType(prefix, type) {
    const emojiGroup = document.getElementById(prefix + 'IconEmojiGroup');
    const urlGroup   = document.getElementById(prefix + 'IconUrlGroup');
    const emojiInput = document.getElementById(prefix + 'IconEmoji');
    const urlInput   = document.getElementById(prefix + 'IconUrl');
    if (type === 'emoji') {
        emojiGroup.style.display = '';
        urlGroup.style.display   = 'none';
        urlInput.value = '';
        syncEditIcon(); // update hidden + preview
    } else {
        emojiGroup.style.display = 'none';
        urlGroup.style.display   = '';
        emojiInput.value = '';
        syncEditIcon();
    }
}

// ─── Edit Category ────────────────────────────────
function editCategory(id, name, urdu, icon, order, active) {
    document.getElementById('editCatId').value = id;
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatUrdu').value = urdu;
    document.getElementById('editCatOrder').value = order;
    document.getElementById('editCatActive').value = active;

    const isUrl = icon && (icon.startsWith('http://') || icon.startsWith('https://'));
    if (isUrl) {
        document.querySelector('input[name="editIconType"][value="url"]').checked = true;
        document.getElementById('editIconEmojiGroup').style.display = 'none';
        document.getElementById('editIconUrlGroup').style.display   = '';
        document.getElementById('editIconEmoji').value = '';
        document.getElementById('editIconUrl').value   = icon;
    } else {
        document.querySelector('input[name="editIconType"][value="emoji"]').checked = true;
        document.getElementById('editIconEmojiGroup').style.display = '';
        document.getElementById('editIconUrlGroup').style.display   = 'none';
        document.getElementById('editIconUrl').value = '';
        document.getElementById('editIconEmoji').value = icon || '';
    }
    syncEditIcon();
    new bootstrap.Modal(document.getElementById('editCatModal')).show();
}

function deleteCategory(id, name) {
    if (!confirm(`Delete category "${name}"? This cannot be undone.`)) return;
    $.ajax({
        url: `/admin/prices/categories/${id}`,
        method: 'DELETE',
        success: function(r) {
            if (r.success) {
                document.getElementById(`cat-row-${id}`).remove();
                toastr.success('Category deleted');
            }
        },
        error: function(x) { toastr.error(x.responseJSON?.message || 'Error'); }
    });
}

// ─── Form submits ─────────────────────────────────
$('#addCatForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/admin/prices/categories', $(this).serialize(), function(r) {
        if (r.success) { toastr.success('Category added'); location.reload(); }
    }).fail(x => toastr.error(x.responseJSON?.message || 'Error'));
});

$('#editCatForm').on('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editCatId').value;
    $.ajax({
        url: `/admin/prices/categories/${id}`,
        method: 'POST',
        data: $(this).serialize() + '&_method=PUT',
        success: function(r) {
            if (r.success) { toastr.success('Category updated'); location.reload(); }
        },
        error: x => toastr.error(x.responseJSON?.message || 'Error')
    });
});

// ─── Event listeners ──────────────────────────────
// Add icon radio toggle
$('input[name="addIconType"]').on('change', function() {
    const g = document.getElementById('addIconEmojiGroup');
    const h = document.getElementById('addIconUrlGroup');
    if (this.value === 'emoji') {
        g.style.display = ''; h.style.display = 'none';
        document.getElementById('addIconUrl').value = '';
    } else {
        g.style.display = 'none'; h.style.display = '';
        document.getElementById('addIconEmoji').value = '';
    }
    syncAddIcon();
});
$('#addIconEmoji').on('input', syncAddIcon);
$('#addIconUrl').on('input', syncAddIcon);

// Edit icon radio toggle
$('input[name="editIconType"]').on('change', function() {
    toggleIconType('edit', this.value);
});
$('#editIconEmoji').on('input', syncEditIcon);
$('#editIconUrl').on('input', syncEditIcon);

// Auto-generate slug from name (add form only)
$('#addCatForm input[name="name"]').on('input', function() {
    const slug = $(this).val().toLowerCase().replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'');
    $('#addCatForm input[name="slug"]').val(slug);
});
</script>
@endpush
