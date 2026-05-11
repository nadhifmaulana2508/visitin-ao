<style>
    /* Styling khusus Login (Full Screen) */
    .login-wrapper {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 30px;
        color: white;
    }
    .login-card {
        background-color: white;
        border-radius: 24px;
        padding: 30px 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .form-control-custom {
        background-color: #F4F7F6;
        border: 1px solid #E0E0E0;
        border-radius: 12px;
        padding: 12px 15px;
        font-size: 0.9rem;
    }
    .form-control-custom:focus {
        border-color: var(--color-accent);
        box-shadow: 0 0 0 0.25rem rgba(255, 123, 84, 0.25);
        background-color: white;
    }
    .btn-login {
        background-color: var(--color-accent);
        color: white;
        border-radius: 12px;
        padding: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .btn-login:hover { background-color: #e66a45; color: white; }
    .logo-circle {
        width: 80px;
        height: 80px;
        background-color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px auto;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
</style>

<div class="login-wrapper">
    <div class="text-center mb-4">
        <div class="logo-circle">
            <i class="fa-solid fa-map-location-dot fs-1 text-primary"></i>
        </div>
        <h2 class="fw-bold mb-1">Visitin</h2> <p class="small text-white-50">Sistem Kunjungan Nasabah</p>
    </div>

    <div class="login-card">
        <h5 class="fw-bold text-dark mb-4 text-center">Silakan Login</h5>
        
        <div id="login-alert"></div>

        <form id="form-login" action="<?= BASE_APP ?>/home" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">ID Pegawai (NIK)</label>
                <input type="text" name="id_peg" class="form-control form-control-custom" placeholder="Masukkan ID Pegawai" required autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Password</label>
                <input type="password" name="password" class="form-control form-control-custom" placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" id="btn-login" class="btn btn-login w-100 mb-3">Login Sekarang</button>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_APP ?>/reset" class="text-decoration-none small fw-bold" style="color: var(--color-primary);">Lupa Password / Aktivasi Akun?</a>
                <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem;">(Gunakan NIK dan No HP untuk aktivasi pertama kali)</p>
            </div>
        </form>
    </div>
    
    <div class="text-center mt-4">
        <p class="small text-white-50 mb-0">&copy; 2026 IT Department</p>
    </div>
</div>



<script>
    (function () {
        const form     = document.getElementById('form-login');
        const alertBox = document.getElementById('login-alert');
        const btn      = document.getElementById('btn-login');
        const BASE_APP = <?= json_encode(BASE_APP) ?>;

        function showAlert(type, msg) {
            alertBox.innerHTML =
                '<div class="alert alert-' + type + ' py-2 small">' + msg + '</div>';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.innerHTML = '';
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = 'Memproses...';

            const fd = new FormData(form);
            const payload = {
                id_peg:   (fd.get('id_peg') || '').toString().trim(),
                password: (fd.get('password') || '').toString(),
            };

            try {
                const res = await fetch(BASE_APP + '/api/?action=login', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const body = await res.json().catch(() => ({}));

                if (res.ok && body.status === 200) {
                    showAlert('success', body.message || 'Login berhasil');
                    window.location.href = BASE_APP + '/home';
                    return;
                }
                showAlert('danger', body.message || 'Login gagal. Periksa kredensial Anda.');
            } catch (err) {
                showAlert('danger', 'Tidak bisa terhubung ke server.');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    })();
</script>
