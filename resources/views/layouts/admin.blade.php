<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'HRIS Panel')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Google Font: Custom -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Manrope:wght@400&family=Noto+Sans+Georgian:wght@400&display=swap"
        rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    {{-- Link ke CSS Kustom Anda --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />

    @stack('styles')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item d-none d-sm-inline-block">
                    <div class="d-flex align-items-center h-100 pl-3">
                        <span class="@yield('header_icon', 'default-icon-class')"></span>
                        <h1 class="header-title mb-0 ml-2">@yield('content_header', 'Page Title')</h1>
                    </div>
                </li>
            </ul>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="https://placehold.co/160x160" class="user-image img-circle elevation-2"
                            alt="User Image">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="https://placehold.co/160x160" class="img-circle elevation-2" alt="User Image">
                            <p>
                                {{ Auth::user()->name ?? 'Admin User' }}
                                <small>Member since {{ (Auth::user()->created_at ?? now())->format('M. Y') }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="#" class="btn btn-default btn-flat">Profile</a>
                            <a href="{{-- route('logout') --}}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="btn btn-default btn-flat float-right">Sign out</a>
                            <form id="logout-form" action="{{-- route('logout') --}}" method="POST" class="d-none">
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
            {{-- Konten sidebar tetap sama --}}
            <a href="{{ url('/') }}" class="brand-link">
                <img src="{{ asset('img/logo.png') }}" alt="HRIS Logo" class="brand-image">
                <div class="brand-text-wrapper">
                    <span class="brand-text brand-title">HRIS</span>
                    <span class="brand-text brand-subtitle">Super Admin</span>
                </div>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="#" class="nav-link{{ request()->routeIs('dashboard') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="mdi-light--home"></span>
                                    <p>Dashboard</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#"
                                class="nav-link{{ request()->routeIs('announcement.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="bi--list-ul"></span>
                                    <p>Announcement</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('employees.index') }}"
                                class="nav-link{{ request()->routeIs('employees.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="icon-park-outline--file-staff-one"></span>
                                    <p>Employee Information</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#"
                                class="nav-link{{ request()->routeIs('employee_request.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="charm--git-request"></span>
                                    <p>Employee Request</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item"><a href="#"
                                class="nav-link{{ request()->routeIs('organization_structure.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="fluent--organization-24-regular"></span>
                                    <p>Organization Structure</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item"><a href="#"
                                class="nav-link{{ request()->routeIs('careers_administration.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="material-symbols--work-outline"></span>
                                    <p>Careers Administration</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item"><a href="#"
                                class="nav-link{{ request()->routeIs('time_attendance.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="mdi--clock-outline"></span>
                                    <p>Time & Attendance</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item"><a href="#"
                                class="nav-link{{ request()->routeIs('reimbursement.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="ri--refund-line"></span>
                                    <p>Reimbursement</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item"><a href="#"
                                class="nav-link{{ request()->routeIs('payroll.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="ri--bill-line"></span>
                                    <p>Payroll</p>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item"><a href="#"
                                class="nav-link{{ request()->routeIs('setting.*') ? ' active' : '' }}">
                                <div class="nav-icon-text">
                                    <span class="uil--setting"></span>
                                    <p>Setting</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            {{-- Content Header (Judul Halaman) SUDAH DIPINDAHKAN KE ATAS --}}

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    @stack('scripts')
</body>

</html>
