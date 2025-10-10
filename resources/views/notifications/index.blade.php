@extends('layouts.admin')

@section('title', 'Notifications')

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" 
             width="24" height="24" viewBox="0 0 24 24" 
             class="mr-2" fill="currentColor">
            <path d="M10 21h4a2 2 0 0 1-4 0m9-6V11a7 7 0 0 0-5-6.71V4a2 2 0 1 0-4 0v.29
                     A7 7 0 0 0 5 11v4l-1.29 1.29A1 1 0 0 0 4 18h16a1 1 0 0 0 .71-1.71Z"/>
        </svg>
        <h1 class="header-title mb-0">Notifications</h1>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Your Notifications</h3>
        <form action="{{ route('notifications.readAll') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-primary">Mark all as read</button>
        </form>
    </div>
    <div class="card-body">
        <ul class="list-group">
            @forelse($notifications as $notification)
                <li class="list-group-item d-flex justify-content-between align-items-start 
                           {{ $notification->read_at ? '' : 'font-weight-bold' }}">
                    <div>
                        {{-- 🔗 Jika notifikasi punya URL, klik langsung tandai read --}}
                        @if(isset($notification->data['url']))
                            <a href="{{ route('notifications.redirect', ['id' => $notification->id]) }}"
                               class="text-dark">
                                {!! $notification->data['message'] !!}
                            </a>
                        @else
                            {{ $notification->data['message'] }}
                        @endif

                        <small class="text-muted d-block">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                </li>
            @empty
                <li class="list-group-item">No notifications found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
