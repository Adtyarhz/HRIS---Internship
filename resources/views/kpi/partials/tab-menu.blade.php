@push('styles')
<style>
    .tabs-container {
        margin: -20px -20px 20px -20px;
    }

    .tabs-nav {
        display: flex;
        width: 100%;
        height: 50px;
        background: #F7F7DA;
        border-bottom: 1px solid rgba(0, 0, 0, 0.20);
    }

    .tabs-nav__item {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
        height: 100%;
        padding: 5px 10px;
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
