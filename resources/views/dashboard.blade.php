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
        Carbon::setLocale('id');
        $now = Carbon::now()->translatedFormat('l, d F Y H:i');
    @endphp

    <div style="width: 1175px; min-height: 100vh; position: relative; background: #FEFEF9; overflow: hidden; padding: 15px;">
        {{-- Tanggal Hari Ini --}}
        <div style="width: 370px; margin-left: 43px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; font-family: Montserrat; font-size: 24px; font-weight: semibold; border-bottom: 1px solid black; padding-bottom: 5px;">
            {{ $now }}
        </div>

        <div style="display: flex; gap: 20px; padding: 0 15px;">
            {{-- Panel Kiri: Employee Stats --}}
            <div style="width: 363px; height: 731px; background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.2); padding: 20px; flex-shrink: 0;">
                <div style="text-align: center; font-size: 24px; font-family: Montserrat; font-weight: 500; border-bottom: 1px solid rgba(0,0,0,0.3); padding-bottom: 10px; margin-bottom: 20px;">
                    Employee Stats
                </div>
                <div id="employee-chart" style="height: 600px;"></div>
            </div>

{{-- Panel Kanan: Announcement --}}
<div style="flex: 1; height: 747px; background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.2); padding: 20px; overflow-y: auto;">
    <div style="font-size: 24px; font-family: Montserrat; font-weight: 500; border-bottom: 1px solid rgba(0,0,0,0.3); padding-bottom: 8px; margin-bottom: 16px;">
        Announcement
    </div>

    @if ($announcements->isEmpty())
        <p style="text-align: center;">Tidak ada pengumuman saat ini.</p>
    @else
        @foreach ($announcements as $announcement)
            <div style="margin-bottom: 18px;">
                {{-- Label dan Judul --}}
                <div style="font-family: 'Montserrat', sans-serif; font-size: 26px; font-weight: 500; color: #000;">
                <a href="{{ route('announcement.show', ['announcement' => $announcement->id, 'from' => 'dashboard']) }}"
                    style="text-decoration: none; color: inherit;">
                        <span style="color: #530087;">[{{ strtoupper($announcement->label ?? 'HR') }}]</span>
                        {{ $announcement->title }}
                    </a>
                </div>

                {{-- Ringkasan konten --}}
                <div style="color: #555; font-size: 13.5px; font-family: 'Montserrat', sans-serif; font-weight: 200; margin-top: 0px;">
                    {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 120, '...') }}
                </div>
            </div>
        @endforeach

        <div>
            {{ $announcements->links() }}
        </div>
    @endif
</div>

    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex&display=swap" rel="stylesheet">
        <style>
            body {
                background-color: #FEFEF9;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('employee-chart');
                if (ctx) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['HR', 'IT', 'Finance', 'Marketing'],
                            datasets: [{
                                label: 'Employee Count',
                                data: [15, 10, 8, 12],
                                backgroundColor: 'rgba(154, 59, 59, 0.6)',
                                borderColor: 'rgba(154, 59, 59, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection