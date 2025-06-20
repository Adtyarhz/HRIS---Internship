<?php

namespace App\Http\Controllers;

use App\Models\Polling;
use App\Models\PollingVote;
use App\Models\PollingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PollingController extends Controller
{
  public function vote(Request $request, $pollingId)
  {
      $request->validate([
          'polling_option_id' => 'required|exists:polling_options,id',
      ]);
  
      $userId = Auth::id();
  
      $polling = Polling::with('pengumuman')->findOrFail($pollingId);
  
      // Cegah HC atau pembuat polling untuk vote
      if (Auth::user()->role === 'HC' || $polling->pengumuman->user_id === $userId) {
          return back()->with('error', 'Anda tidak diizinkan memberikan suara pada polling ini.');
      }
  
      // Cegah voting jika sudah lewat batas waktu
      if ($polling->batas_waktu && now()->gt($polling->batas_waktu)) {
          return back()->with('error', 'Polling sudah ditutup.');
      }
  
      // Cek apakah user sudah voting di polling tersebut
      $sudahVote = PollingVote::where('user_id', $userId)
          ->whereHas('pollingOption', function ($query) use ($pollingId) {
              $query->where('polling_id', $pollingId);
          })
          ->exists();
  
      if ($sudahVote) {
          return back()->with('error', 'Anda sudah memberikan suara pada polling ini.');
      }
  
      PollingVote::create([
          'user_id' => $userId,
          'polling_option_id' => $request->polling_option_id,
      ]);
  
      return back()->with('success', 'Terima kasih, suara Anda telah direkam.');
  }
  
    public function export($id)
    {
        $polling = Polling::with('options.votes')->findOrFail($id);

        // Hanya role HC yang bisa ekspor
        if (auth()->user()->role !== 'HC') {
            abort(403, 'Anda tidak diizinkan mengunduh hasil polling.');
        }

        // Cek apakah batas waktu sudah habis
        if (!$polling->batas_waktu || now()->lt($polling->batas_waktu)) {
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

        return new StreamedResponse($callback, 200, $headers);
    }
}
