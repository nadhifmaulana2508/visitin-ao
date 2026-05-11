<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Helper API fetch dengan cookie terkirim otomatis
    window.api = async function (path, options = {}) {
        const res = await fetch(path, {
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
            ...options,
        });
        const body = await res.json().catch(() => ({}));
        return { status: res.status, body };
    };

    // Tombol logout (opsional, muncul kalau ada #btn-logout)
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btn-logout');
        if (btn) {
            btn.classList.remove('d-none');
            btn.addEventListener('click', async () => {
                await api('/api/auth/logout', { method: 'POST' });
                window.location.href = '/pages/login.php';
            });
        }
    });
</script>
</body>
</html>
