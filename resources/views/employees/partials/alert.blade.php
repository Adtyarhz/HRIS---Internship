@php
    $type = session()->has('success') ? 'success'
           : (session()->has('error') ? 'danger'
           : (session()->has('warning') ? 'warning'
           : null));

    $message = session($type);
@endphp

@if(session('success') || session('error'))
    <div id="flash-alert"
        style="position: absolute; top: -16px; right: 0;
               padding: 12px 16px;
               background: #F7FBD3;
               box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.08);
               border-radius: 16px;
               outline: 1px rgba(0, 0, 0, 0.10) solid;
               display: flex; align-items: center; gap: 11px;
               z-index: 999;
               max-width: 90vw; white-space: nowrap;">
        <div style="width: 40px; height: 40px; background: #F7FBD3;
                    display: flex; align-items: center; justify-content: center;
                    border-radius: 6px;">
            <div style="font-size: 20px; font-weight: 600;">✅</div>
        </div>
        <div style="flex: 1; color: #000; font-size: 15px;">
            {{ session('success') ?? session('error') }}
        </div>
    </div>

    @push('scripts')
    <script>
        setTimeout(() => {
            const alertBox = document.getElementById('flash-alert');
            if (alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 4000);
    </script>
    @endpush
@endif
