<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Employee::whereNotNull('separation_date')
            ->where('separation_date', '<', Carbon::today())
            ->where('status', '!=', 'Tidak Aktif')
            ->update(['status' => 'Tidak Aktif']);

        $query = Employee::where('status', 'Aktif');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        $employees = $query->latest()->paginate(9)->withQueryString();
        return view('employees.data.index', compact('employees', 'divisions', 'positions'));
    }

    public function indexCareer(Request $request)
    {
        $query = Employee::query();
        // Employee::whereNotNull('separation_date')
        //     ->where('separation_date', '<', Carbon::today())
        //     ->where('status', '!=', 'Tidak Aktif')
        //     ->update(['status' => 'Tidak Aktif']);

        $query = Employee::where('status', 'Aktif');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        $employees = $query->latest()->paginate(9)->withQueryString();
        return view('career-path.indexCareer', compact('employees', 'divisions', 'positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();
        $users = User::whereDoesntHave('employee')->orderBy('name')->get();
        return view('employees.data.create', compact('divisions', 'positions', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'nik' => 'required|string|size:16|unique:employees,nik|regex:/^[0-9]+$/',
            'nip' => 'nullable|string|max:20|unique:employees,nip|regex:/^[0-9]+$/',
            'npwp' => 'nullable|string|max:20|unique:employees,npwp|regex:/^[0-9]+$/',
            'full_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => ['required', 'string', 'max:20', 'unique:employees,phone_number', 'regex:/^\+?[0-9]{8,20}$/'],
            'email' => 'required|email|max:100|unique:employees,email',
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'office' => ['nullable', Rule::in(['Kantor Pusat', 'Kantor Cabang'])],
            'hire_date' => 'required|date',
            'separation_date' => 'nullable|date|after_or_equal:hire_date',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => 'nullable|unique:employees,user_id|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('cv_file')) {
                $file = $request->file('cv_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validateData['cv_file'] = $file->storeAs('cv', $filename, 'public');
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validateData['photo'] = $file->storeAs('photo', $filename, 'public');
            }

            Employee::create($validateData);

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file yang sudah terupload jika gagal
            if (isset($validateData['cv_file'])) {
                Storage::disk('public')->delete($validateData['cv_file']);
            }
            if (isset($validateData['photo'])) {
                Storage::disk('public')->delete($validateData['photo']);
            }

            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $age = null;
        if ($employee->birth_date) {
            $age = Carbon::parse($employee->birth_date)->age;
        }
        $healthRecord = $employee->healthRecord;
        $educationHistories = $employee->educationHistory;
        $dependents = $employee->familyDependents;
        $certifications = $employee->certifications;
        $insurances = $employee->insurance;
        $workExperiences = $employee->workExperience;
        $trainingHistories = $employee->trainingHistories;
        return view('employees.data.show', compact('employee', 'age', 'healthRecord', 'educationHistories', 'dependents', 'certifications', 'insurances', 'workExperiences', 'trainingHistories' ));
    }

    /**
     * Display the specified resource for career path details.
     */
    public function showCareer(Employee $employee)
    {
        $careerHistories = $employee->careerHistories()->with(['position', 'division'])->get();
        $careerProjection = $employee->careerProjection()->with(['projectedPosition', 'creator'])->first();
        return view('career-path.showCareer', compact('employee', 'careerHistories', 'careerProjection'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();
        $users = User::whereDoesntHave('employee')->orWhere('id', $employee->user_id)->orderBy('name')->get();
        return view('employees.data.edit', compact('employee', 'divisions', 'positions', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'nik' => ['required', 'string', 'size:16', Rule::unique('employees')->ignore($employee->id), 'regex:/^[0-9]+$/'],
            'nip' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id), 'regex:/^[0-9]+$/'],
            'npwp' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id), 'regex:/^[0-9]+$/'],
            'full_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id), 'regex:/^\+?[0-9]{8,20}$/'],
            'email' => ['required', 'email', 'max:100', Rule::unique('employees')->ignore($employee->id)],
            'status' => ['required', Rule::in(['Aktif', 'Tidak Aktif'])],
            'employee_type' => ['required', Rule::in(['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'])],
            'office' => ['nullable', Rule::in(['Kantor Pusat', 'Kantor Cabang'])],
            'hire_date' => 'required|date',
            'separation_date' => 'nullable|date|after_or_equal:hire_date',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'division_id' => 'nullable|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('employees')->ignore($employee->id)],
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('cv_file')) {
                if ($employee->cv_file) {
                    Storage::disk('public')->delete($employee->cv_file);
                }

                $file = $request->file('cv_file');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validatedData['cv_file'] = $file->storeAs('cv', $filename, 'public');
            }

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::disk('public')->delete($employee->photo);
                }

                $file = $request->file('photo');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $validatedData['photo'] = $file->storeAs('photo', $filename, 'public');
            }

            $employee->update($validatedData);

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();

            if ($employee->cv_file) {
                Storage::delete('public/cv/' . $employee->cv_file);
            }

            if ($employee->photo) {
                Storage::delete('public/photo/' . $employee->photo);
            }

            $employee->delete();
            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('employees.index')->with('error', 'Gagal menghapus data karyawan. Mungkin data ini masih terhubung dengan data lain.');
        }
    }

    public function deactivate(Employee $employee): RedirectResponse
    {
        if ($employee->status !== 'Tidak Aktif') {
            $employee->status = 'Tidak Aktif';
            $employee->separation_date = now();
            $employee->save();

            return redirect()->back()->with('success', 'Status karyawan berhasil diubah menjadi Tidak Aktif.');
        }

        return redirect()->back()->with('info', 'Karyawan sudah berstatus Tidak Aktif.');
    }

    public function editAddress(Employee $employee)
    {
        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();
        $users = User::whereDoesntHave('employee')->orWhere('id', $employee->user_id)->orderBy('name')->get();

        return view('employees.data.edit', compact('employee', 'divisions', 'positions', 'users'));
    }
}
