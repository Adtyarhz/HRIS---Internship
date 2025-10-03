@if ($errors->any())
    <div class="flash-alert flash-alert-danger" id="flash-alert">
        <div class="flash-icon">❌</div>
        <div class="flash-message">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
