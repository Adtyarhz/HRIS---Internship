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
    style="display: none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); z-index: 2000; width: auto;">
    <div
        style="background: #FAFBEF; border-radius: 12px; padding: 24px 32px; max-width: 90vw; box-shadow: 0 4px 20px rgba(63, 63, 63, 0.2);">
        <!-- Header: Icon + Title -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="background: #FFEA9F; border-radius: 8px; width: 48px; height: 48px; display: flex; justify-content: center; align-items: center;">
                <span class="modal-detail-icon"></span>
            </div>
            <div style="font-size: 20px; font-family: Inter, sans-serif; font-weight: 600; color: black;">
                Detail Jabatan
            </div>
        </div>

        <!-- Content -->
        <div style="margin-top: 20px;">
            <p><strong>Jabatan:</strong> {{ $position['title'] }}</p>

            @if (!empty($position['parent']))
                <p><strong>Parent:</strong> {{ $position['parent'] }}</p>
            @endif

            @if (!empty($position['indirect_supervisor']))
                <p><strong>Diawasi Oleh:</strong> {{ $position['indirect_supervisor'] }}</p>
            @endif

            @if (!empty($position['employees']))
                <p><strong>Diisi oleh:</strong> {{ implode(', ', $position['employees']) }}</p>
            @else
                <p><strong>Diisi oleh:</strong> <em>Belum ada</em></p>
            @endif
        </div>

        <!-- Buttons -->
        <div style="display: flex; justify-content: center; gap: 16px; margin-top: 24px;">
            <button type="button" onclick="hideDetailModal('{{ $modalId }}')"
                style="width: 120px; height: 44px; background: #9A3B3B; border-radius: 8px; color: white; font-size: 14px; font-family: Inter, sans-serif; font-weight: 500; border: 1px solid rgba(0, 0, 0, 0.2);">
                Cancel
            </button>
            <a href="{{ $editRoute }}"
                style="width: 120px; height: 44px; background: #F9FCE6; border-radius: 8px; font-size: 14px; font-family: Inter, sans-serif; font-weight: 500; border: 1px solid rgba(0, 0, 0, 0.2); text-align:center; line-height:44px; text-decoration:none; color:black;">
                Edit
            </a>
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
