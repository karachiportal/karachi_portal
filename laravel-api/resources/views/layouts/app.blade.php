<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — Commissioner Karachi Portal</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Toastr -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        :root {
            --green-dark:  #0D3D22;
            --green-main:  #1A5C38;
            --green-mid:   #2E7D52;
            --green-light: #E8F5E9;
            --green-pale:  #F0F4F1;
            --sidebar-w:   260px;
        }

        body { background: var(--green-pale); font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--green-dark);
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .25s ease;
        }
        #sidebar .sidebar-brand {
            padding: 10px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        #sidebar .sidebar-brand img { width: 40px; height: 40px; border-radius: 50%; background: white; padding: 4px; }
        #sidebar .sidebar-brand h6 { color: #fff; font-size: 12px; font-weight: 700; letter-spacing: .4px; margin: 0; }
        #sidebar .sidebar-brand small { color: rgba(255,255,255,.6); font-size: 11px; }

        #sidebar .nav-section {
            padding: 6px 16px 2px;
            color: rgba(255,255,255,.4);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 7px 16px;
            border-radius: 8px;
            margin: 1px 10px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all .2s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        #sidebar .nav-link .bi { font-size: 17px; width: 20px; text-align: center; }
        #sidebar .nav-link .badge { margin-left: auto; font-size: 10px; }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,.1);
        }

        /* ── Main content ── */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        #topbar {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        #topbar .topbar-title { font-weight: 700; color: var(--green-main); font-size: 16px; }
        #topbar .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }

        /* ── Page content ── */
        .page-content { padding: 24px; flex: 1; }

        /* ── Cards ── */
        .stat-card {
            border: none;
            border-radius: 14px;
            padding: 20px;
            color: white;
        }
        .stat-card .stat-icon {
            width: 50px; height: 50px;
            background: rgba(255,255,255,.2);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .stat-card .stat-value { font-size: 32px; font-weight: 800; line-height: 1; }
        .stat-card .stat-label { font-size: 13px; opacity: .85; }

        .card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; border-radius: 14px 14px 0 0 !important; padding: 16px 20px; }
        .card-header h6 { margin: 0; font-weight: 700; color: #1a1a1a; }

        /* ── Tables ── */
        .table th { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #888; font-weight: 600; border-top: none; }
        .table td { font-size: 13.5px; vertical-align: middle; }

        /* ── Badges ── */
        .badge-pending    { background: #FFF8E1; color: #F57F17; }
        .badge-in_progress{ background: #E3F2FD; color: #1565C0; }
        .badge-resolved   { background: #E8F5E9; color: #1B5E20; }
        .badge-rejected   { background: #FFEBEE; color: #C62828; }
        .badge-status { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }

        /* ── Price table inline edit ── */
        .price-editable { cursor: pointer; border-bottom: 1px dashed #ccc; min-width: 80px; display: inline-block; }
        .price-editable:hover { background: #fff9c4; border-radius: 4px; }
        .price-input { width: 90px; padding: 2px 6px; border: 1.5px solid var(--green-main); border-radius: 6px; font-size: 13px; }

        /* Price change colors */
        .price-up   { color: #c62828; }
        .price-down { color: #1b5e20; }
        .price-same { color: #888; }

        /* ── Buttons ── */
        .btn-green  { background: var(--green-main); color: #fff; border: none; }
        .btn-green:hover { background: var(--green-mid); color: #fff; }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: var(--green-mid); border-radius: 3px; }

        /* ── Urdu text ── */
        .urdu { font-family: 'Noto Nastaliq Urdu', serif; direction: rtl; }

        /* Image preview */
        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; }
    </style>

    @stack('styles')
</head>
<body>

<!-- ══════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════ -->
<nav id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-2">
        <div style="width:110px;px;height:60px;border-radius:50%;background:#0D3D22;background-image:url('{{ asset("logo.png") }}');background-size:130%;background-position:center;background-repeat:no-repeat;background-blend-mode:luminosity;"></div>
        <div>
            <h6 class="mb-0">COMMISSIONER</h6>
            <small>Karachi Portal Admin</small>
        </div>
    </div>

    <div class="py-1 overflow-auto flex-grow-1">
        <span class="nav-section">Main</span>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <span class="nav-section mt-2">Complaints</span>
        <a href="{{ route('admin.complaints.index') }}"
           class="nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-circle-fill"></i> All Complaints
            @if($pendingCount ?? 0)
                <span class="badge bg-danger rounded-pill">{{ $pendingCount }}</span>
            @endif
        </a>

        <span class="nav-section mt-2">Price Management</span>
        <a href="{{ route('admin.prices.categories') }}"
           class="nav-link {{ request()->routeIs('admin.prices.categories*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill"></i> Categories
        </a>
        <a href="{{ route('admin.prices.items') }}"
           class="nav-link {{ request()->routeIs('admin.prices.items*') ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i> Price Items
        </a>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('admin.prices.bulk') }}"
           class="nav-link {{ request()->routeIs('admin.prices.bulk*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square"></i> Bulk Update / Import
        </a>
        @endif
        <a href="{{ route('admin.price-update-logs.index') }}"
           class="nav-link {{ request()->routeIs('admin.price-update-logs*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Price Update Logs
        </a>

        <span class="nav-section mt-2">Communication</span>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('admin.notifications.index') }}"
           class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell-fill"></i> Notifications
        </a>
        @endif

        <span class="nav-section mt-2">Users</span>
        <a href="{{ route('admin.users.index') }}"
           class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Citizens
        </a>

        <span class="nav-section mt-2">System</span>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('admin.trash.index') }}"
           class="nav-link {{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">
            <i class="bi bi-trash-fill"></i> Recycle Bin / کوڑے دان
        </a>
        @endif

    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:32px;height:32px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-person-fill text-white" style="font-size:16px;"></i>
            </div>
            <div>
                <a href="{{ route('admin.change-password') }}" style="color:#fff;font-size:12.5px;font-weight:600;text-decoration:none;display:block;">{{ auth()->user()->name ?? 'Admin' }}</a>
                <div style="color:rgba(255,255,255,.5);font-size:11px;">Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border:none;">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>
</nav>

<!-- ══════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════ -->
<div id="main-content">
    <!-- Topbar -->
    <div id="topbar">
        <button class="btn btn-sm d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list" style="font-size:20px;"></i>
        </button>
        <span class="topbar-title">@yield('page-title', 'Dashboard')</span>

        <div class="topbar-right">
            <!-- Notification bell -->
            <a href="{{ route('admin.complaints.index', ['status' => 'pending']) }}"
               class="btn btn-sm position-relative"
               style="background:var(--green-light);color:var(--green-main);">
                <i class="bi bi-exclamation-circle"></i>
                @if($pendingCount ?? 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:9px;">{{ $pendingCount }}</span>
                @endif
            </a>

            <span style="color:#888;font-size:12px;">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d M Y') }}
            </span>
        </div>
    </div>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3 mb-0" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3 mb-0" role="alert">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Page content -->
    <div class="page-content">
        @yield('content')
    </div>
</div>

<!-- ══════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script>
    // CSRF setup for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Toastr config
    toastr.options = { positionClass: 'toast-top-right', timeOut: 3000 };

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Auto-dismiss alerts
    setTimeout(() => { $('.alert').alert('close'); }, 4000);
</script>

@stack('scripts')
</body>
</html>
