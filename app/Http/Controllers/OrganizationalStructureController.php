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
        $positions = Position::with('employees')->get();
        $chartData = $this->getChartData($positions);
        return view('organization.structure.index', compact('chartData'));
    }

    /**
     * Menampilkan halaman detail untuk satu jabatan.
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
     * Menyimpan jabatan baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:positions,title',
            'parent_id' => 'nullable|exists:positions,id',
        ]);

        $depth = 0;
        if ($request->filled('parent_id')) {
            $parentPosition = Position::find($request->parent_id);
            $depth = $parentPosition->depth + 1;
        }

        Position::create([
            'title' => $validatedData['title'],
            'parent_id' => $validatedData['parent_id'],
            'depth' => $depth,
        ]);

        return redirect()->route('organization.structure.index')->with('success', 'Jabatan baru berhasil ditambahkan.');
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
     * Memperbarui data jabatan di database.
     */
    public function update(Request $request, Position $position)
    {
        $descendantIds = $this->getDescendantIds($position);
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('positions')->ignore($position->id)],
            'parent_id' => ['nullable', 'exists:positions,id', Rule::notIn(array_merge([$position->id], $descendantIds))],
        ]);

        DB::beginTransaction();
        try {
            $oldParentId = $position->parent_id;
            $newParentId = $request->parent_id;

            $newDepth = 0;
            if ($request->filled('parent_id')) {
                $parentPosition = Position::find($request->parent_id);
                $newDepth = $parentPosition->depth + 1;
            }

            $position->update([
                'title' => $validatedData['title'],
                'parent_id' => $validatedData['parent_id'],
                'depth' => $newDepth,
            ]);

            if ($oldParentId != $newParentId) {
                $this->updateChildrenDepth($position, $newDepth);
            }

            DB::commit();
            return redirect()->route('organization.structure.index')->with('success', 'Struktur jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus jabatan dari database.
     */
    public function destroy(Position $position)
    {
        DB::beginTransaction();
        try {
            foreach ($position->children as $child) {
                $child->depth = 0;
                $child->save();
                $this->updateChildrenDepth($child, 0);
            }
            $position->delete();
            DB::commit();
            return redirect()->route('organization.structure.index')->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus jabatan.');
        }
    }

    /**
     * Menyiapkan data untuk format Google Org Chart.
     */
    private function getChartData($positions)
    {
        $data = [];
        foreach ($positions as $position) {

            $detailUrl = route('organization.structure.show', $position->id);
            $nodeContent = '<a href="' . $detailUrl . '" style="text-decoration: none; color: inherit;">' .
                '<div><strong>' . $position->title . '</strong></div>' .
                '</a>';

            $node = ['v' => (string) $position->id, 'f' => $nodeContent];
            $parent = $position->parent_id ? (string) $position->parent_id : '';

            $tooltip = 'Klik untuk melihat detail ' . $position->title;

            $data[] = [$node, $parent, $tooltip];
        }
        return $data;
    }

    /**
     * Mendapatkan semua ID bawahan secara rekursif.
     */
    private function getDescendantIds(Position $position)
    {
        $ids = [];
        foreach ($position->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    /**
     * Memperbarui depth semua bawahan secara rekursif.
     */
    private function updateChildrenDepth(Position $parentPosition, $parentDepth)
    {
        foreach ($parentPosition->children as $child) {
            $child->depth = $parentDepth + 1;
            $child->save();
            $this->updateChildrenDepth($child, $child->depth);
        }
    }
}
