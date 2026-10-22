@extends('layouts.app')
@section('title', 'Recycle Bin')
@section('page-title', 'Recycle Bin / کوڑے دان')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-trash-fill me-2" style="color:#1A5C38;"></i>Deleted Records <span class="badge bg-secondary ms-2">{{ count($trashed) }}</span></h6>
        <small class="text-muted">Soft-deleted records can be restored or permanently deleted</small>
    </div>
    <div class="card-body p-0">
        @if(count($trashed) === 0)
            <div class="text-center py-5">
                <i class="bi bi-emoji-smile" style="font-size:48px;color:#ccc;"></i>
                <p class="text-muted mt-2 mb-0">No deleted records found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="trashTable">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name / ID</th>
                            <th>Deleted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($trashed as $item)
                        <tr id="trash-row-{{ $item['type'] }}-{{ $item['id'] }}">
                            <td>
                                <span class="badge"
                                    style="background:
                                        {{ match($item['type']) {
                                            'category'     => '#E8F5E9',
                                            'item'         => '#E3F2FD',
                                            'complaint'    => '#FFF8E1',
                                            'user'         => '#F3E5F5',
                                            default        => '#f0f0f0',
                                        } }};
                                        color:
                                        {{ match($item['type']) {
                                            'category'     => '#1B5E20',
                                            'item'         => '#1565C0',
                                            'complaint'    => '#F57F17',
                                            'user'         => '#7B1FA2',
                                            default        => '#333',
                                        } }};">
                                    {{ $item['type_label'] }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $item['name'] }}</td>
                            <td style="font-size:12px;color:#888;">{{ $item['deleted_at'] }}</td>
                            <td>
                                @if(Auth::user()->isAdmin())
                                <button class="btn btn-sm btn-success me-1" onclick="restoreRecord('{{ $item['type'] }}', {{ $item['id'] }})">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="forceDeleteRecord('{{ $item['type'] }}', {{ $item['id'] }})">
                                    <i class="bi bi-trash"></i> Delete Forever
                                </button>
                                @else
                                <span class="text-muted" style="font-size:12px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function restoreRecord(type, id) {
    if (!confirm('Restore this record?')) return;
    $.post('/admin/trash/restore/' + type + '/' + id, function(r) {
        if (r.success) {
            document.getElementById('trash-row-' + type + '-' + id).remove();
            toastr.success(r.message);
            location.reload();
        }
    }).fail(x => toastr.error(x.responseJSON?.message || 'Error'));
}

function forceDeleteRecord(type, id) {
    if (!confirm('Permanently delete this record? This CANNOT be undone.')) return;
    $.post('/admin/trash/force-delete/' + type + '/' + id, function(r) {
        if (r.success) {
            document.getElementById('trash-row-' + type + '-' + id).remove();
            toastr.success(r.message);
            location.reload();
        }
    }).fail(x => toastr.error(x.responseJSON?.message || 'Error'));
}
</script>
@endpush
