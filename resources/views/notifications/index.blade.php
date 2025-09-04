@extends('layouts.admin')

@section('title', 'Notifications')

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
                <li class="list-group-item {{ $notification->read_at ? '' : 'font-weight-bold' }}">
                    {{-- 🔗 Jika notifikasi punya URL, tampilkan link --}}
                    @if(isset($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}">
                            {{ $notification->data['message'] }}
                        </a>
                    @else
                        {{ $notification->data['message'] }}
                    @endif

                    <small class="text-muted d-block">
                        {{ $notification->created_at->diffForHumans() }}
                    </small>
                </li>
            @empty
                <li class="list-group-item">No notifications found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
