<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'HRIS Panel')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Manrope:wght@400&family=Noto+Sans+Georgian:wght@400&display=swap" rel="stylesheet" />
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />

    @stack('styles')

</head>
<body>
    <div id="app" class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('img/logo.png') }}" alt="Company Logo" class="logo" />
                <div class="brand">
                    <span class="brand-title">HRIS</span>
                    <span class="brand-subtitle">Super Admin</span>
                </div>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    {{-- Contoh link menu dinamis dari Laravel --}}
                    <li class="{{- request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{-- route('dashboard') --}}">Dashboard</a>
                    </li>
                    <li class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <a href="{{ route('employees.index') }}">Employee Information</a>
                    </li>
                    <li class="{{ request()->routeIs('requests.*') ? 'active' : '' }}">
                        <a href="{{-- route('requests.index') --}}#">Employee Request</a>
                    </li>
                    <li class="{{ request()->routeIs('organization.*') ? 'active' : '' }}">
                        <a href="#">Organization Structure</a>
                    </li>
                    <li class="{{ request()->routeIs('careers.*') ? 'active' : '' }}">
                        <a href="#">Careers Administration</a>
                    </li>
                    <li class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <a href="#">Time & Attendance</a>
                    </li>
                    <li class="{{ request()->routeIs('reimbursement.*') ? 'active' : '' }}">
                        <a href="#">Reimbursement</a>
                    </li>
                     <li class="{{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                        <a href="#">Payroll</a>
                    </li>
                     <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a href="#">Setting</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            
            <!-- Header Atas -->
            <header class="main-header">
                <h1 class="header-title">
                    @yield('content_header', 'Page Title')
                </h1>
                <img src="https://placehold.co/50x50" alt="User Avatar" class="user-avatar" />
            </header>

            <!-- Panel Konten Utama -->
            <div class="form-panel">
                
                @yield('content')
            
            </div>
        </main>
    </div>

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script> --}}
</body>
</html>
