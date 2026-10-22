@extends('layouts.app')
@section('title', 'Citizens')
@section('page-title', 'Registered Citizens / رجسٹرڈ شہری')

@section('content')

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search by name, CNIC, mobile..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-green w-100">
                    <i class="bi bi-search me-1"></i>Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
            </div>
            <div class="col-md-3 text-end">
                <span class="text-muted" style="font-size:12px;">
                    Total: <strong>{{ $users->total() }}</strong> citizens
                </span>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6><i class="bi bi-people-fill me-2" style="color:#1A5C38;"></i>Citizens List</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Name</th>
                        <th>CNIC</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Complaints</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12px;">{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:#E8F5E9;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-person-fill" style="color:#1A5C38;font-size:14px;"></i>
                                </div>
                                <div class="fw-semibold" style="font-size:13.5px;">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td style="font-size:13px;letter-spacing:.3px;">{{ $user->cnic }}</td>
                        <td style="font-size:13px;">{{ $user->mobile }}</td>
                        <td style="font-size:13px;">{{ $user->email ?? '—' }}</td>
                        <td><code style="font-size:12px;color:#1A5C38;">{{ $user->username }}</code></td>
                        <td>
                            <a href="{{ route('admin.complaints.index', ['search' => $user->cnic]) }}"
                               class="badge" style="background:#E8F5E9;color:#1A5C38;text-decoration:none;">
                                {{ $user->complaints_count }} complaints
                            </a>
                        </td>
                        <td class="text-muted" style="font-size:12px;">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}"
                               class="btn btn-sm btn-green">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No citizens found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:12px;">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
        </span>
        {{ $users->appends(request()->all())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
