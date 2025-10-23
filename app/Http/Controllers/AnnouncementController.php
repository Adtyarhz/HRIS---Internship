<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Polling;
use App\Models\Division;
use App\Models\Employee;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementController extends Controller
{
    /**
     * Memastikan hanya role tertentu yang bisa mengakses aksi create, update, destroy.
     */
    private function authorizeAccess()
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'hc'])) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }
    }

    public function dashboard()
    {
        try {
            $user = auth()->user();
            $role = $user->role;
            $divisionId = $user->employee->division_id ?? null;

            if (in_array($role, ['hc', 'superadmin'])) {
                $announcements = Announcement::latest()->paginate(20);
            } else {
                $announcements = Announcement::where(function ($q) use ($divisionId) {
                    $q->whereHas('targetDivisions', function ($q2) use ($divisionId) {
                        $q2->where('divisions.id', $divisionId);
                    })->orDoesntHave('targetDivisions');
                })->latest()->paginate(20);
            }

            $genderStats = collect();
            $divisionStats = collect();

            if (in_array($role, ['superadmin', 'hc', 'direksi'])) {
                $genderStats = Employee::selectRaw('gender, COUNT(*) as total')
                    ->groupBy('gender')
                    ->pluck('total', 'gender')
                    ->mapWithKeys(function ($value, $key) {
                        $englishKey = match (strtolower($key)) {
                            'laki-laki' => 'Male',
                            'perempuan' => 'Female',
                            default => ucfirst($key),
                        };
                        return [$englishKey => $value];
                    });

                $divisionStats = Division::where('name', '!=', 'N/A')->withCount('employees')->get();
            } elseif ($role === 'manager') {
                $divisionId = $user->employee->division_id ?? null;
                if ($divisionId) {
                    $genderStats = Employee::where('division_id', $divisionId)
                        ->selectRaw('gender, COUNT(*) as total')
                        ->groupBy('gender')
                        ->pluck('total', 'gender')
                        ->mapWithKeys(function ($value, $key) {
                            $englishKey = match (strtolower($key)) {
                                'laki-laki' => 'Male',
                                'perempuan' => 'Female',
                                default => ucfirst($key),
                            };
                            return [$englishKey => $value];
                        });
                }
            } elseif ($role === 'section_head') {
                $divisionId = $user->employee->division_id ?? null;
                if ($divisionId) {
                    $hasManager = User::where('role', 'manager')
                        ->whereHas('employee', function ($q) use ($divisionId) {
                            $q->where('division_id', $divisionId);
                        })->exists();
                    if (!$hasManager) {
                        $genderStats = Employee::where('division_id', $divisionId)
                            ->selectRaw('gender, COUNT(*) as total')
                            ->groupBy('gender')
                            ->pluck('total', 'gender')
                            ->mapWithKeys(function ($value, $key) {
                                $englishKey = match (strtolower($key)) {
                                    'laki-laki' => 'Male',
                                    'perempuan' => 'Female',
                                    default => ucfirst($key),
                                };
                                return [$englishKey => $value];
                            });
                    }
                }
            }

            return view('dashboard', compact('announcements', 'genderStats', 'divisionStats'));
        } catch (\Exception $e) {
            Log::error('Error loading dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while loading the dashboard.');
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Announcement::query();
            $user = auth()->user();
            $role = $user->role;
            $divisionId = $user->employee->division_id ?? null;

            if (!in_array($role, ['hc', 'superadmin'])) {
                $query->where(function ($q) use ($divisionId) {
                    $q->whereHas('targetDivisions', function ($q2) use ($divisionId) {
                        $q2->where('divisions.id', $divisionId);
                    })->orDoesntHave('targetDivisions');
                });
            }

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
            return redirect()->back()->with('error', 'An error occurred while loading the announcement list.');
        }
    }

    public function create()
    {
        $this->authorizeAccess();
        $divisions = Division::orderBy('name')->get();
        return view('announcement.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'announcement_type' => 'required|in:Umum,Urgent,Informasi,Polling',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'external_link' => 'nullable|array',
            'external_link.*' => 'nullable|url',
            'label' => 'required|string|max:50',
            'target_divisions' => 'nullable|array',
            'target_divisions.*' => 'exists:divisions,id',
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

        $user = Auth::user();
        DB::beginTransaction();

        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $file = $request->file('attachment_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $user->role === 'hc' ? 'temp/announcement/' : 'announcement/';
                $attachmentPath = $file->storeAs($path, $filename, 'public');
            }

            if ($user->role === 'hc') {
                $announcementData = [
                    'created_by' => $user->id,
                    'title' => $request->title,
                    'content' => $request->content,
                    'announcement_type' => $request->announcement_type,
                    'attachment_file' => $attachmentPath,
                    'label' => $request->label,
                    'external_link' => $request->external_link,
                ];

                $tempModel = new Announcement($announcementData);
                $extraData = [
                    'target_divisions' => $request->filled('target_divisions')
                        ? Division::whereIn('id', $request->target_divisions)->get(['id', 'name'])->toArray()
                        : [],
                    'attachment_file' => $attachmentPath,
                ];

                if ($request->announcement_type === 'Polling') {
                    $extraData['polling'] = [
                        'deadline' => $request->deadline,
                        'created_by' => $user->id,
                        'options' => array_map(function ($option) {
                            return ['option_text' => $option];
                        }, $request->options ?? []),
                    ];
                }

                $cdr = ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create', $extraData);
                if (!$cdr) {
                    DB::rollBack();
                    return redirect()->route('announcement.index')->with('error', 'Gagal membuat permintaan pembuatan pengumuman.');
                }

                DB::commit();
                return redirect()->route('announcement.index')->with('success', 'Permintaan pembuatan pengumuman telah dikirim untuk approval.');
            }

            $announcement = Announcement::create([
                'created_by' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'announcement_type' => $request->announcement_type,
                'attachment_file' => $attachmentPath,
                'label' => $request->label,
                'external_link' => $request->external_link,
            ]);

            if ($request->filled('target_divisions')) {
                $announcement->targetDivisions()->sync($request->target_divisions);
            }

            if ($request->announcement_type === 'Polling') {
                $polling = $announcement->polling()->create([
                    'deadline' => $request->deadline,
                    'created_by' => $user->id,
                ]);

                if ($request->has('options')) {
                    foreach ($request->options as $option) {
                        if (trim($option) !== '') {
                            $polling->options()->create(['option_text' => $option]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('announcement.index')->with('success', 'Announcement has been created');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing announcement: ' . $e->getMessage());
            return redirect()->route('announcement.index')->with('error', 'Gagal membuat pengumuman: ' . $e->getMessage());
        }
    }

    public function show($id, Request $request)
    {
        $announcement = Announcement::with('polling.options.votes', 'targetDivisions')->findOrFail($id);
        $now = now();
        $deadline = optional($announcement->polling)->deadline;
        $isExpired = $deadline && $now->greaterThan($deadline);
        $from = $request->query('from');

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
        $this->authorizeAccess();
        $announcement = Announcement::with(['polling.options', 'targetDivisions'])->findOrFail($id);
        $divisions = Division::orderBy('name')->get();
        return view('announcement.edit', compact('announcement', 'divisions'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAccess();
        $announcement = Announcement::findOrFail($id);
        $polling = $announcement->polling;
        $isPolling = $announcement->announcement_type === 'Polling';
        $isExpired = $polling && $polling->deadline && now()->gt($polling->deadline);
        $isLocked = $polling && $polling->is_locked;
        $hasVotes = $polling && $polling->options()->withCount('votes')->get()->sum('votes_count') > 0;

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'announcement_type' => 'required|in:Umum,Urgent,Informasi,Polling',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'external_link' => 'nullable|array',
            'external_link.*' => 'nullable|url',
            'label' => 'required|string|max:50',
            'target_divisions' => 'nullable|array',
            'target_divisions.*' => 'exists:divisions,id',
            'existing_options' => 'nullable|array',
            'existing_options.*' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string|min:1',
            'deleted_options' => 'nullable|array',
        ];

        if ($isPolling && !$isExpired && !$isLocked && !$hasVotes) {
            $rules['deadline'] = 'required_if:announcement_type,Polling|date|after:now';
            $rules['options'] = [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->announcement_type === 'Polling') {
                        $allOptions = array_merge(
                            $value ?? [],
                            $request->existing_options ? array_filter($request->existing_options, fn($opt) => trim($opt) !== '') : []
                        );
                        if (count($allOptions) < 1) {
                            $fail('Minimal satu opsi polling harus diisi.');
                        }
                    }
                },
            ];
        }

        $request->validate($rules);

        $user = Auth::user();
        DB::beginTransaction();

        try {
            if ($user->role === 'hc') {
                $attachmentPath = $announcement->attachment_file;
                if ($request->hasFile('attachment_file')) {
                    if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                        Storage::disk('public')->delete($attachmentPath);
                    }
                    $file = $request->file('attachment_file');
                    $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('temp/announcement/', $filename, 'public');
                }

                $announcementData = [
                    'created_by' => $announcement->created_by,
                    'title' => $request->title,
                    'content' => $request->content,
                    'announcement_type' => $request->announcement_type,
                    'attachment_file' => $attachmentPath,
                    'label' => $request->label,
                    'external_link' => $request->external_link,
                ];

                $tempModel = $announcement->replicate()->fill($announcementData);
                $extraData = [
                    'target_divisions' => $request->filled('target_divisions')
                        ? Division::whereIn('id', $request->target_divisions)->get(['id', 'name'])->toArray()
                        : [],
                    'attachment_file' => $attachmentPath,
                ];

                if ($request->announcement_type === 'Polling') {
                    $extraData['polling'] = [
                        'id' => $polling ? $polling->id : null,
                        'deadline' => $request->deadline,
                        'created_by' => $announcement->created_by,
                        'options' => [],
                    ];

                    if ($request->has('existing_options')) {
                        foreach ($request->existing_options as $optionId => $optionText) {
                            if (trim($optionText) !== '') {
                                $extraData['polling']['options'][] = [
                                    'id' => $optionId,
                                    'option_text' => $optionText,
                                ];
                            }
                        }
                    }

                    if ($request->has('options')) {
                        foreach ($request->options as $option) {
                            if (trim($option) !== '') {
                                $extraData['polling']['options'][] = [
                                    'option_text' => $option,
                                ];
                            }
                        }
                    }

                    if ($request->has('deleted_options')) {
                        $extraData['polling']['deleted_options'] = $request->deleted_options;
                    }
                }

                $cdr = ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update', $extraData);
                if (!$cdr) {
                    DB::rollBack();
                    return redirect()->route('announcement.index')->with('error', 'Gagal membuat permintaan pembaruan pengumuman.');
                }

                DB::commit();
                return redirect()->route('announcement.index')->with('success', 'Permintaan pembaruan pengumuman telah dikirim untuk approval.');
            }

            $attachmentPath = $announcement->attachment_file;
            if ($request->hasFile('attachment_file')) {
                if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $file = $request->file('attachment_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $attachmentPath = $file->storeAs('announcement/', $filename, 'public');
            }

            $announcement->update([
                'title' => $request->title,
                'content' => $request->content,
                'announcement_type' => $request->announcement_type,
                'attachment_file' => $attachmentPath,
                'label' => $request->label,
                'external_link' => $request->external_link,
            ]);

            if ($request->filled('target_divisions')) {
                $announcement->targetDivisions()->sync($request->target_divisions);
            } else {
                $announcement->targetDivisions()->detach();
            }

            if ($announcement->announcement_type === 'Polling' && $polling) {
                if (!($polling->is_locked || $isExpired || $hasVotes)) {
                    $polling->update(['deadline' => $request->deadline]);

                    if ($request->has('existing_options')) {
                        foreach ($request->existing_options as $optionId => $optionText) {
                            $option = $polling->options()->find($optionId);
                            if ($option && trim($optionText) !== '') {
                                $option->update(['option_text' => $optionText]);
                            }
                        }
                    }

                    if ($request->has('deleted_options')) {
                        foreach ($request->deleted_options as $optionId) {
                            $polling->options()->where('id', $optionId)->delete();
                        }
                    }

                    if ($request->has('options')) {
                        foreach ($request->options as $option) {
                            if (trim($option) !== '') {
                                $polling->options()->create(['option_text' => $option]);
                            }
                        }
                    }
                }
            } elseif ($announcement->announcement_type !== 'Polling' && $polling) {
                $polling->options()->delete();
                $polling->delete();
            }

            DB::commit();
            return redirect()->route('announcement.index')->with('success', 'Announcement has been updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating announcement: ' . $e->getMessage());
            return redirect()->route('announcement.index')->with('error', 'Gagal memperbarui pengumuman: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorizeAccess();
        $announcement = Announcement::findOrFail($id);
        $user = Auth::user();

        DB::beginTransaction();
        try {
            if ($user->role === 'hc') {
                $extraData = [
                    'attachment_file' => $announcement->attachment_file,
                    'target_divisions' => $announcement->targetDivisions()->get(['divisions.id', 'divisions.name'])->toArray(),
                ];
                if ($announcement->polling) {
                    $extraData['polling'] = [
                        'id' => $announcement->polling->id,
                        'deadline' => $announcement->polling->deadline,
                        'created_by' => $announcement->polling->created_by,
                        'options' => $announcement->polling->options()->get(['id', 'option_text'])->toArray(),
                    ];
                }

                $cdr = ApprovalWorkflowService::captureModelChange($user, $announcement, 'delete', $extraData);
                if (!$cdr) {
                    DB::rollBack();
                    return redirect()->route('announcement.index')->with('error', 'Gagal membuat permintaan penghapusan pengumuman.');
                }

                DB::commit();
                return redirect()->route('announcement.index')->with('success', 'Permintaan penghapusan pengumuman telah dikirim untuk approval.');
            }

            if ($announcement->attachment_file && Storage::disk('public')->exists($announcement->attachment_file)) {
                Storage::disk('public')->delete($announcement->attachment_file);
            }

            $announcement->delete();
            DB::commit();
            return redirect()->route('announcement.index')->with('success', 'Announcement has been deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting announcement: ' . $e->getMessage());
            return redirect()->route('announcement.index')->with('error', 'Gagal menghapus pengumuman: ' . $e->getMessage());
        }
    }

    public function exportPolling($id)
    {
        $announcement = Announcement::with('polling.options.votes.creator')->findOrFail($id);

        if (!$announcement->polling || now()->lt($announcement->polling->deadline)) {
            return redirect()->back()->with('error', 'Poll not finished or not found.');
        }

        $filename = 'hasil_polling_' . Str::slug($announcement->title) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($announcement) {
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'w');
            $delimiter = ';';
            fputcsv($handle, ['Opsi', 'Jumlah Suara', 'Nama Voter'], $delimiter);

            foreach ($announcement->polling->options as $option) {
                $voters = $option->votes->map(fn($vote) => optional($vote->creator)->name)->filter()->toArray();
                $voterNames = !empty($voters) ? implode(' | ', $voters) : '-';
                fputcsv($handle, [
                    $option->option_text,
                    $option->votes->count(),
                    $voterNames,
                ], $delimiter);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}