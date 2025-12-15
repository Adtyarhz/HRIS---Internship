<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'HRIS Panel')</title>

    <!-- Google Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Manrope:wght@400&family=Noto+Sans+Georgian:wght@400&display=swap"
        rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/plugins/fontawesome-free/css/all.min.css">
    <!-- AdminLTE Theme Style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    {{-- Link to your custom CSS file --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- sweetalert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
    @stack('styles')
</head>

<!-- The 'sidebar-mini' class enables AdminLTE's responsive sidebar functionality -->

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <!-- Hamburger Menu Icon: Toggles the sidebar on mobile -->
                <li class="nav-item d-lg-none">
                    <a class="nav-link custom-sidebar-toggle" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <div class="d-flex align-items-center h-100 pl-3 header-content-wrapper">
                        <span class="@yield('header_icon', 'default-icon-class')"></span>
                        <h1 class="header-title mb-0 ml-2">@yield('content_header', 'Page Title')</h1>
                    </div>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto d-flex align-items-center">

              <!-- Notification Bell -->
<li class="nav-item dropdown d-flex align-items-center">
    <a class="nav-link position-relative d-flex align-items-center justify-content-center rounded-circle bg-light mx-2"
        style="width:45px; height:45px;" data-toggle="dropdown" href="#">
        <i class="far fa-bell" style="font-size:20px;"></i>
        @if (auth()->user()->unreadNotifications->count() > 0)
            <span class="badge badge-danger navbar-badge"
                style="font-size: 0.65rem; position: absolute; top: 6px; right: 6px;">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">
            {{ auth()->user()->notifications->count() }} Notifications
        </span>
        <div class="dropdown-divider"></div>

        @forelse(auth()->user()->notifications->take(5) as $notification)
            @php
                $url = $notification->data['url'] ?? null;
                $message = $notification->data['message'] ?? 'Notification';
                $isUnread = is_null($notification->read_at);
            @endphp

            <a href="{{ route('notifications.readAndRedirect', ['id' => $notification->id]) }}"
               class="dropdown-item d-flex align-items-start {{ $isUnread ? 'font-weight-bold' : '' }}">
                <i class="fas fa-info-circle fa-2x text-primary mr-2"></i>
                <div class="notification-text" style="white-space: normal; max-width: 250px;">
                    <div class="text-sm">{!! $message !!}</div>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
            </a>
            <div class="dropdown-divider"></div>
        @empty
            <span class="dropdown-item text-center text-muted">No notifications</span>
        @endforelse

        <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">
            See All Notifications
        </a>
    </div>
</li>



                <!-- Profile Menu -->
                <li class="nav-item dropdown user-menu d-flex align-items-center">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                        @php
                            $user = Auth::user();
                            $employee = $user?->employee;
                        @endphp
                        <img src="{{ $employee && $employee->photo ? asset('storage/' . $employee->photo) : 'https://placehold.co/160x160/9A3B3B/FFFFFF?text=' . strtoupper(substr($user->name, 0, 1)) }}"
                            class="user-image img-circle elevation-2" alt="User Image"
                            style="width:45px; height:45px; object-fit:cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="{{ $employee && $employee->photo ? asset('storage/' . $employee->photo) : 'https://placehold.co/160x160/9A3B3B/FFFFFF?text=' . strtoupper(substr($user->name, 0, 1)) }}"
                                class="img-circle elevation-2" alt="User Image"
                                style="width:90px; height:90px; object-fit:cover;">
                            <p>
                                {{ Auth::user()->name ?? 'Admin User' }}
                                <small>Member since {{ (Auth::user()->created_at ?? now())->format('M. Y') }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer -->
                        <li class="user-footer">
                            @php
                                $isSuperadmin = $user && $user->role === 'superadmin';
                            @endphp
                            @if ($isSuperadmin)
                                <a href="#" class="btn btn-default btn-flat"
                                    onclick="alert('Superadmin does not have personal data')">Profile</a>
                            @elseif ($employee)
                                <a href="{{ route('employees.show', $employee->id) }}"
                                    class="btn btn-default btn-flat">Profile</a>
                            @else
                                <a href="#" class="btn btn-default btn-flat"
                                    onclick="alert('Employee data not yet available')">Profile</a>
                            @endif
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="btn btn-default btn-flat float-right">Sign out</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar elevation-4">
            <a href="{{ url('/') }}" class="brand-link">
    <img src="{{ asset('img/BPR LOGO WITH PX (updated)-13.png') }}" alt="HRIS Logo" class="brand-image">

    <div class="brand-text-wrapper">
        <span class="brand-text brand-title">HRIS</span>
        <span class="brand-text brand-subtitle">
            {{ ucfirst(str_replace('_', ' ', Auth::user()->role ?? 'User')) }}
        </span>
        @if(Auth::user()->employee && Auth::user()->employee->division)
            <span class="brand-text brand-subtitle text-sm text-muted">
                {{ Auth::user()->employee->division->name }}
            </span>
        @endif
    </div>
</a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @foreach ($menu ?? [] as $item)
                            <li class="nav-item">
                                @php
                                    $url = '#';
                                    if ($item['route'] !== '#') {
                                        try {
                                            $url = isset($item['params'])
                                                ? route($item['route'], $item['params'])
                                                : route($item['route']);
                                        } catch (Exception $e) {
                                            $url = '#';
                                        }
                                    }
                                    $isActive =
                                        request()->routeIs($item['route']) ||
                                        (isset($item['submenu']) &&
                                            collect($item['submenu'])->contains(function ($subitem) {
                                                return request()->routeIs($subitem['route']);
                                            }));
                                @endphp
                                <a href="{{ $url }}" class="nav-link{{ $isActive ? ' active' : '' }}"
                                    @if ($url === '#') onclick="alert('This feature is under development')" @endif>
                                    <div class="nav-icon-text d-flex align-items-center">
                                        <span class="iconify mr-2" data-icon="{{ $item['icon'] ?? 'mdi:alert' }}"
                                            style="font-size: 18px;"></span>
                                        <p class="mb-0">{{ $item['label'] }}</p>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            
            {{-- Global Alert & Validation Errors --}}
            @include('partials.alert')
            @include('partials.validation-errors')

            @hasSection('content-wrapper')
                @yield('content-wrapper')
            @else
                <section class="content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </section>
            @endif
        </div>

    </div>

    <!-- REQUIRED SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    @stack('scripts')
</body>
    <script>
    $(document).ready(function() {
        // Handle sidebar toggle for desktop (collapse/expand)
        $('.custom-sidebar-toggle').on('click', function(e) {
            e.preventDefault();
            if ($(window).width() >= 992) { // Desktop
                $('body').toggleClass('sidebar-collapse');
            } else { // Mobile
                $('body').toggleClass('sidebar-open');
            }
        });

        // For mobile: close sidebar when clicking outside
        $(document).on('click', function(e) {
            if ($(window).width() < 992) { // Mobile
                if (!$(e.target).closest('.main-sidebar').length &&
                    !$(e.target).closest('.custom-sidebar-toggle').length &&
                    $('body').hasClass('sidebar-open')) {
                    $('body').removeClass('sidebar-open');
                }
            }
        });
    });

    function showDeletePopup(id) {
        const modal = new bootstrap.Modal(document.getElementById('deletePopup-' + id));
        modal.show();
    }

    function closeDeletePopup(id) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deletePopup-' + id));
        if (modal) modal.hide();
    }
</script>

</html>
