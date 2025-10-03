@php
    $type = session()->has('success') ? 'success'
           : (session()->has('error') ? 'error'
           : (session()->has('warning') ? 'warning'
           : (session()->has('info') ? 'info' : null)));

    $message = $type ? session($type) : null;

    $icon = match($type) {
        'success' => '✅',
        'error'  => '❌',
        'warning' => '⚠️',
        'info'    => 'ℹ️',
        default   => ''
    };
@endphp

@if ($message)
    <div id="flash-alert" class="flash-alert flash-alert-{{ $type }}">
        <div class="flash-icon">{{ $icon }}</div>
        <div class="flash-message">{{ $message }}</div>
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
