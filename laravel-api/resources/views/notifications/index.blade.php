{{-- notifications/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications / اطلاعات')

@section('content')
<div class="row g-3">
@if(Auth::user()->isAdmin())
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header" style="background:#1A5C38;color:white;border-radius:14px 14px 0 0;">
                <h6 class="mb-0 text-white"><i class="bi bi-broadcast me-2"></i>Broadcast Notification</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.notifications.broadcast') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Type</label>
                        <select name="type" class="form-select">
                            <option value="announcement">Announcement</option>
                            <option value="price_alert">Price Alert</option>
                            <option value="system">System</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Title *</label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="Notification title">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:13px;">Message *</label>
                        <textarea name="body" class="form-control" rows="4" required
                                  placeholder="Write your message here..." maxlength="500"></textarea>
                        <div class="text-muted mt-1" style="font-size:11px;">Max 500 characters</div>
                    </div>
                    <div class="p-3 rounded mb-3" style="background:#FFF8E1;border:1px solid #F9A825;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill" style="color:#F9A825;"></i>
                            <div style="font-size:12.5px;">
                                This will send notification to <strong>all {{ $totalCitizens }} citizens</strong>.
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-green w-100"
                            onclick="return confirm('Send to all {{ $totalCitizens }} citizens?')">
                        <i class="bi bi-send-fill me-2"></i>Send to All Citizens
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h6><i class="bi bi-clock-history me-2" style="color:#1A5C38;"></i>Recent Notifications Sent</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th class="ps-3">Title</th>
                        <th>Type</th>
                        <th>Recipients</th>
                        <th>Date</th>
                    </tr></thead>
                    <tbody>
                    @forelse($recentBroadcasts as $b)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold" style="font-size:13px;">{{ $b->title }}</div>
                                <div class="text-muted" style="font-size:11.5px;">{{ Str::limit($b->body, 60) }}</div>
                            </td>
                            <td><span class="badge bg-secondary">{{ $b->type }}</span></td>
                            <td style="font-size:13px;">{{ $b->recipient_count ?? 'All' }}</td>
                            <td class="text-muted" style="font-size:12px;">{{ $b->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No broadcasts yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
