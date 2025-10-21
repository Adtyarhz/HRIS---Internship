@push('styles')
<style>
    /* Container styling */
    .tabs-container {
        margin: 0;
        width: 100%;
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        width: 100%;
        background: #F7F7DA;
        border-bottom: 1px solid rgba(0, 0, 0, 0.20);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        white-space: nowrap;
        scrollbar-width: none;
    }

    .tabs-nav::-webkit-scrollbar {
        display: none;
    }

    .tabs-nav__item {
        flex: 1 0 auto;
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
        font-size: 14px;
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

    /* Responsive */
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
            margin: -15px -15px 15px -15px;
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

    // Dua tab saja
    $tabs = [
        ['label' => 'Applicant', 'route' => 'applicants.index'],
        ['label' => 'Interview Schedule', 'route' => 'interview-schedule.index'],
    ];

    // Filter tab berdasarkan role (opsional)
    if (in_array($role, ['superadmin', 'hc', 'manager', 'section_head', 'direksi'])) {
        $visibleTabs = $tabs;
    } else {
        $visibleTabs = [];
    }
@endphp

@if (!empty($visibleTabs))
    <div class="tabs-container">
        <nav class="tabs-nav">
            @foreach ($visibleTabs as $tab)
                @php
                    $isActive = request()->routeIs($tab['route'] . '*');
                    $classes = 'tabs-nav__item' . ($isActive ? ' tabs-nav__item--active' : '');
                @endphp

                <a href="{{ route($tab['route']) }}" class="{{ $classes }}">
                    <span class="tabs-nav__item-text">{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>
@endif
