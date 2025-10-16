<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function index()
    {
        // $this->authorizeRole(['superadmin', 'hc']);
        $divisions = Division::withCount(['employees', 'careerHistories'])
            ->orderBy('name')
            ->paginate(10);
        return view('organization.division.index', compact('divisions'));
    }

    public function create()
    {
        $this->authorizeRole(['superadmin', 'hc']);
        return view('organization.division.create');
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
        ]);

        DB::beginTransaction();
        try {
            Division::create($validated);
            DB::commit();
            return redirect()->route('organization.division.index')->with('success', 'Divisi baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan divisi: ' . $e->getMessage());
        }
    }

    public function edit(Division $division)
    {
        $this->authorizeRole(['superadmin', 'hc']);
        return view('organization.division.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('divisions')->ignore($division->id)],
        ]);

        DB::beginTransaction();
        try {
            $division->update($validated);
            DB::commit();
            return redirect()->route('organization.division.index')->with('success', 'Divisi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui divisi: ' . $e->getMessage());
        }
    }

    public function destroy(Division $division)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        if ($division->employees()->exists()) {
            return back()->with('error', 'Divisi tidak dapat dihapus karena masih memiliki karyawan.');
        }

        DB::beginTransaction();
        try {
            $division->delete();
            DB::commit();
            return redirect()->route('organization.division.index')->with('success', 'Divisi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus divisi: ' . $e->getMessage());
        }
    }

    private function authorizeRole(array $roles)
    {
        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }
}
