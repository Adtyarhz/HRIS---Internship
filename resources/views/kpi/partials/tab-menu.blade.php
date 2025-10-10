@push('styles')
<style>
    /* Container styling to handle layout integration with AdminLTE */
    .tabs-container {
        margin: 0px 0px 0px 0px;
        width: 100%;
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        width: 100%;
        background: #F7F7DA;
        border-bottom: 1px solid rgba(0, 0, 0, 0.20);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on touch devices */
        white-space: nowrap; /* Prevent wrapping for scrollable tabs */
        scrollbar-width: none; /* Hide scrollbar for Firefox */
    }

    .tabs-nav::-webkit-scrollbar {
        display: none; /* Hide scrollbar for Webkit browsers */
    }

    .tabs-nav__item {
        flex: 1 0 auto; /* Allow tabs to size based on content */
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
        height: 50px;
        padding: 8px 12px;
        border-right: 1px solid rgba(0, 0, 0, 0.20);
        box-sizing: border-box;
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
    }

    .tabs-nav__item:last-child {
        border-right: none;
    }

    .tabs-nav__item-text {
        color: black;
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        line-height: 1.3;
        font-size: 14px; /* Base font size */
    }

    /* Modifiers */
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

    /* Responsive Design */
    @media (max-width: 1024px) {
        .tabs-nav__item {
            min-width: 80px;
            padding: 6px 10px;
        }

        .tabs-nav__item-text {
            font-size: 13px;
        }
    }

    @media (max-width: 768px) {
        .tabs-container {
            margin: -15px -15px 15px -15px; /* Adjusted for smaller screens */
        }

        .tabs-nav {
            height: 48px;
        }

        .tabs-nav__item {
            min-width: 70px;
            padding: 5px 8px;
        }

        .tabs-nav__item-text {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .tabs-container {
            margin: -10px -10px 10px -10px;
        }

        .tabs-nav {
            height: 40px;
        }

        .tabs-nav__item {
            min-width: 60px;
            padding: 4px 6px;
        }

        .tabs-nav__item-text {
            font-size: 11px;
            line-height: 1.2;
        }
    }
</style>
@endpush

@php
    $role = auth()->user()->role ?? null;

    // Definisi semua tab KPI
    $kpiSubmenu = [
        ['label' => 'KPI Periods', 'route' => 'kpi-periods.index'],
        ['label' => 'KPI Indicators', 'route' => 'kpi-indicators.index'],
        ['label' => 'KPI Template', 'route' => 'kpi-templates.index'],
        ['label' => 'KPI Assessment', 'route' => 'kpi-assessments.index'],
        ['label' => 'KPI Report', 'route' => 'kpi-reports.index'],
    ];

    // Tentukan menu berdasarkan role
    if ($role === 'superadmin') {
        $visibleTabs = collect($kpiSubmenu)
                        ->whereIn('label', ['KPI Periods', 'KPI Indicators', 'KPI Template'])
                        ->all();
    } elseif ($role === 'hc') {
        $visibleTabs = $kpiSubmenu;
    } elseif ($role === 'manager') {
        $visibleTabs = collect($kpiSubmenu)
                        ->whereIn('label', ['KPI Assessment', 'KPI Report'])
                        ->all();
    } else {
        $visibleTabs = [];
    }
@endphp

@if (!empty($visibleTabs))
    <div class="tabs-container">
        <nav class="tabs-nav">
            @foreach ($visibleTabs as $tab)
                @php
                    $isActivePage = request()->routeIs($tab['route'] . '*');
                    $classes = 'tabs-nav__item' . ($isActivePage ? ' tabs-nav__item--active' : '');
                @endphp

                <a href="{{ route($tab['route']) }}" class="{{ $classes }}">
                    <span class="tabs-nav__item-text">{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>
@endif
