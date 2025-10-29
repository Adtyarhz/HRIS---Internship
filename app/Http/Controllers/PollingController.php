<?php

namespace App\Http\Controllers;

use App\Models\Polling;
use App\Models\PollingVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollingController extends Controller
{
    public function vote(Request $request, $pollingId)
    {
        $request->validate([
            'polling_option_id' => 'required|exists:polling_options,id',
        ]);

       // $userId = 1; // Gunakan ID test user "1" untuk sementara
        $userId = Auth::id();
        $userRole = Auth::user()->role; // pastikan ada field `role` di users


        $polling = Polling::with('announcement')->findOrFail($pollingId);

        // Cegah test user "1" (diasumsikan sebagai HC atau pembuat) untuk memberikan suara
        $forbiddenRoles = ['superadmin'];

        if (in_array($userRole, $forbiddenRoles)) {
            return back()->with('error', 'Role Anda tidak diizinkan memberikan suara.');
        }

        if ($polling->announcement->created_by === $userId) {
            return back()->with('error', 'Anda tidak diizinkan memberikan suara pada polling yang Anda buat.');
        }

        // Cegah voting jika sudah lewat batas waktu
        if ($polling->deadline && now()->gt($polling->deadline)) {
            return back()->with('error', 'Polling sudah ditutup.');
        }

        // Cek apakah user sudah voting di polling tersebut
        $sudahVote = PollingVote::where('created_by', $userId)
            ->whereHas('pollingOption', function ($query) use ($pollingId) {
                $query->where('polling_id', $pollingId);
            })
            ->exists();

        if ($sudahVote) {
            return back()->with('error', 'You have already voted on this poll.');
        }

        // Simpan suara
        PollingVote::create([
            'created_by' => $userId,
            'polling_option_id' => $request->polling_option_id,
        ]);

        return back()->with('success', 'Thank you, your voice has been recorded.');
    }

    public function export($id)
    {
        $polling = Polling::with('options.votes')->findOrFail($id);

        // Izinkan test user "1" untuk ekspor (diasumsikan sebagai HC)
        if (1 !== 1) { // Kondisi ini selalu false untuk test user "1"
            abort(403, 'Anda tidak diizinkan mengunduh hasil polling.');
        }

        if (!$polling->deadline || now()->lt($polling->deadline)) {
            return back()->with('error', 'Hasil polling hanya bisa diunduh setelah batas waktu berakhir.');
        }

        $filename = 'hasil_polling_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($polling) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Opsi', 'Jumlah Suara']);

            foreach ($polling->options as $option) {
                fputcsv($handle, [$option->option_text, $option->votes->count()]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function endNow($id)
{
    $polling = Polling::findOrFail($id);

    // Hanya superadmin atau HC yang bisa end polling
    if (!in_array(Auth::user()->role, ['superadmin', 'hc'])) {
        abort(403, 'Anda tidak memiliki izin untuk mengakhiri polling ini.');
    }

    // Jika polling sudah berakhir, kembalikan pesan
    if ($polling->deadline && now()->gt($polling->deadline)) {
        return back()->with('info', 'Polling sudah berakhir sebelumnya.');
    }

    // Update deadline jadi waktu sekarang
    $polling->update([
        'deadline' => now(),
    ]);

    return back()->with('success', 'Poll has been ended manually.');
}

}