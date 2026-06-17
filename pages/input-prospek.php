<?php
// Cek role untuk auto-delegasi
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_ao_kredit = in_array('AO_KREDIT', $user_permissions) || $user_role === 'developer';
$is_ao_dana = in_array('AO_DANA', $user_permissions) || $user_role === 'developer';
$is_ao_remedial = (in_array('AO_REMEDIAL_FE', $user_permissions) || in_array('AO_REMEDIAL_BE', $user_permissions)) || $user_role === 'developer';
?>

<style>
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 45px 20px;
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    .form-container { margin: -25px 15px 20px 15px; position: relative; z-index: 10; }
    .form-card {
        background-color: #ffffff; border-radius: 16px; margin-bottom: 15px;
        padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #F1F5F9;
    }
    .section-title {
        font-size: 0.8rem; font-weight: 800; color: var(--color-primary);
        text-transform: uppercase; margin-bottom: 15px;
        border-bottom: 2px solid #F4F7F6; padding-bottom: 10px;
    }
    .form-label-custom {
        font-size: 0.7rem; font-weight: 700; color: #64748B;
        margin-bottom: 5px; display: block; text-transform: uppercase;
    }
    .input-custom {
        background-color: #ffffff; border: 1px solid #CBD5E1; border-radius: 10px;
        padding: 11px 14px; font-size: 0.85rem; font-weight: 600; color: #1E293B; width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .input-custom:focus {
        border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(10,25,49,0.08); outline: none;
    }
    .input-custom:disabled, .input-custom[readonly] {
        background-color: #F8FAFC; color: #64748B; cursor: not-allowed;
    }
    .input-custom::placeholder { color: #94A3B8; font-weight: 400; }

    /* Chip jenis prospek */
    .chip-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .chip-radio { display: none; }
    .chip-label {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;
        background: #F1F5F9; color: #475569; border: 2px solid transparent;
        cursor: pointer; transition: all 0.2s; user-select: none;
    }
    .chip-label:active { transform: scale(0.95); }
    .chip-radio:checked + .chip-label {
        background: rgba(10,25,49,0.08); color: var(--color-primary);
        border-color: var(--color-primary);
    }
    .chip-label i { font-size: 0.8rem; }

    /* Auto delegasi badge */
    .badge-auto-delegasi {
        background: #E8F5E9; color: #388E3C; font-size: 0.65rem;
        padding: 4px 10px; border-radius: 8px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 4px;
    }

    .btn-submit-prospek {
        background-color: var(--color-accent); color: white; border: none;
        border-radius: 12px; font-weight: 800; font-size: 0.95rem;
        padding: 14px; width: 100%; box-shadow: 0 8px 15px rgba(255,123,84,0.3);
        transition: 0.2s;
    }
    .btn-submit-prospek:active { transform: scale(0.98); box-shadow: 0 4px 8px rgba(255,123,84,0.2); }
    .btn-submit-prospek:disabled { opacity: 0.6; }

    /* Info box */
    .info-box {
        background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px;
        padding: 10px 14px; font-size: 0.75rem; color: #92400E;
        display: flex; align-items: flex-start; gap: 8px;
    }
    .info-box i { margin-top: 2px; }
</style>

<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/daftar-prospek" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
            <h5 class="fw-bold mb-0">Input Prospek Baru</h5>
            <p class="small text-white-50 mb-0" style="font-size: 0.7rem;">Lengkapi data calon nasabah</p>
        </div>
    </div>
</div>

<div class="form-container">
    <form id="form-prospek">

        <!-- JENIS PROSPEK -->
        <div class="form-card">
            <h6 class="section-title"><i class="fa-solid fa-tag me-2 text-accent"></i>Jenis Prospek</h6>

            <div class="chip-group" id="chip-jenis">
                <input type="radio" name="prospect_type" value="KREDIT" id="type_kredit" class="chip-radio" required>
                <label for="type_kredit" class="chip-label"><i class="fa-solid fa-money-bill-transfer"></i> Kredit</label>

                <input type="radio" name="prospect_type" value="TABUNGAN" id="type_tabungan" class="chip-radio">
                <label for="type_tabungan" class="chip-label"><i class="fa-solid fa-piggy-bank"></i> Tabungan</label>

                <input type="radio" name="prospect_type" value="DEPOSITO" id="type_deposito" class="chip-radio">
                <label for="type_deposito" class="chip-label"><i class="fa-solid fa-vault"></i> Deposito</label>

                <input type="radio" name="prospect_type" value="PEMBELI_ASET" id="type_aset" class="chip-radio">
                <label for="type_aset" class="chip-label"><i class="fa-solid fa-building"></i> Pembeli Aset</label>

                <input type="radio" name="prospect_type" value="DEBITUR_EXISTING" id="type_existing" class="chip-radio">
                <label for="type_existing" class="chip-label"><i class="fa-solid fa-user-check"></i> Debitur Existing</label>
            </div>

            <?php if ($is_ao): ?>
            <div class="mt-3" id="auto-delegasi-info" style="display:none;">
                <span class="badge-auto-delegasi">
                    <i class="fa-solid fa-bolt"></i> Auto-delegasi: prospek langsung masuk pipeline Anda
                </span>
            </div>
            <?php else: ?>
            <div class="info-box mt-3">
                <i class="fa-solid fa-circle-info"></i>
                <span>Prospek Anda akan masuk ke daftar tunggu delegasi. Superuser akan mendelegasikan ke AO yang tepat.</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- DATA CALON NASABAH -->
        <div class="form-card">
            <h6 class="section-title"><i class="fa-solid fa-user-plus me-2 text-accent"></i>Data Calon Nasabah</h6>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="input-custom" name="customer_name" placeholder="Nama calon nasabah" required>
                </div>

                <div class="col-6">
                    <label class="form-label-custom">No. Identitas (KTP)</label>
                    <input type="text" class="input-custom" name="identity_number" placeholder="16 digit" maxlength="16">
                </div>

                <div class="col-6">
                    <label class="form-label-custom">No. HP / WA <span class="text-danger">*</span></label>
                    <input type="tel" class="input-custom" name="phone_number" placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Produk yang Diminati</label>
                    <input type="text" class="input-custom" name="product_interest" placeholder="Contoh: KMK, Deposito 12bln, dll">
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Nominal Estimasi (Rp)</label>
                    <input type="number" class="input-custom" name="estimated_amount" placeholder="0" min="0">
                </div>
            </div>
        </div>

        <!-- ALAMAT -->
        <div class="form-card">
            <h6 class="section-title"><i class="fa-solid fa-map-location-dot me-2 text-accent"></i>Alamat</h6>

            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label-custom">Provinsi <span class="text-danger">*</span></label>
                    <select class="input-custom" name="provinsi" id="sel-provinsi" required>
                        <option value="">-- Pilih --</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label-custom">Kab / Kota <span class="text-danger">*</span></label>
                    <select class="input-custom" name="kab_kota" id="sel-kabkota" disabled>
                        <option value="">-- Pilih Provinsi --</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label-custom">Kecamatan <span class="text-danger">*</span></label>
                    <select class="input-custom" name="kecamatan" id="sel-kecamatan" disabled>
                        <option value="">-- Pilih Kab/Kota --</option>
                    </select>
                </div>

                <div class="col-6">
                    <label class="form-label-custom">Desa / Kelurahan <span class="text-danger">*</span></label>
                    <select class="input-custom" name="desa" id="sel-desa" disabled>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Alamat Lengkap</label>
                    <textarea class="input-custom" name="address" rows="2" placeholder="Jalan, RT/RW, No. Rumah..."></textarea>
                </div>
            </div>
        </div>

        <!-- KETERANGAN -->
        <div class="form-card">
            <h6 class="section-title"><i class="fa-solid fa-note-sticky me-2 text-accent"></i>Keterangan</h6>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-custom">Catatan / Keterangan Prospek</label>
                    <textarea class="input-custom" name="description" rows="3" placeholder="Informasi tambahan tentang calon nasabah..."></textarea>
                </div>
            </div>
        </div>

        <!-- SUBMIT -->
        <button type="submit" class="btn-submit-prospek" id="btn-submit">
            <i class="fa-solid fa-paper-plane me-2"></i> Simpan Prospek
        </button>

    </form>
</div>

<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const isAO = <?= $is_ao ? 'true' : 'false' ?>;
    const isAOKredit = <?= $is_ao_kredit ? 'true' : 'false' ?>;
    const isAODana = <?= $is_ao_dana ? 'true' : 'false' ?>;
    const isAORemedial = <?= $is_ao_remedial ? 'true' : 'false' ?>;

    // =========================================
    // AUTO-DELEGASI INFO
    // =========================================
    const chipRadios = document.querySelectorAll('.chip-radio');
    const autoDelegasiInfo = document.getElementById('auto-delegasi-info');

    chipRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (!isAO || !autoDelegasiInfo) return;
            const val = this.value;
            let show = false;

            if (val === 'KREDIT' && isAOKredit) show = true;
            if (val === 'TABUNGAN' && isAODana) show = true;
            if (val === 'DEPOSITO' && isAODana) show = true;
            if (val === 'PEMBELI_ASET' && isAORemedial) show = true;
            if (val === 'DEBITUR_EXISTING' && isAOKredit) show = true;

            autoDelegasiInfo.style.display = show ? 'block' : 'none';
        });
    });

    // =========================================
    // DROPDOWN WILAYAH (API emsifa / dummy)
    // =========================================
    const API_WILAYAH = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    const selProv = document.getElementById('sel-provinsi');
    const selKab = document.getElementById('sel-kabkota');
    const selKec = document.getElementById('sel-kecamatan');
    const selDesa = document.getElementById('sel-desa');

    // Load provinsi
    async function loadProvinsi() {
        try {
            const res = await fetch(API_WILAYAH + '/provinces.json');
            const data = await res.json();
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                opt.dataset.name = p.name;
                selProv.appendChild(opt);
            });
        } catch(e) {
            // Fallback dummy
            const dummy = [{id:'33', name:'JAWA TENGAH'},{id:'32', name:'JAWA BARAT'},{id:'34', name:'DI YOGYAKARTA'}];
            dummy.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                opt.dataset.name = p.name;
                selProv.appendChild(opt);
            });
        }
    }

    selProv.addEventListener('change', async function() {
        const provId = this.value;
        selKab.innerHTML = '<option value="">Memuat...</option>';
        selKab.disabled = true;
        selKec.innerHTML = '<option value="">-- Pilih Kab/Kota --</option>';
        selKec.disabled = true;
        selDesa.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        selDesa.disabled = true;

        if (!provId) return;

        try {
            const res = await fetch(API_WILAYAH + '/regencies/' + provId + '.json');
            const data = await res.json();
            selKab.innerHTML = '<option value="">-- Pilih --</option>';
            data.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.textContent = k.name;
                opt.dataset.name = k.name;
                selKab.appendChild(opt);
            });
            selKab.disabled = false;
        } catch(e) {
            selKab.innerHTML = '<option value="">Gagal memuat</option>';
        }
    });

    selKab.addEventListener('change', async function() {
        const kabId = this.value;
        selKec.innerHTML = '<option value="">Memuat...</option>';
        selKec.disabled = true;
        selDesa.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        selDesa.disabled = true;

        if (!kabId) return;

        try {
            const res = await fetch(API_WILAYAH + '/districts/' + kabId + '.json');
            const data = await res.json();
            selKec.innerHTML = '<option value="">-- Pilih --</option>';
            data.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.textContent = k.name;
                opt.dataset.name = k.name;
                selKec.appendChild(opt);
            });
            selKec.disabled = false;
        } catch(e) {
            selKec.innerHTML = '<option value="">Gagal memuat</option>';
        }
    });

    selKec.addEventListener('change', async function() {
        const kecId = this.value;
        selDesa.innerHTML = '<option value="">Memuat...</option>';
        selDesa.disabled = true;

        if (!kecId) return;

        try {
            const res = await fetch(API_WILAYAH + '/villages/' + kecId + '.json');
            const data = await res.json();
            selDesa.innerHTML = '<option value="">-- Pilih --</option>';
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                opt.dataset.name = d.name;
                selDesa.appendChild(opt);
            });
            selDesa.disabled = false;
        } catch(e) {
            selDesa.innerHTML = '<option value="">Gagal memuat</option>';
        }
    });

    loadProvinsi();

    // =========================================
    // FORM SUBMIT
    // =========================================
    const form = document.getElementById('form-prospek');
    const btnSubmit = document.getElementById('btn-submit');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validasi jenis prospek
        const prospectType = form.querySelector('input[name="prospect_type"]:checked');
        if (!prospectType) {
            showToast('<i class="fa-solid fa-triangle-exclamation me-2"></i> Pilih jenis prospek terlebih dahulu', 'warning');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Menyimpan...';

        const fd = new FormData(form);
        const payload = {
            prospect_type: fd.get('prospect_type'),
            customer_name: fd.get('customer_name'),
            identity_number: fd.get('identity_number') || null,
            phone_number: fd.get('phone_number'),
            product_interest: fd.get('product_interest') || null,
            estimated_amount: fd.get('estimated_amount') ? parseInt(fd.get('estimated_amount')) : 0,
            provinsi: selProv.options[selProv.selectedIndex]?.dataset?.name || '',
            kab_kota: selKab.options[selKab.selectedIndex]?.dataset?.name || '',
            kecamatan: selKec.options[selKec.selectedIndex]?.dataset?.name || '',
            desa: selDesa.options[selDesa.selectedIndex]?.dataset?.name || '',
            address: fd.get('address') || '',
            description: fd.get('description') || '',
            is_ao_input: isAO,
        };

        try {
            const res = await fetch(BASE_APP + '/api/?action=create_prospect', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const body = await res.json().catch(() => ({}));

            if (res.ok && (body.status === 200 || body.status === 201)) {
                showToast('<i class="fa-solid fa-check-circle me-2"></i> Prospek berhasil disimpan!', 'success');
                setTimeout(() => {
                    window.location.href = BASE_APP + '/daftar-prospek';
                }, 1000);
            } else {
                showToast('<i class="fa-solid fa-xmark me-2"></i> ' + (body.message || 'Gagal menyimpan'), 'danger');
            }
        } catch(err) {
            showToast('<i class="fa-solid fa-wifi me-2"></i> Tidak bisa terhubung ke server', 'danger');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> Simpan Prospek';
        }
    });
})();
</script>
