<style>
    /* Styling pakai dasar login wrapper */
    .login-wrapper {
        background: var(--color-bg);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding: 20px;
    }
    .back-btn {
        color: var(--color-primary);
        font-size: 1.5rem;
        text-decoration: none;
    }
</style>

<div class="login-wrapper">
    <div class="d-flex align-items-center mb-4 mt-3">
        <a href="<?= BASE_APP ?>/login" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        <h5 class="mb-0 ms-3 fw-bold text-dark">Aktivasi / Reset Password</h5>
    </div>

    <div class="card border-0 rounded-4 shadow-sm p-3">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="fa-solid fa-user-shield fs-1 mb-3" style="color: var(--color-accent);"></i>
                <p class="text-muted small">Masukkan <b>ID Pegawai</b> dan <b>No HP</b> yang terdaftar di SIMPEG untuk melakukan aktivasi atau reset password.</p>
            </div>
            
            <form action="<?= BASE_APP ?>/login" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">ID Pegawai (NIK)</label>
                    <input type="text" class="form-control bg-light border-0 py-2" placeholder="Contoh: 123456" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nomor WhatsApp / HP</label>
                    <input type="number" class="form-control bg-light border-0 py-2" placeholder="0812xxxxxx" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Buat Password Baru</label>
                    <input type="password" class="form-control bg-light border-0 py-2" placeholder="Minimal 6 karakter" required>
                </div>
                
                <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background-color: var(--color-primary); border-radius: 12px;">Verifikasi & Simpan</button>
            </form>
        </div>
    </div>
</div>