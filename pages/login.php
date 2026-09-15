<style>
    /* =========================================
       LOGIN PAGE - 2 GRID (Desktop) / Single (Mobile)
    ========================================= */
    .login-page {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        background: var(--color-bg);
    }

    /* LEFT PANEL: Slider/Poster (hidden on mobile) */
    .login-left {
        display: none;
        flex: 1;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
        position: relative;
        overflow: hidden;
        padding: 40px;
        justify-content: center;
        align-items: center;
    }

    @media (min-width: 768px) {
        .login-left { display: flex; }
        .login-page { flex-direction: row; }
    }

    .slider-container {
        width: 100%;
        max-width: 450px;
        text-align: center;
        color: white;
        position: relative;
        z-index: 2;
    }

    .slide { display: none; animation: fadeSlide 0.5s ease; }
    .slide.active { display: block; }
    @keyframes fadeSlide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .slide-icon {
        width: 100px; height: 100px; border-radius: 24px;
        background: rgba(255,255,255,0.1); margin: 0 auto 30px auto;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; color: var(--color-accent);
        backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);
    }
    .slide h3 { font-weight: 800; margin-bottom: 12px; font-size: 1.4rem; }
    .slide p { font-size: 0.9rem; line-height: 1.6; color: rgba(255,255,255,0.75); }

    .slider-dots {
        display: flex; gap: 8px; justify-content: center; margin-top: 30px;
    }
    .slider-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: rgba(255,255,255,0.3); transition: 0.3s; cursor: pointer;
    }
    .slider-dot.active { background: var(--color-accent); width: 24px; border-radius: 4px; }

    /* Decorative circles */
    .login-left::before {
        content: ''; position: absolute; width: 300px; height: 300px;
        border-radius: 50%; background: rgba(255,123,84,0.08);
        top: -80px; right: -80px;
    }
    .login-left::after {
        content: ''; position: absolute; width: 200px; height: 200px;
        border-radius: 50%; background: rgba(255,255,255,0.03);
        bottom: -50px; left: -50px;
    }

    /* RIGHT PANEL: Login Form */
    .login-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        min-height: 100vh;
        min-height: 100dvh;
        background: linear-gradient(180deg, var(--color-primary) 0%, var(--color-secondary) 40%, var(--color-bg) 100%);
    }

    @media (min-width: 768px) {
        .login-right {
            background: var(--color-bg);
            max-width: 500px;
            padding: 40px;
        }
    }

    .login-form-wrapper {
        width: 100%;
        max-width: 380px;
    }

    /* Logo section (only shows on mobile since left panel hidden) */
    .login-logo-mobile {
        text-align: center;
        margin-bottom: 24px;
        color: white;
    }
    @media (min-width: 768px) {
        .login-logo-mobile { color: var(--color-primary); }
        .login-logo-mobile .text-white-50 { color: #64748B !important; }
    }

    .logo-circle-sm {
        width: 64px; height: 64px; border-radius: 50%;
        background: white; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    @media (min-width: 768px) {
        .logo-circle-sm { background: #E6EDF5; box-shadow: none; }
    }

    .login-card {
        background: white;
        border-radius: 20px;
        padding: 28px 22px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    @media (min-width: 768px) {
        .login-card { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    }

    .form-control-login {
        background: #F4F7F6; border: 1px solid #E2E8F0; border-radius: 10px;
        padding: 12px 14px; font-size: 0.9rem; width: 100%; min-height: 46px;
        transition: border-color 0.2s;
    }
    .form-control-login:focus {
        border-color: var(--color-accent); outline: none;
        box-shadow: 0 0 0 3px rgba(255,123,84,0.12); background: white;
    }

    .btn-login {
        background: var(--color-accent); color: white; border: none;
        border-radius: 10px; padding: 13px; font-weight: 700; font-size: 0.9rem;
        width: 100%; letter-spacing: 0.5px; transition: 0.2s; min-height: 48px;
    }
    .btn-login:hover { background: #e66a45; }
    .btn-login:disabled { opacity: 0.65; }

    /* # COMPONENT: Shortcut user development untuk berpindah akun tanpa mengubah jalur autentikasi SSO. */
    .login-user-list {
        margin-top: 14px;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 11px 12px;
        background: #F8FAFC;
    }
    .login-user-list summary {
        cursor: pointer;
        color: #64748B;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .login-user-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 7px 0;
        border-bottom: 1px solid #E2E8F0;
        font-size: 0.68rem;
    }
    .login-user-row:last-child { border-bottom: 0; }
    .login-user-role { color: #FF6F4D; font-weight: 700; }
    .login-user-id { color: #475569; font-family: monospace; font-weight: 700; }
    .login-user-fill {
        border: 0;
        border-radius: 6px;
        padding: 4px 8px;
        background: #E6EDF5;
        color: var(--color-primary);
        font-size: 0.62rem;
        font-weight: 700;
        cursor: pointer;
    }
    .login-user-fill:hover { background: var(--color-primary); color: #fff; }
    .login-user-note { color: #94A3B8; font-size: 0.58rem; }

</style>

<div class="login-page">

    <!-- LEFT: Slider/Poster (Desktop only) -->
    <div class="login-left">
        <div class="slider-container">
            <div class="slide active" id="slide-1">
                <div class="slide-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Waspada Fraud!</h3>
                <p>Jangan pernah memberikan password atau kode OTP kepada siapapun termasuk atasan. Segera laporkan jika menemukan indikasi kecurangan.</p>
            </div>
            <div class="slide" id="slide-2">
                <div class="slide-icon"><i class="fa-solid fa-bullseye"></i></div>
                <h3>Target Prospek Tercapai</h3>
                <p>Input prospek secara rutin setiap bertemu calon nasabah. Setiap prospek yang diinput adalah peluang bisnis yang terukur.</p>
            </div>
            <div class="slide" id="slide-3">
                <div class="slide-icon"><i class="fa-solid fa-handshake"></i></div>
                <h3>Layani dengan Hati</h3>
                <p>Nasabah adalah aset terpenting. Berikan pelayanan terbaik dan bangun hubungan jangka panjang untuk pertumbuhan berkelanjutan.</p>
            </div>
            <div class="slide" id="slide-4">
                <div class="slide-icon"><i class="fa-solid fa-chart-line"></i></div>
                <h3>Data Akurat, Keputusan Tepat</h3>
                <p>Pastikan setiap data yang diinput akurat dan lengkap. Data yang baik menghasilkan analisis yang tepat untuk pengambilan keputusan.</p>
            </div>

            <div class="slider-dots">
                <span class="slider-dot active" onclick="goSlide(1)"></span>
                <span class="slider-dot" onclick="goSlide(2)"></span>
                <span class="slider-dot" onclick="goSlide(3)"></span>
                <span class="slider-dot" onclick="goSlide(4)"></span>
            </div>
        </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="login-right">
        <div class="login-form-wrapper">

            <!-- Logo -->
            <div class="login-logo-mobile">
                <div class="logo-circle-sm">
                    <i class="fa-solid fa-map-location-dot" style="font-size:1.5rem; color:var(--color-primary);"></i>
                </div>
                <h4 class="fw-bold mb-1" style="font-size:1.3rem;">Visitin</h4>
                <p class="small text-white-50 mb-0">Sistem E-Prospek & Kunjungan</p>
            </div>

            <!-- Login Card -->
            <div class="login-card">
                <h5 class="fw-bold text-dark mb-3 text-center" style="font-size:1.05rem;">Silakan Login</h5>
                
                <div id="login-alert">
                    <?php if (!empty($_SESSION['login_error'])): ?>
                    <div class="alert alert-danger py-2 small mb-3" style="border-radius:8px; font-size:0.8rem;">
                        <?= $_SESSION['login_error'] ?>
                    </div>
                    <?php unset($_SESSION['login_error']); endif; ?>
                </div>

                <form id="form-login" method="POST" action="<?= BASE_APP ?>/login">
                    <input type="hidden" name="app" value="ims">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem; font-weight:700; color:#64748B;">ID Pegawai</label>
                        <input type="text" name="id_peg" id="input_id_peg" class="form-control-login" placeholder="Contoh: 102-119" required autofocus>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label" style="font-size:0.75rem; font-weight:700; color:#64748B;">Password</label>
                        <input type="password" name="password" id="input_password" class="form-control-login" placeholder="Masukkan Password" required>
                    </div>
                    
                    <button type="submit" id="btn-login" class="btn-login">Login</button>
                </form>
            </div>

            <!-- # COMPONENT: Shortcut akun untuk development; submit tetap diverifikasi SSO SIMPEG. -->
            <details class="login-user-list">
                <summary><i class="fa-solid fa-users me-1"></i> User yang sering dipakai</summary>
                <div class="mt-2">
                    <div class="login-user-row">
                        <div><span class="login-user-role">Developer</span> <span class="login-user-id ms-2">102-119</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('102-119', '123456')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">AO Kredit</span> <span class="login-user-id ms-2">111-028</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('111-028', 'bkkjtg123')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">AO Dana</span> <span class="login-user-id ms-2">111-007</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('111-007', 'bkkjtg123')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">AO Remedial</span> <span class="login-user-id ms-2">111-027</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('111-027', 'bkkjtg123')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">Superuser Cabang</span> <span class="login-user-id ms-2">113-008</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('113-008', 'bkkjtg123')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">User Non Pusat</span> <span class="login-user-id ms-2">102-118</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('102-118', 'bkkjtg123')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">User Non-AO Cabang</span> <span class="login-user-id ms-2">111-026</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('111-026', 'bkkjtg123')">Isi</button>
                    </div>
                    <div class="login-user-row">
                        <div><span class="login-user-role">Superuser Pusat</span> <span class="login-user-id ms-2">102-081</span></div>
                        <button type="button" class="login-user-fill" onclick="fillLogin('102-081', 'bkkjtg123')">Isi</button>
                    </div>
                </div>
                <div class="login-user-note mt-2">Shortcut hanya mengisi form. Validasi akun tetap dilakukan oleh SSO SIMPEG.</div>
            </details>

            <p class="text-center mt-3 mb-0" style="font-size:0.65rem; color:#94A3B8;">&copy; 2026 IT Department - BKK Jateng</p>
        </div>
    </div>

</div>

<script>
(function () {
    // Slider auto-rotate
    let currentSlide = 1;
    const totalSlides = 4;
    setInterval(() => {
        currentSlide = currentSlide >= totalSlides ? 1 : currentSlide + 1;
        goSlide(currentSlide);
    }, 5000);
})();

function goSlide(n) {
    document.querySelectorAll('.slide').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.slider-dot').forEach(d => d.classList.remove('active'));
    const slide = document.getElementById('slide-' + n);
    if (slide) slide.classList.add('active');
    const dots = document.querySelectorAll('.slider-dot');
    if (dots[n-1]) dots[n-1].classList.add('active');
}

function fillLogin(idPeg, password) {
    document.getElementById('input_id_peg').value = idPeg;
    document.getElementById('input_password').value = password;
}

(function () {
    const form = document.getElementById('form-login');
    const btn = document.getElementById('btn-login');
    const alertBox = document.getElementById('login-alert');
    const idInput = document.getElementById('input_id_peg');
    const passwordInput = document.getElementById('input_password');
    const baseApp = '<?= BASE_APP ?>';
    const loginUrl = `${baseApp}/api/?action=login`;

    function showLoginError(message) {
        alertBox.innerHTML = `
            <div class="alert alert-danger py-2 small mb-3" style="border-radius:8px; font-size:0.8rem;">
                ${String(message || 'Login gagal').replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                }[c]))}
            </div>
        `;
    }

    async function readJsonResponse(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (error) {
            throw new Error(text || `HTTP ${response.status}`);
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const idPeg = idInput.value.trim();
        const password = passwordInput.value;
        if (!idPeg || !password) {
            showLoginError('ID Pegawai dan password wajib diisi.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Memeriksa...';
        alertBox.innerHTML = '';

        try {
            const loginRes = await fetch(loginUrl, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_peg: idPeg, password, app: 'ims' })
            });
            const loginJson = await readJsonResponse(loginRes);
            if (loginJson?.status !== 200 || !loginJson?.data?.token) {
                throw new Error(loginJson?.message || 'ID Pegawai atau password salah.');
            }

            const token = loginJson.data.token;
            localStorage.setItem('visitin_token', token);
            window.location.href = `${baseApp}/home`;
        } catch (error) {
            const failedFetch = String(error?.message || '').includes('Failed to fetch');
            showLoginError(failedFetch ? 'Gagal terhubung ke server aplikasi/SSO.' : error.message);
            btn.disabled = false;
            btn.textContent = 'Login';
        }
    });
})();
</script>
