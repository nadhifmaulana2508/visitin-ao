<style>
    /* Styling khusus Login (Full Screen) */
    .login-wrapper {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 20px;
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
        border: none;
    }
    .btn-login:hover { background-color: #e66a45; color: white; }
    .btn-login:disabled { opacity: 0.7; }
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
    /* Demo accounts collapsible */
    .demo-accounts {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 16px;
        padding: 15px;
        margin-top: 20px;
    }
    .demo-accounts summary {
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255,255,255,0.7);
    }
    .demo-accounts summary:hover {
        color: white;
    }
    .demo-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        font-size: 0.7rem;
    }
    .demo-item:last-child { border-bottom: none; }
    .demo-item .demo-role {
        color: var(--color-accent);
        font-weight: 700;
        font-size: 0.65rem;
        text-transform: uppercase;
    }
    .demo-item .demo-id {
        color: rgba(255,255,255,0.9);
        font-weight: 600;
        font-family: monospace;
    }
    .btn-demo-fill {
        background: rgba(255,255,255,0.15);
        border: none;
        color: white;
        font-size: 0.6rem;
        padding: 3px 8px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }
    .btn-demo-fill:hover {
        background: var(--color-accent);
    }
</style>

<div class="login-wrapper">
    <div class="text-center mb-4">
        <div class="logo-circle">
            <i class="fa-solid fa-map-location-dot fs-1 text-primary"></i>
        </div>
        <h2 class="fw-bold mb-1">Visitin</h2>
        <p class="small text-white-50">Sistem Prospek & Kunjungan Nasabah</p>
    </div>

    <div class="login-card">
        <h5 class="fw-bold text-dark mb-4 text-center">Silakan Login</h5>
        
        <div id="login-alert"></div>

        <form id="form-login">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">ID Pegawai</label>
                <input type="text" name="id_peg" id="input_id_peg" class="form-control form-control-custom" placeholder="Contoh: 102-119" required autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Password</label>
                <input type="password" name="password" id="input_password" class="form-control form-control-custom" placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" id="btn-login" class="btn btn-login w-100 mb-3">Login Sekarang</button>
            
            <div class="text-center mt-3">
                <a href="<?= BASE_APP ?>/reset" class="text-decoration-none small fw-bold" style="color: var(--color-primary);">Lupa Password / Aktivasi Akun?</a>
            </div>
        </form>
    </div>
    
    <!-- Demo Accounts -->
    <details class="demo-accounts">
        <summary><i class="fa-solid fa-flask me-1"></i> Akun Demo (Klik untuk lihat)</summary>
        <div class="mt-2">
            <div class="demo-item">
                <div>
                    <span class="demo-role">Developer (Full)</span><br>
                    <span class="demo-id">102-119</span>
                </div>
                <button class="btn-demo-fill" onclick="fillLogin('102-119')">Isi</button>
            </div>
            <div class="demo-item">
                <div>
                    <span class="demo-role">AO Kredit</span><br>
                    <span class="demo-id">201-001</span>
                </div>
                <button class="btn-demo-fill" onclick="fillLogin('201-001')">Isi</button>
            </div>
            <div class="demo-item">
                <div>
                    <span class="demo-role">AO Dana</span><br>
                    <span class="demo-id">201-002</span>
                </div>
                <button class="btn-demo-fill" onclick="fillLogin('201-002')">Isi</button>
            </div>
            <div class="demo-item">
                <div>
                    <span class="demo-role">AO Remedial (FE+BE)</span><br>
                    <span class="demo-id">201-003</span>
                </div>
                <button class="btn-demo-fill" onclick="fillLogin('201-003')">Isi</button>
            </div>
            <div class="demo-item">
                <div>
                    <span class="demo-role">Superuser (Kabid)</span><br>
                    <span class="demo-id">201-004</span>
                </div>
                <button class="btn-demo-fill" onclick="fillLogin('201-004')">Isi</button>
            </div>
            <div class="demo-item">
                <div>
                    <span class="demo-role">Staff / Non-AO (Teller)</span><br>
                    <span class="demo-id">201-005</span>
                </div>
                <button class="btn-demo-fill" onclick="fillLogin('201-005')">Isi</button>
            </div>
        </div>
        <p class="text-center mt-2 mb-0" style="font-size: 0.6rem; opacity: 0.5;">Password semua akun: <b>123456</b></p>
    </details>

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
            '<div class="alert alert-' + type + ' py-2 small mb-3">' + msg + '</div>';
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
            app: 'visitin_ao'
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
                // Simpan user data ke localStorage untuk akses FE
                const userData = body.data || {};
                localStorage.setItem('user_data', JSON.stringify(userData));
                localStorage.setItem('user_role', userData.role || 'staff');
                localStorage.setItem('user_permissions', JSON.stringify(userData.permissions || []));
                localStorage.setItem('user_name', userData.full_name || '');
                localStorage.setItem('user_branch', userData.branch_name || '');

                showAlert('success', 'Login berhasil! Mengalihkan...');
                setTimeout(() => {
                    window.location.href = BASE_APP + '/home';
                }, 500);
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

// Demo account fill helper
function fillLogin(idPeg) {
    document.getElementById('input_id_peg').value = idPeg;
    document.getElementById('input_password').value = '123456';
    document.getElementById('input_id_peg').focus();
}
</script>
