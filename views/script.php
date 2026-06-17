    </div><!-- /.mobile-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // =========================================
    // GLOBAL UTILITIES
    // =========================================
    
    // Toast notification
    function showToast(message, type = 'info', duration = 3000) {
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();
        
        const colors = {
            success: '#388E3C',
            danger: '#D32F2F',
            warning: '#F57C00',
            info: '#0A1931'
        };
        
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.background = colors[type] || colors.info;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(-20px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Format rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    // Get user data from localStorage
    function getUserData() {
        try {
            return JSON.parse(localStorage.getItem('user_data') || '{}');
        } catch(e) {
            return {};
        }
    }

    function getUserRole() {
        return localStorage.getItem('user_role') || 'staff';
    }

    function getUserPermissions() {
        try {
            return JSON.parse(localStorage.getItem('user_permissions') || '[]');
        } catch(e) {
            return [];
        }
    }

    function hasPermission(code) {
        return getUserPermissions().includes(code);
    }

    // Check if user is AO (has any AO permission)
    function isAO() {
        const perms = getUserPermissions();
        return perms.some(p => p.startsWith('AO_'));
    }
    </script>
</body>
</html>
