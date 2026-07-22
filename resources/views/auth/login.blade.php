<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMBAR - Masuk</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo_BPS.PNG') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>

<div class="auth-bg">
    <div class="auth-card">

        {{-- KIRI: PANEL PUTIH (FORM LOGIN) --}}
        <div class="auth-left">
            <h2 style="color:#1f4068; font-size:26px; margin:0 0 5px 0;">Masuk ke SIMBAR</h2>
            <p style="color:#7f8c8d; font-size:13px; margin:0 0 30px 0; display:flex; align-items:center; gap:5px;">
                <i data-lucide="lock" style="width:14px;height:14px;"></i>
                Silakan masukkan kredensial Anda
            </p>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="alert alert-error" style="width:100%; max-width:320px;">
                    {{ $errors->first() }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error" style="width:100%; max-width:320px;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" style="width:100%; max-width:320px; display:flex; flex-direction:column; gap:15px;" id="login-form">
                @csrf

                {{-- Email --}}
                <div style="position:relative;">
                    <i data-lucide="mail" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:#a0aec0;"></i>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="nama@bps.go.id"
                        class="input-field"
                        autocomplete="email"
                    >
                </div>

                {{-- Password --}}
                <div style="position:relative;">
                    <i data-lucide="lock" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:#a0aec0;"></i>
                    <input
                        type="password"
                        name="password"
                        id="password-input"
                        required
                        placeholder="Masukkan kata sandi"
                        class="input-field"
                        style="padding-right:45px;"
                        autocomplete="current-password"
                    >
                    <button type="button" onclick="togglePassword()" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; background:none; border:none; color:#a0aec0; padding:0;">
                        <i data-lucide="eye" id="eye-icon" style="width:18px;height:18px;"></i>
                    </button>
                </div>

                <div style="display:flex; justify-content:center; margin-top:10px;">
                    <button type="submit" id="submit-btn" style="width:100%; padding:12px; background-color:#21527e; color:white; border:none; border-radius:25px; font-weight:bold; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 10px rgba(33,82,126,0.3); font-family:inherit; box-sizing:border-box; transition:opacity 0.2s ease;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <span id="btn-text">Login</span>
                        <i data-lucide="arrow-right" style="width:16px;height:16px;" id="btn-icon"></i>
                    </button>
                </div>
            </form>

            {{-- Tombol Login SSO BPS --}}
            <a href="#" style="width:100%; max-width:320px; margin-top:10px; padding:12px; background-color:#21527e; color:white; border:none; border-radius:25px; font-weight:bold; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; text-decoration:none; box-shadow:0 4px 10px rgba(33,82,126,0.3); font-family:inherit; box-sizing:border-box; transition:opacity 0.2s ease;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <i data-lucide="shield-check" style="width:18px;height:18px;"></i>
                <span>Login SSO BPS</span>
            </a>
        </div>

        {{-- KANAN: PANEL BIRU --}}
        <div class="auth-right">
            <div class="brand-icon">
                <i data-lucide="boxes" style="width:40px;height:40px;color:#204b7a;"></i>
            </div>
            <span style="font-size:10px; letter-spacing:4px; text-transform:uppercase; margin-bottom:5px; opacity:0.8;">SIMBAR</span>
            <h3 style="font-size:16px; margin:0 0 40px; font-weight:normal;">Sistem Informasi Manajemen Barang</h3>
            <h1 style="font-size:28px; margin:0 0 15px; font-weight:bold;">HALO, PEGAWAI!</h1>
            <p style="font-size:13px; margin:0 0 30px; line-height:1.6; opacity:0.9; max-width:280px;">
                Belum memiliki akun? Silakan hubungi Administrator untuk pembuatan akun SIMBAR Anda.
            </p>
        </div>

    </div>
</div>

<script>
    lucide.createIcons();

    function togglePassword() {
        const input = document.getElementById('password-input');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    document.getElementById('login-form').addEventListener('submit', function() {
        const btn = document.getElementById('submit-btn');
        const text = document.getElementById('btn-text');
        btn.disabled = true;
        text.textContent = 'MEMPROSES...';
    });
</script>
</body>
</html>
