<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ApprovalWorkflowService;

class OrganizationalStructureController extends Controller
{
    public function index()
    {
        $positions = Position::with([
            'division',
            'parent',
            'children',
            'indirectSupervisor',
            'indirectSubordinates',
            'employees' => function ($query) {
                $query->where('status', 'Aktif');
            }
        ])->get();

        $chartData = $this->getChartData($positions);
        return view('organization.structure.index', compact('positions', 'chartData'));
    }

    public function show(Position $position)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $position->load([
            'division',
            'employees' => function ($query) {
                $query->where('status', 'Aktif')->with('division');
            }
        ]);
        return view('organization.structure.show', compact('position'));
    }

    public function create()
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $positions = Position::orderBy('title')->get();
        $divisions = Division::orderBy('name')->get();

        return view('organization.structure.create', compact('positions', 'divisions'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:positions,title',
            'division_id' => 'required|exists:divisions,id',
            'parent_id' => 'nullable|exists:positions,id',
            'indirect_supervisor_id' => 'nullable|exists:positions,id',
            'depth' => 'nullable|integer|min:0',
        ]);

        //-- APPROVAL LOGIC START --//
        $user = Auth::user();
        if ($user && $user->role === 'hc') {
            $payload = $validatedData;
            $depth = $request->filled('depth') ? $validatedData['depth'] : 0;
            if ($request->filled('parent_id')) {
                $parentPosition = Position::find($request->parent_id);
                $depth = $parentPosition ? $parentPosition->depth + 1 : 0;
            }
            $payload['depth'] = $depth;

            $tempModel = new Position($payload);
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'create');
            return redirect()->route('organization.structure.index')
                ->with('success', 'Permintaan penambahan jabatan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN
        $depth = $request->filled('depth') ? $validatedData['depth'] : 0;
        if ($request->filled('parent_id')) {
            $parentPosition = Position::find($request->parent_id);
            $depth = $parentPosition ? $parentPosition->depth + 1 : 0;
        }

        DB::beginTransaction();
        try {
            Position::create([
                'title' => $validatedData['title'],
                'division_id' => $validatedData['division_id'],
                'parent_id' => $validatedData['parent_id'],
                'indirect_supervisor_id' => $validatedData['indirect_supervisor_id'],
                'depth' => $depth,
            ]);

            DB::commit();
            return redirect()->route('organization.structure.index')
                ->with('success', 'Jabatan baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan jabatan: ' . $e->getMessage());
        }
    }

    public function edit(Position $position)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $descendantIds = $this->getDescendantIds($position);
        $possibleParents = Position::where('id', '!=', $position->id)
            ->whereNotIn('id', $descendantIds)
            ->orderBy('title')
            ->get();

        $divisions = Division::orderBy('name')->get();

        return view('organization.structure.edit', compact('position', 'possibleParents', 'divisions'));
    }

    public function update(Request $request, Position $position)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        $descendantIds = $this->getDescendantIds($position);

        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('positions')->ignore($position->id)],
            'division_id' => ['required', 'exists:divisions,id'],
            'parent_id' => ['nullable', 'exists:positions,id', Rule::notIn(array_merge([$position->id], $descendantIds))],
            'indirect_supervisor_id' => ['nullable', 'exists:positions,id'],
            'depth' => 'nullable|integer|min:0',
        ]);

        //-- APPROVAL LOGIC START (PERBAIKAN KONSISTENSI) --//
        $user = Auth::user();
        if ($user && $user->role === 'hc') {
            $payload = $validatedData;
            $depth = $request->filled('depth') ? $validatedData['depth'] : 0;
            if ($request->filled('parent_id')) {
                $parentPosition = Position::find($request->parent_id);
                $depth = $parentPosition ? $parentPosition->depth + 1 : 0;
            }
            $payload['depth'] = $depth;

            // Gunakan 'clone' untuk menjaga data original
            $tempModel = clone $position;
            // Isi clone dengan data baru dari validasi
            $tempModel->fill($payload);
            
            // Panggil metode public `captureModelChange`
            ApprovalWorkflowService::captureModelChange($user, $tempModel, 'update');
            
            return redirect()->route('organization.structure.index')
                ->with('success', 'Permintaan perubahan jabatan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//

        // Logika di bawah ini hanya berjalan untuk SUPERADMIN
        $depth = $request->filled('depth') ? $validatedData['depth'] : 0;
        if ($request->filled('parent_id')) {
            $parentPosition = Position::find($request->parent_id);
            $depth = $parentPosition ? $parentPosition->depth + 1 : 0;
        }

        DB::beginTransaction();
        try {
            $position->update([
                'title' => $validatedData['title'],
                'division_id' => $validatedData['division_id'],
                'parent_id' => $validatedData['parent_id'],
                'indirect_supervisor_id' => $validatedData['indirect_supervisor_id'],
                'depth' => $depth,
            ]);

            $this->updateChildrenDepth($position, $depth);
            DB::commit();

            return redirect()->route('organization.structure.index')
                ->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    public function destroy(Position $position)
    {
        $this->authorizeRole(['superadmin', 'hc']);

        if ($position->employees()->exists()) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena masih ditempati karyawan.');
        }

        //-- APPROVAL LOGIC START --//
        $user = Auth::user();
        if ($user && $user->role === 'hc') {
            ApprovalWorkflowService::captureModelChange($user, $position, 'delete');
            return redirect()->route('organization.structure.index')
                ->with('success', 'Permintaan penghapusan jabatan telah dikirim untuk approval.');
        }
        //-- APPROVAL LOGIC END --//
        
        // Logika di bawah ini hanya berjalan untuk SUPERADMIN
        DB::beginTransaction();
        try {
            foreach ($position->children as $child) {
                $child->update(['parent_id' => null, 'depth' => 0]);
                $this->updateChildrenDepth($child, 0);
            }

            $position->delete();
            DB::commit();

            return redirect()->route('organization.structure.index')
                ->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus jabatan: ' . $e->getMessage());
        }
    }

    private function authorizeRole(array $roles)
    {
        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    private function getChartData($positions)
    {
        $nodes = [];
        foreach ($positions as $position) {
            $nodes[] = [
                'id' => (string) $position->id,
                'title' => $position->title,
                'division' => $position->division ? $position->division->name : null,
                'parent_id' => $position->parent_id ? (string) $position->parent_id : null,
                'depth' => $position->depth,
                'employees' => $position->employees->isNotEmpty() ? $position->employees->pluck('full_name')->toArray() : [],
                'indirect_supervisor' => $position->indirectSupervisor ? $position->indirectSupervisor->title : null,
            ];
        }

        $roots = array_filter($nodes, fn($node) => $node['parent_id'] === null);

        if (count($roots) > 1) {
            $superRootId = 'super-root-0';
            $superRoot = [
                'id' => $superRootId,
                'parent_id' => null,
                'depth' => -1,
                'title' => 'Virtual Root',
                'isSuperRoot' => true,
                'employees' => [],
                'indirect_supervisor' => null,
            ];
            array_unshift($nodes, $superRoot);

            foreach ($nodes as &$node) {
                if ($node['parent_id'] === null && $node['id'] !== $superRootId) {
                    $node['parent_id'] = $superRootId;
                }
            }
            unset($node);
        }

        $indirectLinks = [];
        $indirectSupervisors = $positions->whereNotNull('indirect_supervisor_id');
        foreach ($indirectSupervisors as $position) {
            $indirectLinks[] = [
                'from' => (string) $position->indirect_supervisor_id,
                'to' => (string) $position->id,
            ];
        }

        return ['nodes' => $nodes, 'indirectLinks' => $indirectLinks];
    }

    private function getDescendantIds(Position $position): array
    {
        $ids = [];
        foreach ($position->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    private function updateChildrenDepth(Position $position, int $parentDepth): void
    {
        foreach ($position->children as $child) {
            $child->depth = $parentDepth + 1;
            $child->save();
            $this->updateChildrenDepth($child, $child->depth);
        }
    }
}

