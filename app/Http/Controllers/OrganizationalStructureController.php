<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrganizationalStructureController extends Controller
{
    /**
     * Menampilkan halaman utama struktur organisasi dalam bentuk bagan.
     */
    public function index()
    {
        $positions = Position::with(['parent', 'children', 'indirectSupervisor', 'indirectSubordinates', 'employees'])->get();
        $chartData = $this->getChartData($positions);
        return view('organization.structure.index', compact('positions', 'chartData'));
    }

    /**
     * Menampilkan detail jabatan tertentu.
     */
    public function show(Position $position)
    {
        $position->load([
            'employees' => function ($query) {
                $query->where('status', 'Aktif')->with('division');
            }
        ]);
        return view('organization.structure.show', compact('position'));
    }

    /**
     * Menampilkan form untuk membuat jabatan baru.
     */
    public function create()
    {
        $positions = Position::orderBy('title')->get();
        return view('organization.structure.create', compact('positions'));
    }

    /**
     * Menyimpan jabatan baru ke database dengan validasi dan perhitungan depth.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:positions,title',
            'parent_id' => 'nullable|exists:positions,id',
            'indirect_supervisor_id' => 'nullable|exists:positions,id',
        ]);

        $depth = 0;
        if ($request->filled('parent_id')) {
            $parentPosition = Position::find($request->parent_id);
            $depth = $parentPosition ? $parentPosition->depth + 1 : 0;
        }

        DB::beginTransaction();
        try {
            Position::create([
                'title' => $validatedData['title'],
                'parent_id' => $validatedData['parent_id'],
                'indirect_supervisor_id' => $validatedData['indirect_supervisor_id'],
                'depth' => $depth,
            ]);
            DB::commit();
            return redirect()->route('organization.structure.index')->with('success', 'Jabatan baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form untuk mengedit jabatan.
     */
    public function edit(Position $position)
    {
        $descendantIds = $this->getDescendantIds($position);
        $possibleParents = Position::where('id', '!=', $position->id)
            ->whereNotIn('id', $descendantIds)
            ->orderBy('title')
            ->get();
        return view('organization.structure.edit', compact('position', 'possibleParents'));
    }

    /**
     * Memperbarui jabatan dengan validasi anti-loop dan pembaruan depth.
     */
    public function update(Request $request, Position $position)
    {
        $descendantIds = $this->getDescendantIds($position);
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('positions')->ignore($position->id)],
            'parent_id' => ['nullable', 'exists:positions,id', Rule::notIn(array_merge([$position->id], $descendantIds))],
            'indirect_supervisor_id' => ['nullable', 'exists:positions,id'],
        ]);

        $depth = 0;
        if ($request->filled('parent_id')) {
            $parentPosition = Position::find($request->parent_id);
            $depth = $parentPosition ? $parentPosition->depth + 1 : 0;
        }

        DB::beginTransaction();
        try {
            $position->update([
                'title' => $validatedData['title'],
                'parent_id' => $validatedData['parent_id'],
                'indirect_supervisor_id' => $validatedData['indirect_supervisor_id'],
                'depth' => $depth,
            ]);
            $this->updateChildrenDepth($position, $depth);
            DB::commit();
            return redirect()->route('organization.structure.index')->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus jabatan dengan pengecekan karyawan dan pembaruan depth anak.
     */
    public function destroy(Position $position)
    {
        if ($position->employees()->exists()) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena masih ditempati karyawan.');
        }

        DB::beginTransaction();
        try {
            foreach ($position->children as $child) {
                $child->update(['parent_id' => null, 'depth' => 0]);
                $this->updateChildrenDepth($child, 0);
            }
            $position->delete();
            DB::commit();
            return redirect()->route('organization.structure.index')->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Menyiapkan data untuk D3.js chart dengan koordinat dan tooltip.
     */
    private function getChartData($positions)
    {
        $data = ['nodes' => [], 'indirectLinks' => []];

        foreach ($positions as $position) {
            $data['nodes'][] = [
                'id' => (string) $position->id,
                'title' => $position->title,
                'parent_id' => $position->parent_id ? (string) $position->parent_id : null,
                'employees' => $position->employees->isNotEmpty() ? $position->employees->pluck('full_name')->toArray() : [],
                'indirect_supervisor' => $position->indirectSupervisor ? $position->indirectSupervisor->title : null,
                'coordinates' => $position->getCoordinatesAttribute(),
                'tooltip' => "Jabatan: {$position->title}" .
                    ($position->employees->isNotEmpty() ? "\nDiisi oleh: " . $position->employees->pluck('full_name')->join(', ') : '') .
                    ($position->indirectSupervisor ? "\nDiawasi oleh: {$position->indirectSupervisor->title}" : ''),
            ];
        }

        // Fetch indirect supervisor relationships from the database
        $indirectSupervisors = Position::whereNotNull('indirect_supervisor_id')->get();
        foreach ($indirectSupervisors as $position) {
            if ($position->indirect_supervisor_id) {
                $data['indirectLinks'][] = [
                    'from' => (string) $position->indirect_supervisor_id,
                    'to' => (string) $position->id,
                ];
            }
        }
        return $data;
    }

    /**
     * Mendapatkan ID turunan secara rekursif untuk validasi.
     */
    private function getDescendantIds(Position $position): array
    {
        $ids = [];
        $children = $position->children;
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
    /**
     * Memperbarui depth anak-anak secara rekursif.
     */
    private function updateChildrenDepth(Position $position, int $parentDepth): void
    {
        foreach ($position->children as $child) {
            $child->depth = $parentDepth + 1;
            $child->save();
            $this->updateChildrenDepth($child, $child->depth);
        }
    }
}