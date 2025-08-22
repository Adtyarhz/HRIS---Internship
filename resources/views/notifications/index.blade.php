@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Your Notifications</h3>
        <form action="{{ route('notifications.readAll') }}" method="POST" class="float-right">
            @csrf
            <button class="btn btn-sm btn-primary">Mark all as read</button>
        </form>
    </div>
    <div class="card-body">
        <ul class="list-group">
            @forelse($notifications as $notification)
                <li class="list-group-item {{ $notification->read_at ? '' : 'font-weight-bold' }}">
                    {{ $notification->data['message'] }}
                    <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                </li>
            @empty
                <li class="list-group-item">No notifications found.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
