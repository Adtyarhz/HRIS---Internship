<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Division;
use Carbon\Carbon;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\NewApplicantNotification;
use App\Models\RecruitmentProgress;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantController extends Controller
{
  public function index(Request $request)
{
    $search = $request->input('search');
    $filterStage = $request->input('stage');
    $filterDivision = $request->input('division_id');
    $sortBy = $request->input('sort', 'desc'); // default sort by newest
    $direction = $sortBy === 'asc' ? 'asc' : 'desc'; // pastikan cuma asc/desc

    $user = auth()->user();
    $divisionId = optional($user->employee)->division_id;

    // Tahapan recruitment
    $stages = [
        'general_knowledge_test',
        'computer_skills_test',
        'hc_interview',
        'user_assessment',
        'bod_interview',
        'offering_letter',
    ];

    $applicants = Applicant::with(['division', 'position', 'recruitmentProgresses'])
        ->get()
        ->filter(function ($applicant) use ($filterStage, $filterDivision, $search, $user, $divisionId, $stages) {

            // 🔍 Filter pencarian nama
            if ($search && stripos($applicant->full_name, $search) === false) {
                return false;
            }

            // 👥 Filter role manager/section_head berdasarkan divisi
            if (in_array($user->role, ['manager', 'section_head']) && $applicant->division_id !== $divisionId) {
                return false;
            }

            // 🏢 Filter division
            if ($filterDivision && $filterDivision !== '') {
                if ($applicant->division_id != $filterDivision) {
                    return false;
                }
            }

            // 🎯 Tentukan current stage berdasarkan logika dari exportCsv
            $progresses = $applicant->recruitmentProgresses->keyBy('stage');
            $currentStage = null;
            $rejected = false;

            foreach ($stages as $index => $stage) {
                $progress = $progresses[$stage] ?? null;
                $status = $progress?->offering_status;

                if ($status === 'rejected') {
                    $rejected = true;
                    break;
                }

                if ($status === 'accepted') {
                    // Kalau offering letter accepted, tetap di offering_letter
                    if ($stage === 'offering_letter') {
                        $currentStage = 'offering_letter';
                    } else {
                        // lanjut ke stage berikutnya (kalau ada)
                        $currentStage = $stages[$index + 1] ?? 'offering_letter';
                    }
                } elseif ($status === 'in_progress') {
                    $currentStage = $stage;
                    break;
                } elseif (!$status && $index === 0) {
                    // Jika belum ada progress sama sekali
                    $currentStage = $stages[0];
                    break;
                }
            }

            if ($rejected) {
                $currentStage = 'rejected';
            }

            // Simpan stage hasil perhitungan (untuk ditampilkan di tabel jika mau)
            $applicant->computed_stage = $currentStage;

            // 🔎 Filter berdasarkan stage (jika user memilih filterStage)
            if ($filterStage && $filterStage !== $currentStage) {
                return false;
            }

            return true;
        });

    // ⚙️ Urutkan berdasarkan tanggal
    $applicants = $sortBy === 'asc'
        ? $applicants->sortBy('created_at')
        : $applicants->sortByDesc('created_at');

    // 📄 Pagination manual
    $perPage = 10;
    $page = $request->get('page', 1);
    $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
        $applicants->forPage($page, $perPage)->values(),
        $applicants->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $divisions = Division::all();

    return view('applicants.index', [
        'applicants' => $paginated,
        'divisions' => $divisions,
        'search' => $search,
        'filterStage' => $filterStage,
        'filterDivision' => $filterDivision,
        'sortBy' => $sortBy,
        'direction' => $direction,
    ]);
}

    public function create()
{
    $user = auth()->user();

    // Batasi hanya untuk HC dan Superadmin
    if (!in_array($user->role, ['hc', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }

    $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
    $positions = Position::all();

    return view('applicants.create', compact('divisions', 'positions'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email',
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required',
            'resume_file' => 'required|file|mimes:pdf,doc,docx',
            'applied_position' => 'required|exists:positions,id',
            'last_education' => 'required|string',
            'origin' => 'required|string',
            'gpa_score' => 'required|numeric',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['resume_file'] = $file->storeAs('resumes', $filename, 'public');
        }

        $applicant = Applicant::create($validated);

        $managers = User::whereHas('employee', function($q) use ($applicant) {
            $q->where('division_id', $applicant->division_id);
        })->where('role', 'manager')->get();

        if ($managers->isEmpty()) {
            $divisionHeads = User::whereHas('employee', function($q) use ($applicant) {
                $q->where('division_id', $applicant->division_id);
            })->where('role', 'section_head')->get();
        } else {
            $divisionHeads = $managers;
        }

        $hcUsers = User::admins()->get();
        foreach ($hcUsers->merge($divisionHeads) as $user) {
            $user->notify(new NewApplicantNotification($applicant));
        }

        return redirect()->route('applicants.index')->with('success', 'Applicant added successfully!');
    }

    public function edit(Applicant $applicant)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['superadmin', 'hc'])) abort(403);
        $divisions = Division::where('name', '!=', 'N/A')->orderBy('name')->get();
        $positions = Position::all();
        return view('applicants.edit', compact('applicant', 'divisions', 'positions'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:applicants,email,' . $applicant->id,
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required',
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx',
            'applied_position' => 'required|exists:positions,id',
            'last_education' => 'required|string',
            'origin' => 'required|string',
            'gpa_score' => 'required|numeric',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $validated['resume_file'] = $file->storeAs('resumes', $filename, 'public');
        }

        $applicant->update($validated);

        return redirect()->route('applicants.index')->with('success', 'Applicant updated successfully!');
    }

   public function show($id)
{
    // Ambil applicant + relasi division (jika ada)
    $applicant = Applicant::with('division')->findOrFail($id);
    $offeringLetter = $applicant->offeringLetter;
    $user = auth()->user();

    // 🔒 Hanya role tertentu yang boleh melihat semua data
    if (!in_array($user->role, ['superadmin', 'hc', 'direksi'])) {

        // Ambil divisi dari user login
        $userDivisionId = optional($user->employee)->division_id;

        // Ambil divisi dari applicant
        $applicantDivisionId = $applicant->division_id;

        // Manager & section head hanya bisa lihat applicant di divisinya
        if (in_array($user->role, ['manager', 'section_head'])) {
            if ($userDivisionId !== $applicantDivisionId) {
                abort(403, 'Anda tidak memiliki izin untuk melihat pelamar dari divisi lain.');
            }
        } else {
            // Role lain tidak boleh sama sekali
            abort(403);
        }
    }

    // 🔍 Cek apakah applicant sudah jadi employee
    $isConverted = \App\Models\Employee::where('full_name', $applicant->full_name)
        ->where('email', $applicant->email)
        ->where('phone_number', $applicant->phone)
        ->exists();

    return view('applicants.show', compact('applicant', 'offeringLetter', 'isConverted'));
}

    public function destroy(Applicant $applicant)
    {   
        $user = auth()->user();
        if (!in_array($user->role, ['superadmin', 'hc'])) abort(403);
        $applicant->delete();
        return redirect()->route('applicants.index')->with('success', 'Applicant deleted successfully!');
    }

    public function convertToEmployee($id)
    {   
        $user = auth()->user();
        if (!in_array($user->role, ['superadmin', 'hc'])) abort(403);
        $applicant = Applicant::with('position')->findOrFail($id);

        $offeringLetter = $applicant->recruitmentProgresses()
            ->where('stage', 'offering_letter')
            ->first();

        if (!$offeringLetter || $offeringLetter->offering_status !== 'accepted') {
            return redirect()->back()->with('error', 'Applicant cannot be converted to employee because the offering letter is not accepted.');
        }

        if ($applicant->employee) {
            return redirect()->back()->with('info', 'This applicant is already registered as an employee.');
        }

        $nik = 'EMP' . date('Y') . str_pad($applicant->id, 4, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'applicant_id'  => $applicant->id,
            'nik'           => $nik,
            'full_name'     => $applicant->full_name,
            'email'         => $applicant->email,
            'position_id'   => $applicant->applied_position,
            'contract_type' => $offeringLetter->contract_type ?? null,
            'start_date'    => now(),
        ]);

        return redirect()
            ->route('employees.show', $employee->id)
            ->with('success', 'Applicant has been successfully converted to employee.');
    }
    public function exportCsv(Request $request)
{
    $filterDivision = $request->input('division_id'); // ambil filter divisi dari request
    $stages = [
        'general_knowledge_test',
        'computer_skills_test',
        'hc_interview',
        'user_assessment',
        'bod_interview',
        'offering_letter',
    ];

    $filename = 'recruitment_report_' . now()->format('Ymd_His') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    // ✅ Jika filter divisi dipilih (bukan "All Divisions")
    if ($filterDivision && $filterDivision !== 'all') {
        $division = Division::with(['applicants.recruitmentProgresses', 'applicants.position'])
            ->findOrFail($filterDivision);

        $callback = function () use ($division, $stages) {
            echo "\xEF\xBB\xBF"; // BOM UTF-8 agar bisa dibuka di Excel
            $handle = fopen('php://output', 'w');
            $delimiter = ';';

            // Header CSV (format detail per pelamar)
            fputcsv($handle, [
                'Full Name',
                'Applied Position',
                'Current Stage',
                'Email',
                'Phone',
                'Address',
                'Last Education',
                'Institution Name',
                'GPA / Score',
            ], $delimiter);

            foreach ($division->applicants as $applicant) {
                $progresses = $applicant->recruitmentProgresses->keyBy('stage');

                // 🔹 Tentukan current stage (logika sama seperti exportCsv sebelumnya)
                $currentStage = null;
                $hasRejected = false;
                $lastAcceptedIndex = -1;

                foreach ($stages as $i => $stage) {
                    if (!isset($progresses[$stage])) continue;

                    $status = $progresses[$stage]->offering_status;

                    if ($status === 'rejected') {
                        $hasRejected = true;
                        break;
                    }

                    if ($status === 'accepted') {
                        $lastAcceptedIndex = $i;
                    } elseif ($status === 'in_progress') {
                        $currentStage = $stage;
                        break;
                    }
                }

                if ($hasRejected) {
                    $currentStage = 'rejected';
                } elseif ($currentStage === null) {
                    if ($lastAcceptedIndex === count($stages) - 1) {
                        $currentStage = 'offering_letter';
                    } elseif ($lastAcceptedIndex >= 0) {
                        $currentStage = $stages[$lastAcceptedIndex + 1] ?? 'offering_letter';
                    } else {
                        $currentStage = $stages[0];
                    }
                }

                // 🔹 Tulis data pelamar ke CSV
                fputcsv($handle, [
                    $applicant->full_name,
                    optional($applicant->position)->title ?? '-',
                    ucfirst(str_replace('_', ' ', $currentStage)),
                    $applicant->email,
                    "\t" . $applicant->phone,
                    $applicant->address,
                    $applicant->last_education,
                    $applicant->origin,
                    $applicant->gpa_score,
                ], $delimiter);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    // ✅ Jika tidak ada filter divisi (All Divisions)
    $divisions = Division::with(['applicants.recruitmentProgresses'])->get();

    $callback = function () use ($divisions, $stages) {
        echo "\xEF\xBB\xBF";
        $handle = fopen('php://output', 'w');
        $delimiter = ';';

        // Header rekap per divisi
        fputcsv($handle, [
            'Division',
            'Total Applicants',
            'General Knowledge Test',
            'Computer Skills Test',
            'HC Interview',
            'User Assessment',
            'BOD Interview',
            'Offering Letter',
            'Rejected',
        ], $delimiter);

        foreach ($divisions as $division) {
            $totalApplicants = $division->applicants->count();

            $counts = [
                'general_knowledge_test' => 0,
                'computer_skills_test' => 0,
                'hc_interview' => 0,
                'user_assessment' => 0,
                'bod_interview' => 0,
                'offering_letter' => 0,
                'rejected' => 0,
            ];

            foreach ($division->applicants as $applicant) {
                $progresses = $applicant->recruitmentProgresses->keyBy('stage');

                $currentStage = null;
                $hasRejected = false;
                $lastAcceptedIndex = -1;

                foreach ($stages as $i => $stage) {
                    if (!isset($progresses[$stage])) continue;
                    $status = $progresses[$stage]->offering_status;

                    if ($status === 'rejected') {
                        $hasRejected = true;
                        break;
                    }

                    if ($status === 'accepted') {
                        $lastAcceptedIndex = $i;
                    }
                }

                if ($hasRejected) {
                    $currentStage = 'rejected';
                } elseif ($lastAcceptedIndex === count($stages) - 1) {
                    $currentStage = 'offering_letter';
                } elseif ($lastAcceptedIndex >= 0) {
                    $currentStage = $stages[$lastAcceptedIndex + 1] ?? 'offering_letter';
                } else {
                    foreach ($stages as $stage) {
                        if (isset($progresses[$stage])) {
                            $currentStage = $stage;
                            break;
                        }
                    }
                }

                if ($currentStage === 'rejected') {
                    $counts['rejected']++;
                } elseif ($currentStage && isset($counts[$currentStage])) {
                    $counts[$currentStage]++;
                }
            }

            fputcsv($handle, [
                $division->name,
                $totalApplicants,
                $counts['general_knowledge_test'],
                $counts['computer_skills_test'],
                $counts['hc_interview'],
                $counts['user_assessment'],
                $counts['bod_interview'],
                $counts['offering_letter'],
                $counts['rejected'],
            ], $delimiter);
        }

        fclose($handle);
    };

    return new StreamedResponse($callback, 200, $headers);
}

}

