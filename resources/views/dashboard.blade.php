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
    <div style="margin-bottom: 20px; font-family: Montserrat; font-size: 20px; font-weight: 600;
                display: inline-block; border-bottom: 1px solid black; padding-bottom: 5px;">
        {{ $now }}
    </div>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">

        {{-- ================= LEFT PANEL ================= --}}
        <div style="flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 20px;">

            {{-- ================= EMPLOYEE STATISTICS ================= --}}
            @if($genderStats->isNotEmpty() || $divisionStats->isNotEmpty())
                <div style="background: #FFFEF9; border-radius: 10px;
                            border: 1px solid rgba(0, 0, 0, 0.2); padding: 20px;">

                    <div style="text-align: center; font-size: 22px; font-family: Montserrat;
                                font-weight: 500; border-bottom: 1px solid rgba(0,0,0,0.3);
                                padding-bottom: 10px; margin-bottom: 20px;">
                        <b>Employee Statistics</b>
                    </div>

                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                        {{-- Employee Gender --}}
                        @if($genderStats->isNotEmpty())
                            <div style="flex: 1; min-width: 250px;">
                                <div style="font-size: 18px; font-weight: 500; text-align:center; margin-bottom: 10px;">
                                    By Gender
                                </div>
                                <div style="height: 300px;">
                                    <canvas id="employee-gender-chart"></canvas>
                                </div>
                            </div>
                        @endif

                        {{-- Employee Division --}}
                        @if(in_array($role, ['superadmin', 'hc', 'direksi']) && $divisionStats->isNotEmpty())
                            <div style="flex: 1; min-width: 250px;">
                                <div style="font-size: 18px; font-weight: 500; text-align:center; margin-bottom: 10px;">
                                    By Division
                                </div>
                                <div style="height: 300px;">
                                    <canvas id="employee-division-chart"></canvas>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif


            {{-- ================= INTERNSHIP STATISTICS ================= --}}
            @if($internGenderStats->isNotEmpty() || $internDivisionStats->isNotEmpty())
                <div style="background: #F4F8FF; border-radius: 10px;
                            border: 1px solid rgba(0, 0, 0, 0.15); padding: 20px;">

                    <div style="text-align: center; font-size: 22px; font-family: Montserrat;
                                font-weight: 500; border-bottom: 1px solid rgba(0,0,0,0.3);
                                padding-bottom: 10px; margin-bottom: 20px;">
                        <b>Internship Statistics</b>
                    </div>

                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                        {{-- Intern Gender --}}
                        @if($internGenderStats->isNotEmpty())
                            <div style="flex: 1; min-width: 250px;">
                                <div style="font-size: 18px; font-weight: 500; text-align:center; margin-bottom: 10px;">
                                    By Gender
                                </div>
                                <div style="height: 300px;">
                                    <canvas id="intern-gender-chart"></canvas>
                                </div>
                            </div>
                        @endif

                        {{-- Intern Division --}}
                        @if(in_array($role, ['superadmin', 'hc', 'direksi']) && $internDivisionStats->isNotEmpty())
                            <div style="flex: 1; min-width: 250px;">
                                <div style="font-size: 18px; font-weight: 500; text-align:center; margin-bottom: 10px;">
                                    By Division
                                </div>
                                <div style="height: 300px;">
                                    <canvas id="intern-division-chart"></canvas>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- ================= RIGHT PANEL ================= --}}
        <div style="flex: 1; min-width: 280px; display: flex; flex-direction: column; gap: 20px;">

            {{-- Onboarding Documents --}}
            @if(isset($onboardingDocuments) && $onboardingDocuments->isNotEmpty())
                <div style="background: #FFFEF9; border-radius: 10px;
                            border: 1px solid rgba(0, 0, 0, 0.2); padding: 20px;">
                    <div style="font-size: 22px; font-family: Montserrat; font-weight: 500;
                                border-bottom: 1px solid rgba(0,0,0,0.3);
                                padding-bottom: 8px; margin-bottom: 16px;">
                        <b>Onboarding Documents</b>
                    </div>

                    @foreach ($onboardingDocuments as $doc)
                        <div style="margin-bottom: 16px;">
                            <div style="font-family: Montserrat; font-size: 18px; font-weight: 500;">
                                📄 {{ $doc->title }}
                            </div>
                            @if($doc->description)
                                <div style="font-size: 14px; color: #555;">
                                    {{ $doc->description }}
                                </div>
                            @endif
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                               style="color: #530087; font-size: 14px;">
                                <i class="fas fa-file-alt"></i> View File
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Announcements --}}
            <div style="background: #FFFEF9; border-radius: 10px;
                        border: 1px solid rgba(0, 0, 0, 0.2);
                        padding: 20px; overflow-y: auto; max-height: 800px;">
                <div style="font-size: 22px; font-family: Montserrat; font-weight: 500;
                            border-bottom: 1px solid rgba(0,0,0,0.3);
                            padding-bottom: 8px; margin-bottom: 16px;">
                    <b>Announcements</b>
                </div>

                @foreach ($announcements as $announcement)
                    <div style="margin-bottom: 18px;">
                        <a href="{{ route('announcement.show', ['announcement' => $announcement->id, 'from' => 'dashboard']) }}"
                           style="text-decoration: none; color: inherit;">
                            <span style="color: #530087;">[{{ strtoupper($announcement->label ?? 'HR') }}]</span>
                            {{ $announcement->title }}
                        </a>
                    </div>
                @endforeach

                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Employee Gender
    @if($genderStats->isNotEmpty())
    new Chart(document.getElementById('employee-gender-chart'), {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($genderStats->toArray())),
            datasets: [{ data: @json(array_values($genderStats->toArray())) }]
        }
    });
    @endif

    // Employee Division
    @if(in_array($role, ['superadmin','hc','direksi']) && $divisionStats->isNotEmpty())
    new Chart(document.getElementById('employee-division-chart'), {
        type: 'bar',
        data: {
            labels: @json($divisionStats->pluck('name')),
            datasets: [{ data: @json($divisionStats->pluck('employees_count')) }]
        }
    });
    @endif

    // Intern Gender
    @if($internGenderStats->isNotEmpty())
    new Chart(document.getElementById('intern-gender-chart'), {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($internGenderStats->toArray())),
            datasets: [{ data: @json(array_values($internGenderStats->toArray())) }]
        }
    });
    @endif

    // Intern Division
    @if(in_array($role, ['superadmin','hc','direksi']) && $internDivisionStats->isNotEmpty())
    new Chart(document.getElementById('intern-division-chart'), {
        type: 'bar',
        data: {
            labels: @json($internDivisionStats->pluck('name')),
            datasets: [{ data: @json($internDivisionStats->pluck('employees_count')) }]
        }
    });
    @endif
});
</script>
@endpush
@endsection
