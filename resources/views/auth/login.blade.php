<body style="margin: 0; padding: 0; overflow: hidden; font-family: 'Roboto', sans-serif">
    <div style="display: flex; height: 100vh; width: 100vw;">

        {{-- Left side (Image Background) --}}
        <div style="flex: 1; position: relative; overflow: hidden;">
    {{-- Background Image --}}
    <div style="
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-image: url('{{ asset('img/bpr-building.jpg') }}');
        background-size: cover;
        background-position: center;
        z-index: 1;
    "></div>

    {{-- White Transparent Overlay --}}
    <div style="
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(255, 255, 255, 0.6); /* 0.5 = 50% transparan */
        z-index: 2;
    "></div>

    {{-- Logo --}}
    <div style="
        position: relative;
        z-index: 3;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    ">
        <img src="{{ asset('img/BPR LOGO WITH PX (updated)-01.png') }}" alt="BPR Logo" style="max-width: 800px; width: 100%;" />
    </div>
</div>


      {{-- Right side (Login Panel) --}}
<div style="
    width: 100%;
    max-width: 420px;
    background: #F3E9D2;
    padding: 48px 32px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
">
    <div style="width: 100%;">
        {{-- Header HRIS --}}
       <div style="display: flex; justify-content: center; margin-bottom: 40px;">
    <div style="display: flex; align-items: center; gap: 16px;">
        <img src="{{ asset('img/BPR LOGO WITH PX (updated)-13.png') }}" alt="Logo" style="width: 40px; height: 40px;" />
        <div style="color: #9A3B3B; font-size: 22.5px; font-weight: 1000; font-family: Montserrat;">
            Human Resource Information System
        </div>
    </div>
</div>

        {{-- Login Title --}}
        <h2 style="text-align: center; font-size: 36px; font-weight: 800; font-family: Oswald, sans-serif; margin-bottom: 32px;">Login</h2>

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="mb-4" style="margin-bottom: 20px;">
                <label for="username" style="font-size: 12px; color: #333; padding-left: 8px;">Username</label>
                <input type="text" name="name" id="username" placeholder="Enter username"
                    style="width: 100%; background: #F2F2F2; border: 1px solid #E5E5E5; border-radius: 6px; height: 44px; padding: 0 14px; font-size: 14px; color: #333;" required>
            </div>

            <div class="mb-4" style="margin-bottom: 28px;">
                <label for="password" style="font-size: 12px; color: #333; padding-left: 8px;">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter password"
                    style="width: 100%; background: #F2F2F2; border: 1px solid #E5E5E5; border-radius: 6px; height: 44px; padding: 0 14px; font-size: 14px; color: #333;" required>
            </div>

            <button type="submit"
                style="width: 100%; padding: 10px 24px; background: #9A3B3B; color: white; font-weight: 700; font-size: 14px; border-radius: 6px; border: none;">
                Sign in
            </button>
        </form>

        {{-- Footer --}}
        <div style="margin-top: 48px; text-align: center; font-size: 11px; color: #666;">
            © {{ date('Y') }} BPR Perdana
        </div>
    </div>
</div>
    </div>
</body>