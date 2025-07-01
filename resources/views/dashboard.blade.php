@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="header-title">Dashboard</h1>
@endsection

@section('content')
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="dashboard-container" style="display: flex; gap: 20px; padding: 20px; flex-wrap: wrap; min-height: 100%;">
        <!-- Chart Section (Employee Data) -->
        <div class="chart-section" style="width: 363px; height: 731px; background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.20); margin-bottom: 20px; position: relative; flex-shrink: 0;">
            <div style="width: 92px; height: 52px; margin: 0 auto; padding-top: 10px; color: black; font-size: 24px; font-family: Montserrat; font-weight: 400; text-align: center;">
                Employee Stats
            </div>
            <div id="employee-chart" style="width: 100%; height: 600px; margin-top: 20px;"></div>
        </div>

        <!-- Container kanan -->
        <div style="flex: 1; display: flex; flex-direction: column; gap: 10px;">
            <!-- Announcement Section -->
<div class="announcement-section" style="width: 100%; background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.20); padding: 20px; overflow-y: auto;">
    <div style="text-align: center; font-size: 24px; font-family: Montserrat; font-weight: 500; margin-bottom: 20px;">
        Announcement
    </div>

    @if ($announcements->isEmpty())
        <p style="text-align: center;">Tidak ada pengumuman saat ini. <strong>Count: {{ $announcements->total() }}</strong></p>
    @else
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach ($announcements as $announcement)
                <div style="background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 16px;">
                    <div style="font-weight: 600; color: #333; font-size: 16px; margin-bottom: 6px;">
                        <a href="{{ route('announcement.show', $announcement->id) }}" style="text-decoration: none; color: inherit;">
                            {{ $announcement->label ? '[' . strtoupper($announcement->label) . '] ' : '' }}{{ $announcement->title }}
                        </a>
                    </div>
                    <div style="font-size: 14px; color: #555;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 80, '...') }}
                    </div>
                    <div style="margin-top: 6px;">
                        <a href="{{ route('announcement.show', $announcement->id) }}" style="font-size: 13px; color: #9A3B3B; font-weight: 500; text-decoration: none;">
                            selengkapnya →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">
            {{ $announcements->links() }}
        </div>
    @endif
</div>
        </div>
    </div>

    @push('styles')
        <style>
            .dashboard-container {
                display: flex;
                gap: 20px;
                padding: 20px;
                flex-wrap: wrap;
                min-height: 100%;
            }
            .chart-section, .announcement-section {
                box-sizing: border-box;
            }
            .announcement-section {
                overflow-y: auto;
            }
            @media (max-width: 1200px) {
                .chart-section, .announcement-section {
                    width: 100% !important;
                }
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
