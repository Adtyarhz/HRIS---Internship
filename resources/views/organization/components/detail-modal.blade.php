@props([
    'modalId',
    'position', // harus berupa model atau array berisi data lengkap posisi
    'editRoute' => '#',
])

@push('styles')
    <style>
        .modal-detail-icon {
            width: 36px;
            height: 36px;
            background-repeat: no-repeat;
            border-radius: 8px;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3Csvg fill='none' stroke='%239A3B3B' stroke-width='1.5' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M16.5 3.75v16.5m0 0L19.5 17M16.5 20.25L13.5 17M7.5 3.75v16.5m0 0L4.5 17M7.5 20.25L10.5 17'%3E%3C/path%3E%3C/svg%3E");
        }
    </style>
@endpush

<!-- Modal Detail -->
<div id="detailModal-{{ $modalId }}"
    style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2000; max-width: 90vw; max-height: 90vh; overflow-y: auto;">
    <div
        style="background: #FAFBEF; border-radius: 12px; padding: 24px 32px; max-width: 90vw; box-shadow: 0 4px 20px rgba(63, 63, 63, 0.2);">
        <!-- Header: Icon + Title -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="font-size: 20px; font-family: Inter, sans-serif; font-weight: 600; color: black;">
                Detail: {{ $position['title'] }}
            </div>
        </div>

        <!-- Content -->
        <div style="margin-top: 20px;">
            @if (!empty($position['parent']))
                <p><strong>Superior Position:</strong> {{ $position['parent'] }}</p>
            @endif

            @if (!empty($position['indirect_supervisor']))
                <p><strong>Indirect Supervisor:</strong> {{ $position['indirect_supervisor'] }}</p>
            @endif

            @if (!empty($position['employees']))
                <p><strong>Assigned To:</strong></p>
                <ul style="padding-left: 20px; margin-top: 4px;">
                    @foreach ($position['employees'] as $employee)
                        <li>{{ $employee }}</li>
                    @endforeach
                </ul>
            @else
                <p><strong>Assigned To:</strong> <em>Unassigned</em></p>
            @endif
        </div>

        <!-- Buttons -->
        <div style="display: flex; justify-content: center; gap: 16px; margin-top: 24px;">
            @php $role = auth()->user()->role; @endphp
            @if (in_array($role, ['superadmin', 'hc']))
                <a href="{{ $editRoute }}"
                    style="width: 120px; height: 44px; background: #7D3014; border-radius: 8px; color: white; font-size: 14px; font-family: Inter, sans-serif; font-weight: 500; border: 1px solid rgba(0, 0, 0, 0.2); text-align:center; line-height:44px; text-decoration:none; ">
                    Edit
                </a>
            @endif
            <button type="button" onclick="hideDetailModal('{{ $modalId }}')"
                style="width: 120px; height: 44px; background: #367FA9; border-radius: 8px; color: white; font-size: 14px; font-family: Inter, sans-serif; font-weight: 500; border: 1px solid rgba(0, 0, 0, 0.2);">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="detailOverlay-{{ $modalId }}"
    style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.4); z-index: 999;">
</div>

@push('scripts')
    <script>
        function showDetailModal(modalId) {
            document.getElementById('detailModal-' + modalId).style.display = 'block';
            document.getElementById('detailOverlay-' + modalId).style.display = 'block';
        }

        function hideDetailModal(modalId) {
            document.getElementById('detailModal-' + modalId).style.display = 'none';
            document.getElementById('detailOverlay-' + modalId).style.display = 'none';
        }

        document.addEventListener('click', function(event) {
            const overlays = document.querySelectorAll('[id^="detailOverlay-"]');
            overlays.forEach(function(overlay) {
                if (event.target === overlay) {
                    const modalId = overlay.id.replace('detailOverlay-', '');
                    hideDetailModal(modalId);
                }
            });
        });
    </script>
@endpush
