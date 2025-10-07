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
    $employeeId = $employee->id ?? null;

    $allTabs = [
        'employees.edit'                     => 'Personal',
        'employees.address.edit'             => 'Address',
        'employees.family-dependents.index'  => 'Family &<br/>Dependent',
        'employees.educationhistory.index'   => 'Education<br/>History',
        'employees.training-histories.index' => 'Training<br/>Record',
        'employees.health.edit'              => 'Health<br/>History',
        'employees.certifications.index'     => 'Certification',
        'employees.insurance.index'          => 'Insurance',
        'employees.work-experience.index'    => 'Work<br/>Experience',
    ];
@endphp

<div class="tabs-container">
    <nav class="tabs-nav">
        @foreach ($allTabs as $route => $label)
            @php
                $isRouteActive = Route::has($route) && $employeeId;

                // Get route prefix without CRUD suffix
                $routePrefix = preg_replace('/\.(index|edit|create|show)$/', '', $route);

                // Get the current route name
                $currentRoute = request()->route()->getName();

                // Check if the current route starts with the same prefix and the next segment is the same (not just 'employees.')
                $isActivePage =
                    $currentRoute === $route ||
                    Str::startsWith($currentRoute, $routePrefix . '.') &&
                    Str::startsWith($routePrefix, 'employees.') &&
                    explode('.', $currentRoute)[1] === explode('.', $routePrefix)[1];

                // Add exception for when creating new employee
                if (request()->routeIs('employees.create') && $route === 'employees.edit') {
                    $isActivePage = true;
                }

                $classes = 'tabs-nav__item';
                if ($isActivePage) {
                    $classes .= ' tabs-nav__item--active';
                } elseif (!$isRouteActive && !request()->routeIs('employees.create')) {
                    $classes .= ' tabs-nav__item--inactive';
                }
            @endphp

            @if ($isRouteActive)
                <a href="{{ route($route, $employeeId) }}" class="{{ $classes }}">
                    <span class="tabs-nav__item-text">{!! $label !!}</span>
                </a>
            @else
                <div class="{{ $classes }}" @if (!Route::has($route)) title="Fitur ini belum tersedia" @endif>
                    <span class="tabs-nav__item-text">{!! $label !!}</span>
                </div>
            @endif
        @endforeach
    </nav>
</div>