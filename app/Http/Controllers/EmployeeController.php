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

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%')
                    ->orWhere('nip', 'like', '%' . $search . '%')
                    ->orWhere('npwp', 'like', '%' . $search . '%');
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        $divisions = Division::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        $employees = $query->latest()->paginate(9)->withQueryString();
        return view('employees.data.index', compact('employees', 'divisions', 'positions'));
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
            'nik' => 'required|string|max:20|unique:employees,nik',
            'nip' => 'nullable|string|max:20|unique:employees,nip',
            'npwp' => 'nullable|string|max:20|unique:employees,npwp',
            'full_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => 'required|string|max:20|unique:employees,phone_number',
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
                $cvFile = $request->file('cv_file');
                $cvFileName = time() . '_' . $cvFile->getClientOriginalName();
                $cvFile->storeAs('public/cv', $cvFileName);
                $validateData['cv_file'] = $cvFileName;
            }

            if ($request->hasFile('photo')) {
                $photoFile = $request->file('photo');
                $photoFileName = time() . '_' . $photoFile->getClientOriginalName();
                $photoFile->storeAs('public/photo', $photoFileName);
                $validateData['photo'] = $photoFileName;
            }

            Employee::create($validateData);
            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($validateData['cv_file']))
                Storage::delete('public/cv/' . $validateData['cv_file']);
            if (isset($validateData['photo']))
                Storage::delete('public/photo/' . $validateData['photo']);

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
        return view('employees.data.show', compact('employee', 'age'));
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
            'nik' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'nip' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'npwp' => ['nullable', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'full_name' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'religion' => 'required|string|max:50',
            'birth_place' => 'required|string|max:50',
            'birth_date' => 'required|date',
            'marital_status' => ['required', Rule::in(['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'])],
            'dependents' => 'required|integer|min:0',
            'ktp_address' => 'required|string',
            'current_address' => 'required|string',
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
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
                    Storage::delete('public/cv/' . $employee->cv_file);
                }
                $cvFile = $request->file('cv_file');
                $cvFileName = time() . '_' . $cvFile->getClientOriginalName();
                $cvFile->storeAs('public/cv', $cvFileName);
                $validatedData['cv_file'] = $cvFileName;
            }

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::delete('public/photo/' . $employee->photo);
                }
                $photoFile = $request->file('photo');
                $photoFileName = time() . '_' . $photoFile->getClientOriginalName();
                $photoFile->storeAs('public/photo', $photoFileName);
                $validatedData['photo'] = $photoFileName;
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
}
