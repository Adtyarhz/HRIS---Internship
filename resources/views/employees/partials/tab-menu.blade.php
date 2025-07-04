@push('styles')
<style>
    /*
     * Container ini berfungsi untuk "menarik" menu tab keluar dari padding
     * default .content-wrapper di AdminLTE, membuatnya rapat dengan header.
     * Sesuaikan nilai margin negatif jika padding layout Anda berbeda.
     */
    .tabs-container {
        margin: -20px -20px 20px -20px; /* Asumsi padding default adalah 20px */
    }

    .tabs-nav {
        display: flex;
        width: 100%;
        height: 50px;
        background: #F7F7DA;
        border-bottom: 1px solid rgba(0, 0, 0, 0.20);
    }

    .tabs-nav__item {
        /*
         * `flex: 1;` membuat semua tab memiliki lebar yang sama dan mengisi ruang.
         * Ini menggantikan kebutuhan untuk scroll horizontal.
         */
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
        height: 100%;
        padding: 5px 10px; /* Padding vertikal dan sedikit horizontal */
        border-right: 1px solid rgba(0, 0, 0, 0.20);
        box-sizing: border-box;
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
        white-space: nowrap;
    }

    .tabs-nav__item:last-child {
        border-right: none;
    }

    .tabs-nav__item-text {
        color: black;
        font-size: 12px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        line-height: 1.3;
    }

    /* --- MODIFIERS --- */

    .tabs-nav__item--active {
        background: #D8E6AD;
        font-weight: 600;
    }

    a.tabs-nav__item:hover {
        background: #c9d893;
        text-decoration: none;
        color: black;
    }

    .tabs-nav__item--inactive {
        cursor: not-allowed;
        background-color: #f7f7da !important;
        opacity: 0.6;
    }

    .tabs-nav__item--inactive .tabs-nav__item-text {
        color: #777;
    }

    /* Responsive untuk layar sangat kecil, teks mungkin perlu dikecilkan */
    @media (max-width: 768px) {
        .tabs-nav__item-text {
            font-size: 11px;
        }
        .tabs-nav__item {
            padding: 5px;
        }
    }
</style>
@endpush

@php
    $employeeId = $employee->id ?? null;

    $allTabs = [
        'employees.edit'             => 'Personal',
        'employees.address.edit'     => 'Address',
        'employees.family.edit'      => 'Family &<br/>Dependent',
        'employees.education.edit'   => 'Education',
        'employees.training.edit'    => 'Training Record',
        'employees.health.edit'      => 'Health History',
        'employees.certification.edit' => 'Certification',
        'employees.assurance.edit'   => 'Assurance',
        'employees.work-experience.index'  => 'Work<br/>Experience',
    ];
@endphp

<div class="tabs-container">
    <nav class="tabs-nav">
        @foreach ($allTabs as $route => $label)
        @php
            // Menentukan class modifier berdasarkan kondisi
            $isRouteActive = Route::has($route) && $employeeId;
            $isActivePage = request()->routeIs($route . '*') 
            || ($route === 'employees.work-experience.index' && request()->routeIs('employees.work-experience.create'))
            || ($route === 'employees.work-experience.index' && request()->routeIs('employees.work-experience.edit'))
            || (request()->routeIs('employees.create') && $route == 'employees.edit');
            $classes = 'tabs-nav__item';

            if ($isActivePage) {
                $classes .= ' tabs-nav__item--active';
            } elseif (!$isRouteActive && !request()->routeIs('employees.create')) {
                // Jangan set inactive jika di halaman create
                $classes .= ' tabs-nav__item--inactive';
            }
        @endphp

        @if ($isRouteActive)
            {{-- Jika route ada dan ini mode edit, buat link <a> --}}
            <a href="{{ route($route, $employeeId) }}" class="{{ $classes }}">
                <span class="tabs-nav__item-text">{!! $label !!}</span>
            </a>
        @else
            {{-- Jika route belum ada ATAU ini mode create, tampilkan sebagai <div> --}}
            <div class="{{ $classes }}" @if (!Route::has($route)) title="Fitur ini belum tersedia" @endif>
                <span class="tabs-nav__item-text">{!! $label !!}</span>
            </div>
        @endif
    @endforeach
    </nav>
</div>
