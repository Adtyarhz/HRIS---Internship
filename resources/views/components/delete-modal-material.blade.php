@props(['modalId', 'message' => 'Are you sure to delete this item?'])

@push('styles')
<style>
    .gg--trash {
        display: inline-block;
        width: 36px;
        height: 36px;
        background-repeat: no-repeat;
        border-radius: 8px;
        background-size: 100% 100%;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg fill='%239A3B3B'%3E%3Cpath fill-rule='evenodd' d='M17 5V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V7h1a1 1 0 1 0 0-2zm-2-1H9v1h6zm2 3H7v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z' clip-rule='evenodd'/%3E%3Cpath d='M9 9h2v8H9zm4 0h2v8h-2z'/%3E%3C/g%3E%3C/svg%3E");
    }
</style>
@endpush

<!-- Modal Delete Material Component -->
<div id="deleteModal-{{ $modalId }}" style="display: none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); z-index: 2000; width: auto;">
    <div style="margin: 0 auto; background: #FAFBEF; border-radius: 12px; padding: 24px 32px; max-width: 90vw; box-shadow: 0 4px 20px rgba(63, 63, 63, 0.2); display: inline-block;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="background: #FFEA9F; border-radius: 8px; width: 48px; height: 48px; display: flex; justify-content: center; align-items: center;">
                <span class="gg--trash" style="font-size: 10px; color:#9A3B3B"></span>
            </div>
            <div style="font-size: 20px; font-family: Inter, sans-serif; font-weight: 600; color: black;">
                {{ $message }}
            </div>
        </div>
        <div style="display: flex; justify-content: center; gap: 16px; margin-top: 24px;">
            <button type="button" onclick="hideDeleteModal('{{ $modalId }}')" style="width: 120px; height: 44px; background: #9A3B3B; border-radius: 8px; color: white; font-size: 14px; font-weight: 500; border: 1px solid rgba(0, 0, 0, 0.2);">Cancel</button>
            <form id="form-{{ $modalId }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" style="width: 120px; height: 44px; background: #F9FCE6; border-radius: 8px; font-size: 14px; font-weight: 500; border: 1px solid rgba(0, 0, 0, 0.2);">Yes</button>
            </form>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="modalOverlay-{{ $modalId }}" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.4); z-index: 999;"></div>

@push('scripts')
<script>
    function showDeleteModal(modalId, actionUrl = null) {
        const modal = document.getElementById('deleteModal-' + modalId);
        const overlay = document.getElementById('modalOverlay-' + modalId);
        modal.style.display = 'block';
        overlay.style.display = 'block';

        if (actionUrl) {
            const form = document.getElementById('form-' + modalId);
            form.setAttribute('action', actionUrl);
        }
    }

    function hideDeleteModal(modalId) {
        document.getElementById('deleteModal-' + modalId).style.display = 'none';
        document.getElementById('modalOverlay-' + modalId).style.display = 'none';
    }

    document.addEventListener('click', function(event) {
        const overlays = document.querySelectorAll('[id^=\"modalOverlay-\"]');
        overlays.forEach(function(overlay) {
            if (event.target === overlay) {
                const modalId = overlay.id.replace('modalOverlay-', '');
                hideDeleteModal(modalId);
            }
        });
    });
</script>
@endpush
