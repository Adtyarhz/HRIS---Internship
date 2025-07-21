@props([
    'modalId',
    'message' => 'Are you sure to delete this item?',
    'method' => 'DELETE',
    'title' => 'Delete Confirmation',
    'action',
    'iconClass' => 'gg--trash',
])

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

        .tab-close-inactive {
            display: inline-block;
            width: 36px;
            height: 36px;
            background-repeat: no-repeat;
            border-radius: 8px;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%239A3B3B' d='M14 22v-2h4v2zm-6 0v-2h4v2zM4 10H2V6h2zm0 6H2v-4h2zm2 6H4q-.825 0-1.412-.587T2 20v-2h2v2h2zm5.9-8.5l2.1-2.1l2.1 2.1l1.4-1.4l-2.1-2.1l2.1-2.1l-1.4-1.4L14 8.6l-2.1-2.1l-1.4 1.4l2.1 2.1l-2.1 2.1zM8 18q-.825 0-1.412-.587T6 16V4q0-.825.588-1.412T8 2h12q.825 0 1.413.588T22 4v12q0 .825-.587 1.413T20 18z'/%3E%3C/svg%3E");
        }
    </style>
@endpush

<!-- Modal Delete Confirmation Component -->
<div id="deleteModal-{{ $modalId }}"
    style="
    display: none;
    position: fixed;
    top: 20%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2000;
    width: auto;
">
    <div
        style="
        margin: 0 auto;
        background: #FAFBEF;
        border-radius: 12px;
        padding: 24px 32px;
        width: auto;
        max-width: 90vw;
        box-shadow: 0 4px 20px rgba(63, 63, 63, 0.2);
        display: inline-block;
    ">
        <!-- Header: Icon + Text -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div
                style="
                background: #FFEA9F;
                border-radius: 8px;
                width: 48px;
                height: 48px;
                display: flex;
                justify-content: center;
                align-items: center;
            ">
                <span class="{{ $iconClass }}" style="font-size: 24px; color:#9A3B3B"></span>
            </div>
            <div
                style="font-size: 20px; font-family: Inter, sans-serif; font-weight: 600; color: black; white-space: nowrap;">
                {{ $message }}
            </div>
        </div>

        <!-- Buttons -->
        <div style="display: flex; justify-content: center; gap: 16px; margin-top: 24px;">
            <button type="button" onclick="hideDeleteModal('{{ $modalId }}')"
                style="
                width: 120px;
                height: 44px;
                background: #9A3B3B;
                border-radius: 8px;
                color: white;
                font-size: 14px;
                font-family: Inter, sans-serif;
                font-weight: 500;
                border: 1px solid rgba(0, 0, 0, 0.2);
            ">
                Cancel
            </button>
            <form action="{{ $action }}" method="POST" style="display: inline;">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <button type="submit"
                    style="
                    width: 120px;
                    height: 44px;
                    background: #F9FCE6;
                    border-radius: 8px;
                    font-size: 14px;
                    font-family: Inter, sans-serif;
                    font-weight: 500;
                    border: 1px solid rgba(0, 0, 0, 0.2);
                ">
                    Yes
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Overlay -->
<div id="modalOverlay-{{ $modalId }}"
    style="
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 999;
">
</div>

@push('scripts')
    <script>
        function showDeleteModal(modalId) {
            document.getElementById('deleteModal-' + modalId).style.display = 'block';
            document.getElementById('modalOverlay-' + modalId).style.display = 'block';
        }

        function hideDeleteModal(modalId) {
            document.getElementById('deleteModal-' + modalId).style.display = 'none';
            document.getElementById('modalOverlay-' + modalId).style.display = 'none';
        }

        document.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('[id^="modalOverlay-"]');
            modals.forEach(function(overlay) {
                if (event.target === overlay) {
                    const modalId = overlay.id.replace('modalOverlay-', '');
                    hideDeleteModal(modalId);
                }
            });
        });
    </script>
@endpush
