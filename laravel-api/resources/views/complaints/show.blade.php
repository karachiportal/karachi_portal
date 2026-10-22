@extends('layouts.app')
@section('title', 'Complaint Detail')
@section('page-title', 'Complaint Detail — ' . $complaint->complaint_number)

@section('content')

<div class="row g-3">
    {{-- Left column: details --}}
    <div class="col-lg-7">

        {{-- Header card --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1" style="color:#1A5C38;">
                            {{ $complaint->complaint_number }}
                        </h5>
                        <div class="text-muted" style="font-size:12px;">
                            <i class="bi bi-calendar3 me-1"></i>
                            Submitted: {{ $complaint->created_at->format('d M Y, h:i A') }}
                        </div>
                    </div>
                    <span class="badge badge-status badge-{{ $complaint->status }} fs-6">
                        {{ ucfirst(str_replace('_',' ',$complaint->status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Complainant info --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-person-fill me-2" style="color:#1A5C38;"></i>Complainant Details</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="ps-3 text-muted" style="width:150px;font-size:12.5px;">Name / نام</td>
                        <td class="fw-semibold">{{ $complaint->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12.5px;">CNIC / شناخت کارڈ</td>
                        <td>{{ $complaint->cnic }}</td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12.5px;">Mobile / موبائل</td>
                        <td>
                            <a href="tel:{{ $complaint->mobile }}" class="text-decoration-none" style="color:#1A5C38;">
                                <i class="bi bi-telephone me-1"></i>{{ $complaint->mobile }}
                            </a>
                        </td>
                    </tr>
                    @if($complaint->user)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12.5px;">Registered User</td>
                        <td>
                            <a href="{{ route('admin.users.show', $complaint->user_id) }}" style="color:#1A5C38;">
                                {{ $complaint->user->username }}
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Complaint info --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-exclamation-circle-fill me-2" style="color:#E65100;"></i>Complaint Details</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="ps-3 text-muted" style="width:150px;font-size:12.5px;">Item / شے</td>
                        <td class="fw-semibold">{{ $complaint->item_name }}</td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12.5px;">Shop / دکان</td>
                        <td>{{ $complaint->shop_name }}</td>
                    </tr>
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12.5px;">Location / مقام</td>
                        <td>
                            {{ $complaint->location_address }}
                            @if($complaint->latitude && $complaint->longitude)
                                <br>
                                <a href="https://maps.google.com/?q={{ $complaint->latitude }},{{ $complaint->longitude }}"
                                   target="_blank" class="btn btn-sm btn-outline-success mt-1" style="font-size:11px;">
                                    <i class="bi bi-geo-alt me-1"></i>View on Google Maps
                                </a>
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="px-3 pb-3">
                    <div class="text-muted mb-1" style="font-size:12px;font-weight:600;">Description / تفصیل</div>
                    <div class="p-3 rounded" style="background:#f8f9fa;font-size:13.5px;line-height:1.7;">
                        {{ $complaint->details }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Pictures --}}
        @if($complaint->pictures->count())
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-images me-2" style="color:#1A5C38;"></i>
                    Pictures ({{ $complaint->pictures->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($complaint->pictures as $pic)
                        <a href="{{ $pic->url }}" target="_blank">
                            <img src="{{ $pic->url }}" class="img-thumb"
                                 onerror="this.src='https://via.placeholder.com/60?text=IMG'">
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Right column: actions --}}
    <div class="col-lg-5">

        {{-- Update Status --}}
        @if(Auth::user()->isAdmin())
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-arrow-repeat me-2" style="color:#1A5C38;"></i>Update Status</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.complaints.update-status', $complaint->id) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">New Status</label>
                        <div class="d-grid gap-2">
                            @foreach(['pending'=>['#FFF8E1','#F57F17'],'in_progress'=>['#E3F2FD','#1565C0'],'resolved'=>['#E8F5E9','#1B5E20'],'rejected'=>['#FFEBEE','#C62828']] as $s => $colors)
                                <label class="d-flex align-items-center gap-2 p-2 rounded" style="cursor:pointer;border:2px solid {{ $complaint->status === $s ? $colors[1] : '#eee' }};background:{{ $complaint->status === $s ? $colors[0] : '#fff' }};">
                                    <input type="radio" name="status" value="{{ $s }}"
                                           {{ $complaint->status === $s ? 'checked' : '' }}
                                           onchange="this.closest('form').querySelectorAll('label').forEach(l=>l.style.borderColor='#eee'); this.closest('label').style.borderColor='{{ $colors[1] }}'">
                                    <span class="badge badge-status badge-{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Admin Remarks (Optional)
                        </label>
                        <textarea name="admin_remarks" class="form-control" rows="3"
                                  placeholder="Write remarks for citizen..."
                                  style="font-size:13px;">{{ $complaint->admin_remarks }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-green w-100">
                        <i class="bi bi-check-circle me-2"></i>Update Status
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Map preview --}}
        @if($complaint->latitude && $complaint->longitude)
        <div class="card mb-3">
            <div class="card-header">
                <h6><i class="bi bi-geo-alt-fill me-2" style="color:#E65100;"></i>Location</h6>
            </div>
            <div class="card-body p-0 overflow-hidden" style="border-radius:0 0 14px 14px;">
                <iframe
                    src="https://maps.google.com/maps?q={{ $complaint->latitude }},{{ $complaint->longitude }}&zoom=15&output=embed"
                    width="100%" height="200" style="border:0;" loading="lazy">
                </iframe>
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header">
                <h6><i class="bi bi-clock-history me-2" style="color:#1A5C38;"></i>Timeline</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 mb-3">
                    <div style="width:10px;height:10px;background:#1A5C38;border-radius:50%;margin-top:4px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Complaint Filed</div>
                        <div class="text-muted" style="font-size:11px;">{{ $complaint->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                @if($complaint->status !== 'pending')
                <div class="d-flex gap-3 mb-3">
                    <div style="width:10px;height:10px;background:#1565C0;border-radius:50%;margin-top:4px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:13px;font-weight:600;">Status Updated</div>
                        <div class="text-muted" style="font-size:11px;">{{ $complaint->updated_at->format('d M Y, h:i A') }}</div>
                        <span class="badge badge-status badge-{{ $complaint->status }} mt-1">
                            {{ ucfirst(str_replace('_',' ',$complaint->status)) }}
                        </span>
                    </div>
                </div>
                @endif
                <div class="d-flex gap-3">
                    <div style="width:10px;height:10px;background:#ccc;border-radius:50%;margin-top:4px;flex-shrink:0;"></div>
                    <div class="text-muted" style="font-size:12px;">Awaiting resolution...</div>
                </div>
            </div>
        </div>

        {{-- Back button --}}
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary w-100 mt-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Complaints
        </a>
    </div>
</div>

@endsection
