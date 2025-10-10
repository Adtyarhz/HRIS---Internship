@php
    $type = session()->has('success') ? 'success'
           : (session()->has('error') ? 'danger'
           : (session()->has('warning') ? 'warning'
           : (session()->has('info') ? 'info' : null)));

    $message = $type ? session($type === 'danger' ? 'error' : $type) : null;

    $icon = match($type) {
        'success' => '✅',
        'danger'  => '❌',
        'warning' => '⚠',
        'info'    => 'ℹ',
        default   => ''
    };
@endphp

@if (is_string($message))
<div id="flash-alert"
    style="position: fixed;
           top: 90px;
           right: 20px;
           width: auto;
           padding: 12px 16px;
           background: #F7FBD3;
           box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
           border-radius: 16px;
           outline: 1px rgba(0, 0, 0, 0.10) solid;
           display: flex;
           align-items: center;
           gap: 11px;
           z-index: 1055;
           transition: opacity 0.5s ease;">

    {{-- Ikon --}}
    <div style="width: 38px;
                height: 38px;
                background: #F7FBD3;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;">
        <div style="font-size: 20px; font-weight: 600;">{{ $icon }}</div>
    </div>

    {{-- Pesan --}}
    <div style="flex: 1;
                color: #000;
                font-size: 15px;
                white-space: nowrap;">
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