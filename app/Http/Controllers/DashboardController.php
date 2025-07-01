<?php
namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->take(2)->get(); // contoh ambil 2 terbaru
        return view('admin.dashboard', compact('announcements'));
    }
}
