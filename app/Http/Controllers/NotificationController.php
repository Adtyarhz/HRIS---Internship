<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications;
        return view('notifications.index', compact('notifications'));
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read');
    }
    public function markOne(Request $request)
{
    $request->validate([
        'id' => 'required|uuid', // id di tabel notifications itu UUID
    ]);

    $notification = auth()->user()->notifications()->where('id', $request->id)->first();

    if ($notification) {
        $notification->markAsRead();
    }

    return back()->with('success', 'Notification marked as read.');
}

}
