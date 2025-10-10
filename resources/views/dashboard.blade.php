@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    <div style="display: flex; align-items: center; gap: 10px; font-family: 'Montserrat', sans-serif; font-weight: 500; font-size: 24px;">
        <i class="fas fa-home" style="color: #000;"></i>
        <span>Dashboard</span>
    </div>
@endsection

@section('content')
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Facades\Auth;

        Carbon::setLocale('en');
        $now = Carbon::now()->translatedFormat('l, d F Y H:i');
        $role = Auth::user()->role;
    @endphp

    <div style="width: 100%; min-height: 100vh; background: #FEFEF9; padding: 15px;">
        {{-- Current Date --}}
        <div style="margin-bottom: 20px; font-family: Montserrat; font-size: 20px; font-weight: 600; display: inline-block; border-bottom: 1px solid black; padding-bottom: 5px;">
            {{ $now }}
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            {{-- Left Panel: Employee Stats --}}
            @if($genderStats->isNotEmpty() || $divisionStats->isNotEmpty())
                <div style="flex: 2; min-width: 300px; background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.2); padding: 20px;">
                    <div style="text-align: center; font-size: 22px; font-family: Montserrat; font-weight: 500; border-bottom: 1px solid rgba(0,0,0,0.3); padding-bottom: 10px; margin-bottom: 20px;">
                        Employee Statistics
                    </div>

                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        {{-- Gender Chart --}}
                        @if($genderStats->isNotEmpty())
                            <div style="flex: 1; min-width: 250px;">
                                <div style="font-size: 18px; font-weight: 500; text-align:center; margin-bottom: 10px; font-family: Montserrat;">
                                    By Gender
                                </div>
                                <div style="height: 300px;">
                                    <canvas id="employee-chart"></canvas>
                                </div>
                            </div>
                        @endif

                        {{-- Division Chart: only for HC, Superadmin, Directors --}}
                        @if(in_array($role, ['superadmin', 'hc', 'direksi']) && $divisionStats->isNotEmpty())
                            <div style="flex: 1; min-width: 250px;">
                                <div style="font-size: 18px; font-weight: 500; text-align:center; margin-bottom: 10px; font-family: Montserrat;">
                                    By Division
                                </div>
                                <div style="height: 300px;">
                                    <canvas id="division-chart"></canvas>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Right Panel: Announcements --}}
            <div style="flex: 1; min-width: 280px; background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.2); padding: 20px; overflow-y: auto; max-height: 800px;">
                <div style="font-size: 22px; font-family: Montserrat; font-weight: 500; border-bottom: 1px solid rgba(0,0,0,0.3); padding-bottom: 8px; margin-bottom: 16px;">
                    Announcements
                </div>

                @if ($announcements->isEmpty())
                    <p style="text-align: center;">No announcements at the moment.</p>
                @else
                    @foreach ($announcements as $announcement)
                        <div style="margin-bottom: 18px;">
                            {{-- Label and Title --}}
                            <div style="font-family: 'Montserrat', sans-serif; font-size: 20px; font-weight: 500; color: #000;">
                                <a href="{{ route('announcement.show', ['announcement' => $announcement->id, 'from' => 'dashboard']) }}"
                                   style="text-decoration: none; color: inherit;">
                                    <span style="color: #530087;">[{{ strtoupper($announcement->label ?? 'HR') }}]</span>
                                    {{ $announcement->title }}
                                </a>
                            </div>

                            {{-- Content Summary --}}
                            <div style="color: #555; font-size: 14px; font-family: 'Montserrat', sans-serif; font-weight: 300; margin-top: 4px;">
                                {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 120, '...') }}
                            </div>
                        </div>
                    @endforeach

                    <div>
                        {{ $announcements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Gender Chart
                @if($genderStats->isNotEmpty())
                    const genderCtx = document.getElementById('employee-chart');
                    if (genderCtx) {
                        new Chart(genderCtx, {
                            type: 'doughnut',
                            data: {
                                labels: @json(array_keys($genderStats->toArray())),
                                datasets: [{
                                    label: 'Number of Employees',
                                    data: @json(array_values($genderStats->toArray())),
                                    backgroundColor: ['#36A2EB', '#FF6384'],
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom' },
                                    title: { display: true, text: 'Employee Gender Statistics' }
                                }
                            }
                        });
                    }
                @endif

                // Division Chart
                @if(in_array($role, ['superadmin', 'hc', 'direksi']) && $divisionStats->isNotEmpty())
                    const divisionCtx = document.getElementById('division-chart');
                    if (divisionCtx) {
                        new Chart(divisionCtx, {
                            type: 'bar',
                            data: {
                                labels: @json($divisionStats->pluck('name')),
                                datasets: [{
                                    label: 'Employees per Division',
                                    data: @json($divisionStats->pluck('employees_count')),
                                    backgroundColor: '#4BC0C0',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    title: { display: true, text: 'Employee Statistics by Division' }
                                },
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });
                    }
                @endif
            });
        </script>
    @endpush
@endsection
