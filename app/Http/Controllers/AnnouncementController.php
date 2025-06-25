<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Polling;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
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

        $announcements = $query->latest()->paginate(10);

        return view('announcement.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcement.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'announcement_type' => 'required|in:Umum,Divisi,Urgent,Informasi,Polling',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'external_link' => 'nullable|url',
            'label' => 'nullable|string|max:50',
            'deadline' => 'nullable|date|after:now',
        ]);

        $attachmentPath = null;

        if ($request->hasFile('attachment_file')) {
            $file = $request->file('attachment_file');
            $attachmentPath = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('announcement', $attachmentPath, 'public');
        }

        $announcement = Announcement::create([
            'created_by' => Auth::id() ?? 1,
            'title' => $request->title,
            'content' => $request->content,
            'announcement_type' => $request->announcement_type,
            'attachment_file' => $attachmentPath,
            'label' => $request->label,
            'external_link' => $request->external_link,
        ]);

        if ($request->announcement_type === 'polling' && $request->has('options')) {
            $polling = $announcement->polling()->create([
                'deadline' => $request->deadline,
            ]);

            foreach ($request->options as $option) {
                if (trim($option) !== '') {
                    $polling->options()->create(['option_text' => $option]);
                }
            }
        }

        return redirect()->route('announcement.index')->with('success', 'Pengumuman berhasil dibuat');
    }

    public function show($id)
    {
        $announcement = Announcement::with('polling.options.votes')->findOrFail($id);

        $now = now();
        $deadline = optional($announcement->polling)->deadline;
        $isExpired = $deadline && $now->greaterThan($deadline);

        return view('announcement.show', compact('announcement', 'isExpired'));
    }

    public function edit($id)
    {
        $announcement = Announcement::with('polling.options')->findOrFail($id);
        return view('announcement.edit', compact('announcement'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'announcement_type' => 'required|in:Umum,Divisi,Urgent,Informasi,Polling',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'external_link' => 'nullable|url',
            'label' => 'nullable|string|max:50',
            'batas_waktu' => 'nullable|date|after:now',
        ]);

        $announcement = Announcement::findOrFail($id);

        // Upload lampiran baru jika ada
        if ($request->hasFile('attachment_file')) {
            if ($announcement->attachment_file && Storage::exists('public/announcement/' . $announcement->attachment_file)) {
                Storage::delete('public/announcement/' . $announcement->attachment_file);
            }

            $file = $request->file('attachment_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('announcement', $filename, 'public');
            $announcement->attachment_file = $filename;
        }

        // Update data utama
        $announcement->title = $request->title;
        $announcement->content = $request->content;
        $announcement->announcement_type = $request->announcement_type;
        $announcement->label = $request->label;
        $announcement->external_link = $request->external_link;
        $announcement->save();

        // Kelola polling jika tipe polling
        if ($announcement->announcement_type === 'polling' && $announcement->polling) {
            $polling = $announcement->polling;
            $isExpired = $polling->deadline && now()->gt($polling->deadline);
            $hasVotes = $polling->options()->withCount('votes')->get()->sum('votes_count') > 0;

            if ($isExpired || $hasVotes) {
                return redirect()->route('announcement.index')->with('error', 'Polling tidak dapat diubah karena sudah ada suara atau melewati batas waktu.');
            }

            $polling->deadline = $request->batas_waktu;
            $polling->save();

            // Update opsi lama
            if ($request->has('existing_options')) {
                foreach ($request->existing_options as $optionId => $optionText) {
                    $option = $polling->options()->find($optionId);
                    if ($option) {
                        $option->option_text = $optionText;
                        $option->save();
                    }
                }
            }

            // Hapus opsi yang dipilih
            if ($request->has('delete_options')) {
                foreach ($request->delete_options as $deleteId) {
                    $optionToDelete = $polling->options()->find($deleteId);
                    if ($optionToDelete) {
                        $optionToDelete->votes()->delete();
                        $optionToDelete->delete();
                    }
                }
            }

            // Tambahkan opsi baru
            if ($request->has('options')) {
                foreach ($request->options as $newOptionText) {
                    if (trim($newOptionText) !== '') {
                        $polling->options()->create([
                            'option_text' => $newOptionText
                        ]);
                    }
                }
            }
        }

        return redirect()->route('announcement.index')->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        // Hapus lampiran
        if ($announcement->attachment_file && Storage::exists('public/announcement/' . $announcement->attachment_file)) {
            Storage::delete('public/announcement/' . $announcement->attachment_file);
        }

        $announcement->delete();

        return redirect()->route('announcement.index')->with('success', 'Pengumuman berhasil dihapus');
    }
}