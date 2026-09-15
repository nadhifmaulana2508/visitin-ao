<style>
    .profile-page {
        padding: 0 0 28px;
    }

    .profile-hero {
        position: relative;
        overflow: hidden;
        padding: 30px 20px 86px;
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: #FFFFFF;
    }

    .profile-hero-inner {
        display: flex;
        align-items: center;
        gap: 18px;
        width: min(100%, 1040px);
        margin: 0 auto;
    }

    .profile-hero-copy {
        min-width: 0;
    }

    .profile-hero h1 {
        margin: 0 0 5px;
        font-size: 1.6rem;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .profile-hero p {
        margin: 0;
        color: rgba(255, 255, 255, 0.74);
        font-size: 0.86rem;
    }

    .profile-hero .ui-status {
        margin-top: 12px;
        background: rgba(255, 255, 255, 0.13);
        color: #FFFFFF;
    }

    .profile-main {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(260px, 0.85fr);
        gap: 16px;
        width: min(calc(100% - 32px), 1040px);
        margin: -48px auto 0;
    }

    .profile-section--wide {
        grid-column: 1 / -1;
    }

    .profile-contact-list {
        display: grid;
        gap: 12px;
    }

    .profile-contact-item {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding: 12px;
        border: 1px solid var(--ui-border);
        border-radius: 10px;
        background: var(--ui-soft);
    }

    .profile-contact-item i {
        color: var(--color-accent);
        text-align: center;
    }

    .profile-contact-item a,
    .profile-contact-item span {
        overflow-wrap: anywhere;
    }

    .profile-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .profile-toolbar p {
        margin: 0;
        color: var(--ui-muted);
        font-size: 0.78rem;
    }

    .profile-photo-input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .profile-photo-trigger {
        position: relative;
        display: inline-flex;
        cursor: pointer;
    }

    .profile-photo-trigger .photo-overlay {
        position: absolute;
        right: 0;
        bottom: 0;
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        border: 3px solid var(--color-primary);
        border-radius: 50%;
        background: var(--color-accent);
        color: #FFFFFF;
        font-size: 0.75rem;
    }

    .profile-empty {
        padding: 18px;
        border-radius: 10px;
        background: var(--ui-soft);
        color: var(--ui-muted);
        font-size: 0.85rem;
        text-align: center;
    }

    [data-profile-loading] {
        color: transparent !important;
        border-radius: 5px;
        background: #E2E8F0;
        animation: profilePulse 1.2s ease-in-out infinite alternate;
    }

    @keyframes profilePulse {
        from { opacity: 0.55; }
        to { opacity: 1; }
    }

    @media (max-width: 767px) {
        .profile-hero {
            padding: 24px 16px 76px;
        }

        .profile-hero-inner {
            align-items: flex-start;
            gap: 13px;
        }

        .profile-hero .ui-avatar {
            width: 76px;
            height: 76px;
            font-size: 1.45rem;
        }

        .profile-main {
            grid-template-columns: 1fr;
            width: min(calc(100% - 24px), 1040px);
            margin-top: -40px;
        }

        .profile-section--wide {
            grid-column: auto;
        }
    }

    @media (max-width: 420px) {
        .profile-hero h1 {
            font-size: 1.15rem;
        }

        .profile-hero p {
            font-size: 0.78rem;
        }

        .profile-toolbar .ui-action-button {
            width: 100%;
        }
    }
</style>

<main class="profile-page" id="profile-page" data-profile-page data-base-app="<?= htmlspecialchars(BASE_APP, ENT_QUOTES, 'UTF-8') ?>">
    <section class="profile-hero">
        <div class="profile-hero-inner">
            <label class="profile-photo-trigger" for="profile-photo-input" title="Pilih foto profil">
                <span class="ui-avatar" id="profile-avatar" aria-hidden="true">U</span>
                <span class="photo-overlay"><i class="fa-solid fa-camera"></i></span>
            </label>
            <input class="profile-photo-input" type="file" id="profile-photo-input" accept="image/*">

            <div class="profile-hero-copy">
                <h1 data-profile-field="full_name" data-profile-loading>Memuat profil</h1>
                <p data-profile-field="job_position" data-profile-loading>Memuat jabatan</p>
                <span class="ui-status">Sesi aktif</span>
            </div>
        </div>
    </section>

    <div class="profile-main">
        <section class="ui-surface ui-section profile-section">
            <div class="profile-toolbar">
                <div>
                    <h2 class="ui-section-title"><i class="fa-solid fa-id-card"></i>Informasi Kepegawaian</h2>
                    <p>Data ditarik dari session login dan SIMPEG.</p>
                </div>
            </div>

            <div class="ui-info-grid">
                <div class="ui-info-item">
                    <span class="ui-kicker">ID Pegawai</span>
                    <div class="ui-value" data-profile-field="employee_id" data-profile-loading>-</div>
                </div>
                <div class="ui-info-item ui-info-item--wide">
                    <span class="ui-kicker">Unit Kerja</span>
                    <div class="ui-value" data-profile-field="unit_kerja" data-profile-loading>-</div>
                </div>
                <div class="ui-info-item ui-info-item--wide">
                    <span class="ui-kicker">Jabatan</span>
                    <div class="ui-value" data-profile-field="job_position" data-profile-loading>-</div>
                </div>
                <div class="ui-info-item">
                    <span class="ui-kicker">Level</span>
                    <div class="ui-value" data-profile-field="level" data-profile-loading>-</div>
                </div>
                <div class="ui-info-item">
                    <span class="ui-kicker">Kode Kantor</span>
                    <div class="ui-value" data-profile-field="kode" data-profile-loading>-</div>
                </div>
                <div class="ui-info-item ui-info-item--wide">
                    <span class="ui-kicker">Kantor Penempatan</span>
                    <div class="ui-value" data-profile-field="branch_name" data-profile-loading>-</div>
                </div>
            </div>
        </section>

        <section class="ui-surface ui-section profile-section">
            <h2 class="ui-section-title"><i class="fa-solid fa-address-card"></i>Kontak Dan Akses</h2>
            <div class="profile-contact-list">
                <div class="profile-contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="ui-value" data-profile-field="email" data-profile-loading>-</span>
                </div>
                <div class="profile-contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span class="ui-value" data-profile-field="telp" data-profile-loading>-</span>
                </div>
                <div class="profile-contact-item">
                    <i class="fa-solid fa-user-shield"></i>
                    <span class="ui-value" data-profile-field="role_label" data-profile-loading>-</span>
                </div>
                <div class="profile-contact-item">
                    <i class="fa-solid fa-users-gear"></i>
                    <span class="ui-value" data-profile-field="group_jabatan" data-profile-loading>-</span>
                </div>
            </div>
        </section>

        <section class="ui-surface ui-section profile-section profile-section--wide">
            <h2 class="ui-section-title"><i class="fa-solid fa-text-height"></i>Preferensi Tampilan</h2>
            <div class="ui-settings-grid">
                <div class="ui-setting-control">
                    <label for="profile-font-family">Jenis Font</label>
                    <select id="profile-font-family">
                        <option value="system">Default Sistem</option>
                        <option value="inter">Inter</option>
                        <option value="roboto">Roboto</option>
                        <option value="jakarta">Plus Jakarta Sans</option>
                        <option value="nunito">Nunito Sans</option>
                        <option value="arial">Arial</option>
                    </select>
                </div>
                <div class="ui-setting-control">
                    <label for="profile-font-scale"><span>Ukuran Text</span><output id="profile-font-scale-value">100%</output></label>
                    <input type="range" id="profile-font-scale" min="90" max="120" step="5" value="100">
                </div>
            </div>
            <div class="ui-settings-status" id="profile-preference-status" aria-live="polite"></div>
        </section>

        <section class="ui-surface ui-section profile-section profile-section--wide">
            <div class="profile-toolbar">
                <div>
                    <h2 class="ui-section-title"><i class="fa-solid fa-sliders"></i>Pengaturan Akun</h2>
                    <p>Kelola sesi dan perbarui tampilan data profil dari sumber login.</p>
                </div>
                <button type="button" class="ui-action-button ui-action-button--quiet" id="profile-refresh">
                    <i class="fa-solid fa-arrows-rotate"></i><span>Segarkan Data</span>
                </button>
            </div>
        </section>

        <div class="profile-empty profile-section--wide" id="profile-error" hidden></div>
    </div>
</main>

<script>
(function () {
    'use strict';

    const page = document.getElementById('profile-page');
    if (!page) return;

    const baseApp = page.dataset.baseApp || '';
    const avatar = document.getElementById('profile-avatar');
    const photoInput = document.getElementById('profile-photo-input');
    const refreshButton = document.getElementById('profile-refresh');
    const errorBox = document.getElementById('profile-error');
    const fontFamilyInput = document.getElementById('profile-font-family');
    const fontScaleInput = document.getElementById('profile-font-scale');
    const fontScaleValue = document.getElementById('profile-font-scale-value');
    const preferenceStatus = document.getElementById('profile-preference-status');
    let preferenceUserKey = '';
    let preferenceBound = false;
    const roleLabels = {
        developer: 'Developer',
        superuser: 'Superuser',
        ao_kredit: 'AO Kredit',
        ao_dana: 'AO Dana',
        ao_remedial: 'AO Remedial',
        staff: 'Staff'
    };

    function setLoading(isLoading) {
        page.querySelectorAll('[data-profile-field]').forEach(function (element) {
            element.toggleAttribute('data-profile-loading', isLoading);
        });
    }

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = false;
    }

    function hideError() {
        errorBox.hidden = true;
        errorBox.textContent = '';
    }

    function renderProfile(user) {
        const values = {
            full_name: user.full_name,
            employee_id: user.employee_id,
            unit_kerja: user.unit_kerja,
            job_position: user.job_position,
            level: user.level,
            kode: user.kode || user.kode_kantor,
            branch_name: user.branch_name || user.branch,
            email: user.email,
            telp: user.telp,
            role_label: roleLabels[user.role] || user.role,
            group_jabatan: user.group_jabatan
        };

        page.querySelectorAll('[data-profile-field]').forEach(function (element) {
            const key = element.dataset.profileField;
            element.textContent = values[key] || '-';
        });

        avatar.textContent = window.VisitinUI
            ? window.VisitinUI.initials(values.full_name)
            : String(values.full_name || 'U').charAt(0).toUpperCase();
        avatar.style.background = 'var(--color-accent)';

        document.title = `${values.full_name || 'Profil'} - Visitin AO`;
        setupPreferences(user.employee_id);
    }

    function setupPreferences(userKey) {
        if (!window.VisitinUI || !window.VisitinUI.preferences || !userKey) return;
        preferenceUserKey = userKey;
        const preferences = window.VisitinUI.preferences.load(preferenceUserKey);
        fontFamilyInput.value = preferences.fontFamily;
        fontScaleInput.value = String(Math.round(preferences.fontScale * 100));
        fontScaleValue.textContent = `${fontScaleInput.value}%`;
        window.VisitinUI.preferences.apply(preferences);

        if (preferenceBound) return;
        const savePreferences = function () {
            const saved = window.VisitinUI.preferences.save(preferenceUserKey, {
                fontFamily: fontFamilyInput.value,
                fontScale: Number(fontScaleInput.value) / 100
            });
            fontScaleValue.textContent = `${Math.round(saved.fontScale * 100)}%`;
            preferenceStatus.textContent = 'Preferensi tersimpan untuk akun ini.';
        };
        fontFamilyInput.addEventListener('change', savePreferences);
        fontScaleInput.addEventListener('input', savePreferences);
        preferenceBound = true;
    }

    async function readJson(response) {
        const body = await response.text();
        let json;
        try {
            json = JSON.parse(body);
        } catch (error) {
            throw new Error(body || `HTTP ${response.status}`);
        }
        if (!response.ok || json.status !== 200 || !json.data) {
            throw new Error(json.message || 'Data profil tidak dapat dimuat.');
        }
        return json.data;
    }

    async function loadProfile() {
        setLoading(true);
        hideError();
        refreshButton.disabled = true;

        try {
            const response = await fetch(`${baseApp}/api/?action=whoami`, {
                method: 'GET',
                credentials: 'include',
                headers: { Accept: 'application/json' }
            });
            const user = await readJson(response);
            renderProfile(user);
        } catch (error) {
            if (/401|Unauthorized|Token/i.test(error.message)) {
                window.location.href = `${baseApp}/login`;
                return;
            }
            showError(error.message || 'Data profil tidak dapat dimuat.');
        } finally {
            setLoading(false);
            refreshButton.disabled = false;
        }
    }

    photoInput.addEventListener('change', function () {
        const file = photoInput.files && photoInput.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function () {
            avatar.textContent = '';
            avatar.style.background = `center / cover no-repeat url("${reader.result}")`;
        };
        reader.readAsDataURL(file);
    });

    refreshButton.addEventListener('click', loadProfile);

    loadProfile();
})();
</script>
