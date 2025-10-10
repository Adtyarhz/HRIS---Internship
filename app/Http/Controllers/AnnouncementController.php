<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Polling;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Division;

class AnnouncementController extends Controller
{
    public function dashboard()
{
    try {
        // Ambil data pengumuman
        $announcements = Announcement::latest()->paginate(20);

        // Hitung jumlah karyawan berdasarkan gender
        $genderStats = Employee::selectRaw('gender, COUNT(*) as total')
            ->groupBy('gender')
            ->pluck('total', 'gender');

        // Hitung jumlah karyawan berdasarkan divisi
        $divisionStats = Division::where('name', '!=', 'N/A')->withCount('employees')->get();

        return view('dashboard', compact('announcements', 'genderStats', 'divisionStats'));
    } catch (\Exception $e) {
        \Log::error('Error loading dashboard: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat dashboard.');
    }
}



    public function index(Request $request)
    {
        try {
            $query = Announcement::query();

            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('type')) {
                $query->where('announcement_type', $request->type);
            }

            if ($request->filled('label')) {
                $query->where('label', 'like', '%' . $request->label . '%');
            }

            $announcements = $query->latest()->paginate(8);
            Log::info('Announcement index loaded', ['announcements_count' => $announcements->count()]);
            return view('announcement.index', compact('announcements'));
        } catch (\Exception $e) {
            Log::error('Error loading announcement index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat daftar pengumuman.');
        }
    }

    public function create()
    {
        return view('announcement.create');
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'announcement_type' => 'required|in:Umum,Divisi,Urgent,Informasi,Polling',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'external_link' => 'nullable|url',
            'label' => 'required|string|max:50',
            'deadline' => 'required_if:announcement_type,Polling|nullable|date|after:now',
            'options' => [
    'nullable',
    'array',
    function ($attribute, $value, $fail) use ($request) {
        if ($request->announcement_type === 'Polling') {
            if (!is_array($value) || count($value) < 1) {
                $fail('Minimal satu opsi polling harus diisi.');
            }
            foreach ($value as $opt) {
                if (trim($opt) === '') {
                    $fail('Semua opsi polling harus diisi.');
                }
            }
        }
    },
],
        ]);
        Log::info('Form masuk store', $request->all());

        $attachmentPath = null;

        if ($request->hasFile('attachment_file')) {
            $file = $request->file('attachment_file');
            $attachmentPath = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('announcement', $attachmentPath, 'public');
        }

        $announcement = Announcement::create([
            'created_by' => 1,
            'title' => $request->title,
            'content' => $request->content,
            'announcement_type' => $request->announcement_type,
            'attachment_file' => $attachmentPath,
            'label' => $request->label,
            'external_link' => $request->external_link,
        ]);

        if ($request->announcement_type === 'Polling') {
            $polling = $announcement->polling()->create([
                'deadline' => $request->deadline,
                'created_by' => 1,
            ]);

            if ($request->has('options')) {
                foreach ($request->options as $option) {
                    if (trim($option) !== '') {
                        $polling->options()->create(['option_text' => $option]);
                    }
                }
            }
        }

        return redirect()->route('announcement.index')->with('success', 'Announcement has been created');
    }

   public function show($id, Request $request)
{
    $announcement = Announcement::with('polling.options.votes')->findOrFail($id);
    $now = now();
    $deadline = optional($announcement->polling)->deadline;
    $isExpired = $deadline && $now->greaterThan($deadline);
    $from = $request->query('from');

    // Cek apakah user login sudah vote
    $userVote = null;
    $userId = Auth::id();

    if ($announcement->polling) {
        foreach ($announcement->polling->options as $option) {
            foreach ($option->votes as $vote) {
                if ($vote->created_by == $userId) {
                    $userVote = $vote;
                    break 2;
                }
            }
        }
    }

    return view('announcement.show', compact('announcement', 'isExpired', 'from', 'userVote'));
}

    public function edit($id)
    {
        $announcement = Announcement::with('polling.options')->findOrFail($id);
        return view('announcement.edit', compact('announcement'));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $polling = $announcement->polling;
        $isPolling = $announcement->announcement_type === 'Polling';
        $isExpired = $polling && $polling->deadline && now()->gt($polling->deadline);
        $isLocked = $polling && $polling->is_locked;
        $hasVotes = $polling && $polling->options()->withCount('votes')->get()->sum('votes_count') > 0;

    $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'announcement_type' => 'required|in:Umum,Divisi,Urgent,Informasi,Polling',
        'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'external_link' => 'nullable|url',
        'label' => 'required|string|max:50',
        'existing_options.*' => 'nullable|string',
        'options' => 'nullable|array',
        'options.*' => 'nullable|string|min:1',
        'deleted_options' => 'nullable|array',
    ];

    // Hanya validasi deadline jika polling masih bisa diedit
    if ($isPolling && !$isExpired && !$isLocked && !$hasVotes) {
        $rules['deadline'] = 'required_if:announcement_type,Polling||date|after:now';
    }

    $request->validate($rules);

    // ✅ Update field yang tetap bisa diubah
    $announcement->title = $request->title;
    $announcement->content = $request->content;
    $announcement->announcement_type = $request->announcement_type;
    $announcement->label = $request->label;
    $announcement->external_link = $request->external_link;

    // ✅ Handle file attachment
    if ($request->hasFile('attachment_file')) {
        if ($announcement->attachment_file && Storage::exists('public/announcement/' . $announcement->attachment_file)) {
            Storage::delete('public/announcement/' . $announcement->attachment_file);
        }

        $file = $request->file('attachment_file');
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('announcement', $filename, 'public');
        $announcement->attachment_file = $filename;
    }

    $announcement->save();

    // ✅ Hanya proses polling jika belum expired atau belum terkunci
    if ($announcement->announcement_type === 'Polling' && $polling) {
        $isExpired = $polling->deadline && now()->gt($polling->deadline);
        $hasVotes = $polling->options()->withCount('votes')->get()->sum('votes_count') > 0;

        if (!($polling->is_locked || $isExpired || $hasVotes)) {
            $polling->deadline = $request->deadline;
            $polling->save();

            // Update opsi lama
            if ($request->has('existing_options')) {
                foreach ($request->existing_options as $optionId => $optionText) {
                    $option = $polling->options()->find($optionId);
                    if ($option && trim($optionText) !== '') {
                        $option->update(['option_text' => $optionText]);
                    }
                }
            }

            // Hapus opsi
            if ($request->has('deleted_options')) {
                foreach ($request->deleted_options as $optionId) {
                    $polling->options()->where('id', $optionId)->delete();
                }
            }            

            // Tambah opsi baru
            if ($request->has('options')) {
                foreach ($request->options as $option) {
                    if (trim($option) !== '') {
                        $polling->options()->create(['option_text' => $option]);
                    }
                }
            }
        }
    }

    return redirect()->route('announcement.index')->with('success', 'Announcement has been updated');
}

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        if ($announcement->attachment_file && Storage::exists('public/announcement/' . $announcement->attachment_file)) {
            Storage::delete('public/announcement/' . $announcement->attachment_file);
        }

        $announcement->delete();

        return redirect()->route('announcement.index')->with('success', 'Announcement has been deleted');
    }

    public function exportPolling($id)
    {
        $announcement = Announcement::with('polling.options.votes')->findOrFail($id);
    
        if (!$announcement->polling || now()->lt($announcement->polling->deadline)) {
            return redirect()->back()->with('error', 'Polling belum berakhir atau tidak ditemukan.');
        }
    
        $filename = 'hasil_polling_' . Str::slug($announcement->title) . '.csv';
    
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
    
        $callback = function () use ($announcement) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Opsi', 'Jumlah Suara']);
    
            foreach ($announcement->polling->options as $option) {
                fputcsv($handle, [$option->option_text, $option->votes->count()]);
            }
    
            fclose($handle);
        };
    
        return new StreamedResponse($callback, 200, $headers);
    }

}