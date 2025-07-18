<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FamilyDependent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class FamilyDependentController extends Controller
{
    /**
     * Menampilkan daftar tanggungan keluarga untuk karyawan tertentu.
     */
    public function index(Employee $employee)
    {
        $dependents = $employee->familyDependents()->latest()->get();
        return view('employees.family-dependents.index', compact('employee', 'dependents'));
    }

    /**
     * Menampilkan form untuk menambahkan tanggungan keluarga baru.
     */
    public function create(Employee $employee)
    {
        return view('employees.family-dependents.create', compact('employee'));
    }

    /**
     * Menyimpan tanggungan keluarga baru ke dalam database.
     */
    public function store(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'phone_number' => ['required', 'string', 'max:20', 'unique:family_dependents, phone_number', 'regex:/^\+?[0-9]{8,20}$/'],
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
        ]);

        try {
            $employee->familyDependents()->create($validatedData);

            return redirect()->route('employees.family-dependents.index', $employee->id)
                             ->with('success', 'Data tanggungan keluarga berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit tanggungan keluarga.
     */
    public function edit(Employee $employee, FamilyDependent $familyDependent)
    {
        return view('employees.family-dependents.edit', compact('employee', 'familyDependent'));
    }

    /**
     * Memperbarui data tanggungan keluarga di database.
     */
    public function update(Request $request, Employee $employee, FamilyDependent $familyDependent)
    {
        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('family_dependents')->ignore($familyDependent->id), 'regex:/^\+?[0-9]{8,20}$/'],
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
        ]);

        try {
            $familyDependent->update($validatedData);

            return redirect()->route('employees.family-dependents.index', $employee->id)
                             ->with('success', 'Data tanggungan keluarga berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus data tanggungan keluarga.
     */
    public function destroy(Employee $employee, FamilyDependent $familyDependent)
    {
        try {
            $familyDependent->delete();
            return redirect()->route('employees.family-dependents.index', $employee->id)
                             ->with('success', 'Data tanggungan keluarga berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }
}
