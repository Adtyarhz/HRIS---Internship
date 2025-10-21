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

    $applicants = Applicant::with(['division', 'position', 'recruitmentProgresses'])
        ->get()
        ->filter(function ($applicant) use ($filterStage, $filterDivision, $search, $user, $divisionId) {

            // 🔍 Pencarian nama
            if ($search && stripos($applicant->full_name, $search) === false) {
                return false;
            }

            // 👥 Filter berdasarkan role manager/section_head
            if (in_array($user->role, ['manager', 'section_head']) && $applicant->division_id !== $divisionId) {
                return false;
            }

            // 🏢 Filter berdasarkan dropdown division
            if ($filterDivision && $filterDivision !== '') {
                if ($applicant->division_id != $filterDivision) {
                    return false;
                }
            }

            // 🎯 Filter berdasarkan current stage
            if ($filterStage && $applicant->current_stage !== $filterStage) {
                return false;
            }

            return true;
        });

    // ⚙️ Urutkan berdasarkan tanggal
    $applicants = $sortBy === 'asc'
        ? $applicants->sortBy('created_at')
        : $applicants->sortByDesc('created_at');

    // Pagination manual
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
}

