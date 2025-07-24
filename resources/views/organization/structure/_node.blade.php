{{-- Setiap item dalam daftar --}}
<li style="margin-left: {{ $node->depth * 30 }}px; margin-bottom: 5px;">
    <div class="org-tree-node">
        <div class="node-content">
            <div>
                <span class="node-title">{{ $node->title }}</span>
                {{-- Tampilkan nama karyawan yang menduduki jabatan --}}
                @if($node->employees->isNotEmpty())
                    <span class="node-employee ml-2">
                        (Diisi oleh: {{ $node->employees->pluck('full_name')->join(', ') }})
                    </span>
                @else
                    <span class="node-empty ml-2">(Posisi Kosong)</span>
                @endif
            </div>
            <div class="node-actions">
                {{-- Tombol Aksi --}}
                <a href="{{ route('organization.structure.edit', $node->id) }}" class="btn btn-sm btn-warning" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('organization.structure.destroy', $node->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jabatan ini? Semua bawahannya akan menjadi jabatan level atas.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</li>

{{-- Jika node ini punya anak, panggil lagi partial ini untuk setiap anak --}}
@if (isset($node->children_nodes) && $node->children_nodes->isNotEmpty())
    @foreach ($node->children_nodes as $childNode)
        @include('organization.structure._node', ['node' => $childNode])
    @endforeach
@endif
