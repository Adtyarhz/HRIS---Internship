<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ApprovalWorkflowService;

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
            // Tambahkan validasi 'description' jika ada di form
            // 'description' => 'nullable|string',
        ]);

        //-- APPROVAL LOGIC START --//
        $user = Auth::user();
        if ($user && $user->role === 'hc') {
            $tempModel = new Division($validated);
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');
            return redirect()->route('organization.division.index')->with('success', 'Permintaan penambahan divisi telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN
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
            // 'description' => 'nullable|string',
        ]);
        
        //-- APPROVAL LOGIC START (PERBAIKAN KONSISTENSI) --//
        $user = Auth::user();
        if ($user && $user->role === 'hc') {
            // Gunakan 'clone' untuk menjaga data original
            $tempModel = clone $division;
            // Isi clone dengan data baru dari validasi
            $tempModel->fill($validated);
            
            // Panggil metode public `captureModelChange`
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');
            
            return redirect()->route('organization.division.index')->with('success', 'Permintaan perubahan divisi telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN
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

        //-- APPROVAL LOGIC START --//
        $user = Auth::user();
        if ($user && $user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $division, 'delete');
            return redirect()->route('organization.division.index')->with('success', 'Permintaan penghapusan divisi telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//
        
        // Logika di bawah ini hanya berjalan untuk SUPERADMIN
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
