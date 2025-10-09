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
    public function redirect($id)
{
    $notification = auth()->user()->notifications()->findOrFail($id);

    // Tandai sebagai read
    if (is_null($notification->read_at)) {
        $notification->markAsRead();
    }

    // Redirect ke URL yang tersimpan dalam notifikasi
    $url = $notification->data['url'] ?? route('notifications.index');

    return redirect($url);
}

public function readAndRedirect($id)
{
    $notification = auth()->user()->notifications()->findOrFail($id);

    // Tandai notifikasi sebagai read
    if (is_null($notification->read_at)) {
        $notification->markAsRead();
    }

    // Ambil URL dari data notifikasi (jika ada)
    $url = $notification->data['url'] ?? route('notifications.index');

    // Redirect ke halaman tujuan
    return redirect($url);
}


}
