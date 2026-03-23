<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PSAU Security') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style (AdminLTE v3) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        .map-wrapper { height: 600px; width: 100%; border-radius: 4px; box-shadow: inset 0 0 10px rgba(0,0,0,0.1); }
        .sidebar-dark-maroon { background-color: #7b1113; }
        .sidebar-dark-maroon .nav-sidebar > .nav-item > .nav-link.active { background-color: rgba(255,255,255,.1); color: #fff; }
        .sidebar-dark-maroon .nav-sidebar > .nav-item > .nav-link { color: #c2c7d0; }
        .sidebar-dark-maroon .brand-link { color: #fff; border-bottom: 1px solid #941719; }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
            </li>
        </ul>
        
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" style="position:relative">
                    <i class="far fa-bell" style="font-size:1.2rem"></i>
                    @if(Auth::user() && Auth::user()->unreadNotifications->count() > 0)
                        <span class="badge badge-danger navbar-badge" style="position:absolute;top:2px;right:2px;font-size:0.6rem;padding:2px 4px;border-radius:50%">
                            {{ Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-lg border-0" style="min-width:320px;border-radius:8px;padding:0">
                    <div style="background:#f8f9fa;padding:12px 16px;font-weight:700;border-bottom:1px solid #dee2e6;border-radius:8px 8px 0 0">
                        <i class="fas fa-bell mr-2 text-primary"></i>Notifications
                    </div>
                    <div style="max-height:350px;overflow-y:auto">
                        @if(Auth::user())
                            @forelse(Auth::user()->notifications->take(10) as $notification)
                                <a href="#" class="dropdown-item py-3 border-bottom" style="white-space:normal;line-height:1.4;background:{{ $notification->read_at ? '#fff' : '#f0fdf4' }}">
                                    @if($notification->data['status'] === 'approved')
                                        <i class="fas fa-check-circle text-success mr-2 mt-1 float-left" style="font-size:1.2rem"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger mr-2 mt-1 float-left" style="font-size:1.2rem"></i>
                                    @endif
                                    <div style="overflow:hidden">
                                        <div class="text-sm font-weight-bold" style="color:#111827">Application {{ ucfirst($notification->data['status'] ?? '') }}</div>
                                        <div class="text-xs text-muted mt-1">{{ mb_strimwidth($notification->data['message'] ?? '', 0, 80, "...") }}</div>
                                        <div class="text-xs mt-1" style="color:#9ca3af"><i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>
                                @php $notification->markAsRead(); @endphp
                            @empty
                                <div class="dropdown-item text-center text-muted py-4">
                                    <i class="fas fa-bell-slash fa-2x mb-2" style="opacity:0.3"></i><br>
                                    <span class="text-sm">You have no new notifications</span>
                                </div>
                            @endforelse
                        @endif
                    </div>
                </div>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> {{ Auth::user()->name ?? 'Account' }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-maroon elevation-4">
        <!-- Brand Logo -->
        <a href="/" class="brand-link flex items-center">
            <span class="brand-text font-weight-bold ml-3">PSAU CMS</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block text-white font-weight-bold">{{ Auth::user()->name ?? 'User' }}</a>
                    <span class="text-sm text-light"><i class="fas fa-circle text-success text-xs mr-1"></i> Online</span>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                    @if(Auth::user() && Auth::user()->role === 'admin')
                        <li class="nav-header">ADMINISTRATION</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Registration Queue</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.approved.index') }}" class="nav-link {{ request()->routeIs('admin.approved.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-qrcode"></i>
                                <p>Approved &amp; QR Stickers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.sanctions.index') }}" class="nav-link {{ request()->routeIs('admin.sanctions.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gavel"></i>
                                <p>Violations &amp; Sanctions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>User Management</p>
                            </a>
                        </li>
                    @endif

                    @if(Auth::user() && Auth::user()->role === 'security')
                        <li class="nav-header">SECURITY TASKS</li>
                        <li class="nav-item">
                            <a href="{{ route('security.dashboard') }}" class="nav-link {{ request()->routeIs('security.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Enforcement Panel</p>
                            </a>
                        </li>
                    @endif

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Validation Errors / Session Status -->
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('status') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} PSAU Security Information Management System.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@yield('scripts')
</body>
</html>
