<?php
$pageTitle = 'Login - visitin-ao';
include __DIR__ . '/../views/header.php';
?>
<div class="card card-auth shadow-sm">
    <div class="card-body p-4">
        <h4 class="brand text-primary text-center mb-4">visitin-ao</h4>
        <h5 class="mb-3">Masuk</h5>

        <div id="alert-box"></div>

        <form id="form-login">
            <div class="mb-3">
                <label class="form-label">ID Pegawai</label>
                <input type="text" name="id_peg" class="form-control" placeholder="contoh: 102-119" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btn-submit">Masuk</button>
        </form>
    </div>
</div>

<script>
    const form = document.getElementById('form-login');
    const alertBox = document.getElementById('alert-box');
    const btn = document.getElementById('btn-submit');

    function showAlert(type, msg) {
        alertBox.innerHTML =
            '<div class="alert alert-' + type + ' py-2">' + msg + '</div>';
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        alertBox.innerHTML = '';
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        const fd = new FormData(form);
        const payload = {
            id_peg:   fd.get('id_peg'),
            password: fd.get('password'),
        };

        try {
            const res = await fetch('/api/auth/login', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const body = await res.json();

            if (res.ok && body.status === 200) {
                showAlert('success', body.message || 'Login berhasil');
                window.location.href = '/';
                return;
            }
            showAlert('danger', body.message || 'Login gagal');
        } catch (err) {
            showAlert('danger', 'Tidak bisa terhubung ke server');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Masuk';
        }
    });
</script>

<?php include __DIR__ . '/../views/script.php'; ?>
