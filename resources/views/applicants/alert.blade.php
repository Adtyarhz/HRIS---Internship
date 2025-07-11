@php
    $type = null;
    $message = null;

    if (session()->has('success')) {
        $type = 'success';
        $message = session('success');
    } elseif (session()->has('error')) {
        $type = 'danger';
        $message = session('error');
    } elseif (session()->has('warning')) {
        $type = 'warning';
        $message = session('warning');
    }
@endphp

@if(is_string($message))
<div id="flash-alert"
    style="position: fixed; top: 90px; right: 20px;
           width: auto; padding: 12px;
           background: #F7FBD3;
           box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.08);
           border-radius: 16px;
           outline: 1px rgba(0, 0, 0, 0.10) solid;
           display: flex; align-items: center; gap: 11px;
           z-index: 1055; transition: opacity 0.5s ease;">

        <div style="width: 40px; height: 40px; background: #F7FBD3;
                    display: flex; align-items: center; justify-content: center;
                    border-radius: 6px;">
            <div style="font-size: 20px; font-weight: 600;">
                @if($type === 'success') ✅
                @elseif($type === 'danger') ❌
                @elseif($type === 'warning') ⚠
                @else ℹ
                @endif
            </div>
        </div>
        <div style="flex: 1; color: #000; font-size: 15px; white-space: nowrap;">
            {{ $message }}
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