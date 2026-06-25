<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_pusat = ($user_kode_kantor === '000');
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .page-header { background: linear-gradient(135deg, var(--color-primary), var(--color-secondary)); color: white; padding: 20px; }
    .page-header h4 { font-weight: 800; margin: 0; font-size: 1.1rem; }
    .page-header p { margin: 4px 0 0 0; font-size: 0.75rem; opacity: 0.7; }

    #form-prospek { width: 100%; max-width: 1100px; margin: 0 auto; padding-bottom: 10px; }
    .form-section { background: #fff; border-radius: 12px; padding: 20px; margin: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    @media(min-width:768px) { .form-section { margin: 20px 32px; padding: 28px; } }
    @media(min-width:1200px) { .form-section { margin-left: 0; margin-right: 0; } }
    .form-section + .form-section { margin-top: 0; }

    .section-label { font-size: 0.72rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.5px; }
    .field-label { font-size: 0.75rem; font-weight: 700; color: #374151; margin-bottom: 6px; display: block; }
    .field-label .req { color: #DC2626; }

    .field-input {
        width: 100%; border: 1px solid #D1D5DB; border-radius: 8px; padding: 10px 12px;
        font-size: 0.88rem; color: #1F2937; background: #fff; transition: border 0.2s;
        min-height: 42px;
    }
    .field-input:focus { border-color: var(--color-primary); outline: none; box-shadow: 0 0 0 2px rgba(10,25,49,0.1); }
    .field-input:disabled { background: #F3F4F6; color: #6B7280; }
    .field-input::placeholder { color: #9CA3AF; }
    select.field-input { appearance: auto; }
    textarea.field-input { resize: vertical; min-height: 70px; }
    .field-help { display:block; margin-top:4px; font-size:0.65rem; color:#64748B; }
    .search-select { position: relative; }
    .search-options {
        display: none; position: absolute; left: 0; right: 0; top: calc(100% + 4px);
        max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #D1D5DB;
        border-radius: 10px; box-shadow: 0 12px 24px rgba(15,23,42,0.12); z-index: 1040;
    }
    .search-select.open .search-options { display: block; }
    .search-option {
        width: 100%; border: 0; background: #fff; text-align: left; padding: 10px 12px;
        font-size: 0.82rem; font-weight: 650; color: #334155; cursor: pointer;
    }
    .search-option:hover, .search-option:focus { background: #F4F7F6; outline: none; }
    .search-empty { padding: 12px; font-size: 0.76rem; color: #94A3B8; }
    .search-select.disabled .search-options { display: none; }

    .row-fields { display: grid; gap: 12px; grid-template-columns: 1fr; }
    @media(min-width:576px) { .row-fields { grid-template-columns: 1fr 1fr; } }
    @media(min-width:992px) { .row-fields.cols-3 { grid-template-columns: 1fr 1fr 1fr; } }

    /* Foto */
    .foto-area { border: 2px dashed #D1D5DB; border-radius: 12px; padding: 16px; text-align: center; position: relative; min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; transition: border-color 0.2s; }
    .foto-area.has-foto { border-color: var(--color-primary); border-style: solid; }
    .foto-area img { max-width: 100%; max-height: 250px; border-radius: 8px; object-fit: cover; }
    .foto-btns { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
    .btn-foto { padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; border: 1px solid #D1D5DB; background: #F9FAFB; color: #374151; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-foto:hover { background: #E5E7EB; }
    .btn-foto.primary { background: var(--color-primary); color: white; border-color: var(--color-primary); }
    .btn-foto.primary:hover { background: var(--color-secondary); }
    .foto-status { font-size: 0.7rem; color: #6B7280; }

    /* GPS */
    .gps-row { display: flex; gap: 8px; align-items: stretch; }
    .gps-row .field-input { flex: 1; }
    .btn-gps { min-width: 46px; border: none; border-radius: 8px; background: var(--color-primary); color: white; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .btn-gps:hover { background: var(--color-secondary); }
    .btn-gps.done { background: #16A34A; }
    #static-map { width: 100%; height: 180px; border-radius: 8px; margin-top: 10px; border: 1px solid #E5E7EB; display: none; }
    @media(min-width:768px) { #static-map { height: 220px; } }

    /* Submit */
    .btn-submit { width: 100%; padding: 14px; border: none; border-radius: 10px; background: #FF7B54; color: white; font-weight: 800; font-size: 0.95rem; cursor: pointer; margin: 16px 0 80px 0; box-shadow: 0 4px 12px rgba(255,123,84,0.3); transition: 0.2s; }
    .btn-submit:hover { background: #E66A45; }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
    @media(min-width:1200px) { .submit-wrap { padding-left: 0 !important; padding-right: 0 !important; } }

    /* Info badge */
    .info-badge { background: #ECFDF5; color: #065F46; font-size: 0.72rem; padding: 8px 12px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 6px; margin-top: 10px; }
    .info-badge.warn { background: #FFFBEB; color: #92400E; }

    /* Modal kamera */
    #cam-video { width: 100%; border-radius: 10px; background: #000; max-height: 320px; object-fit: cover; }
</style>

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_APP ?>/daftar-prospek" style="color:white;font-size:1.2rem;"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h4>Input Prospek Baru</h4>
            <p>Isi data calon nasabah untuk diproses AO</p>
        </div>
    </div>
</div>

<form id="form-prospek" method="POST" action="<?= BASE_APP ?>/api/?action=prospect_create" enctype="multipart/form-data">
<input type="hidden" name="prospect_type" id="inp-prospect-type">

<!-- DATA USAHA & PRODUK -->
<div class="form-section">
    <div class="section-label"><i class="fa-solid fa-briefcase me-1"></i> Data Usaha & Produk</div>
    <div class="row-fields">
        <div>
            <label class="field-label">Jenis Usaha <span class="req">*</span></label>
            <select class="field-input" name="jenis_usaha" id="sel-jenis-usaha" required>
                <option value="">-- Pilih Jenis Usaha --</option>
                <option value="Pertanian">Pertanian</option>
                <option value="Perikanan">Perikanan</option>
                <option value="Peternakan">Peternakan</option>
                <option value="Perdagangan">Perdagangan</option>
                <option value="Jasa">Jasa</option>
                <option value="Industri Rumahan">Industri Rumahan</option>
                <option value="Karyawan">Karyawan</option>
                <option value="Wiraswasta">Wiraswasta</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div>
            <label class="field-label">Rekomendasi Produk <span class="req">*</span></label>
            <select class="field-input" name="rekomendasi_produk" id="sel-produk" required>
                <option value="">-- Pilih Produk --</option>
                <option value="Tabungan">Tabungan</option>
                <option value="Deposito">Deposito</option>
                <option value="Kredit">Kredit</option>
                <option value="Aset">Aset</option>
            </select>
        </div>
    </div>
    <div style="margin-top:12px;">
        <label class="field-label">Keterangan Usaha</label>
        <textarea class="field-input" name="keterangan_usaha" placeholder="Contoh: jualan mainan anak..." rows="2"></textarea>
    </div>
</div>

<!-- DATA NASABAH -->
<div class="form-section">
    <div class="section-label"><i class="fa-solid fa-user me-1"></i> Data Calon Nasabah</div>
    <div class="row-fields">
        <div>
            <label class="field-label">Nama Lengkap <span class="req">*</span></label>
            <input type="text" class="field-input" name="customer_name" placeholder="Nama calon nasabah" required>
        </div>
        <div>
            <label class="field-label">No. HP / WhatsApp <span class="req">*</span></label>
            <input type="tel" class="field-input" name="phone_number" placeholder="08xxxxxxxxxx" required>
        </div>
    </div>
    <div class="row-fields" style="margin-top:12px;">
        <div>
            <label class="field-label">No. KTP (Opsional)</label>
            <input type="text" class="field-input" name="identity_number" placeholder="16 digit" maxlength="16">
        </div>
        <div>
            <label class="field-label">Cabang Tujuan <span class="req">*</span></label>
            <div class="search-select" id="combo-cabang">
                <input type="text" class="field-input" id="inp-cabang-search" placeholder="Ketik kode/nama cabang" autocomplete="off" required>
                <div class="search-options" id="menu-cabang"></div>
            </div>
            <input type="hidden" name="kode_kantor" id="sel-cabang">
            <small class="field-help">Contoh: 001 atau Kc. Utama.</small>
        </div>
    </div>

    <?php if ($is_ao): ?>
    <div class="info-badge" id="badge-auto" style="display:none;">
        <i class="fa-solid fa-user-check"></i> Prospek akan didelegasikan ke AO sesuai produk dan cabang tujuan.
    </div>
    <?php else: ?>
    <div class="info-badge warn">
        <i class="fa-solid fa-info-circle"></i> Prospek akan menunggu delegasi dari Superuser ke AO yang tepat.
    </div>
    <?php endif; ?>
</div>

<!-- LOKASI -->
<div class="form-section">
    <div class="section-label"><i class="fa-solid fa-map-location-dot me-1"></i> Lokasi</div>
    <div class="row-fields">
        <div>
            <label class="field-label">Alamat & Koordinat</label>
            <div class="gps-row">
                <input type="text" class="field-input" id="geo-alamat" placeholder="Klik tombol untuk ambil lokasi GPS" readonly>
                <button type="button" class="btn-gps" id="btn-gps" title="Ambil Lokasi"><i class="fa-solid fa-location-crosshairs"></i></button>
            </div>
            <input type="hidden" name="latitude" id="inp-lat">
            <input type="hidden" name="longitude" id="inp-lng">
            <input type="hidden" name="geo_address" id="inp-geo-addr">
            <div id="static-map"></div>
        </div>
        <div>
            <label class="field-label">Wilayah Administratif</label>
            <div class="search-select" id="combo-provinsi" style="margin-bottom:8px;">
                <input type="text" class="field-input" id="inp-provinsi" placeholder="Ketik provinsi" autocomplete="off">
                <div class="search-options" id="menu-provinsi"></div>
            </div>
            <input type="hidden" name="provinsi" id="sel-provinsi">
            <input type="hidden" id="sel-provinsi-id">

            <div class="search-select disabled" id="combo-kabkota" style="margin-bottom:8px;">
                <input type="text" class="field-input" id="inp-kabkota" placeholder="Ketik kab/kota" autocomplete="off" disabled>
                <div class="search-options" id="menu-kabkota"></div>
            </div>
            <input type="hidden" name="kab_kota" id="sel-kabkota">
            <input type="hidden" id="sel-kabkota-id">

            <div class="search-select disabled" id="combo-kecamatan" style="margin-bottom:8px;">
                <input type="text" class="field-input" id="inp-kecamatan" placeholder="Ketik kecamatan" autocomplete="off" disabled>
                <div class="search-options" id="menu-kecamatan"></div>
            </div>
            <input type="hidden" name="kecamatan" id="sel-kecamatan">
            <input type="hidden" id="sel-kecamatan-id">

            <div class="search-select disabled" id="combo-desa">
                <input type="text" class="field-input" id="inp-desa" placeholder="Ketik desa/kelurahan" autocomplete="off" disabled>
                <div class="search-options" id="menu-desa"></div>
            </div>
            <input type="hidden" name="desa" id="sel-desa">
            <input type="hidden" id="sel-desa-id">
        </div>
    </div>
    <div style="margin-top:12px;">
        <label class="field-label">Alamat Lengkap (Detail)</label>
        <textarea class="field-input" name="address" placeholder="Jalan, RT/RW, No. Rumah, Patokan..." rows="2"></textarea>
    </div>
</div>

<!-- FOTO -->
<div class="form-section">
    <div class="section-label"><i class="fa-solid fa-camera me-1"></i> Foto Prospek</div>
    <div class="foto-area" id="foto-area">
        <img id="foto-preview" src="" alt="" style="display:none;">
        <div id="foto-placeholder">
            <i class="fa-solid fa-image" style="font-size:2rem; color:#D1D5DB;"></i>
            <p class="foto-status" id="foto-status">Belum ada foto. Upload atau ambil dari kamera.</p>
        </div>
        <div class="foto-btns">
            <button type="button" class="btn-foto" onclick="document.getElementById('inp-file').click()">
                <i class="fa-solid fa-upload"></i> Upload Foto
            </button>
            <button type="button" class="btn-foto primary" id="btn-camera">
                <i class="fa-solid fa-camera"></i> Ambil Foto
            </button>
        </div>
    </div>
    <input type="file" id="inp-file" class="d-none" accept="image/*" onchange="handleFileUpload(this)">
    <input type="hidden" name="foto_base64" id="inp-foto-b64">
</div>

<!-- KETERANGAN -->
<div class="form-section">
    <div class="section-label"><i class="fa-solid fa-note-sticky me-1"></i> Keterangan Tambahan</div>
    <textarea class="field-input" name="description" placeholder="Informasi tambahan..." rows="3"></textarea>
</div>

<!-- SUBMIT -->
<div class="submit-wrap" style="padding: 0 16px;">
    <button type="submit" class="btn-submit" id="btn-submit">
        <i class="fa-solid fa-paper-plane me-2"></i> Simpan Prospek
    </button>
</div>

</form>

<!-- Modal Kamera -->
<div class="modal fade" id="modalCam" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="fw-bold"><i class="fa-solid fa-camera me-2 text-primary"></i>Ambil Foto</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <video id="cam-video" autoplay playsinline></video>
                <canvas id="cam-canvas" class="d-none"></canvas>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button type="button" class="btn-foto" id="btn-flip"><i class="fa-solid fa-rotate"></i> Flip</button>
                    <button type="button" class="btn-foto primary" id="btn-snap"><i class="fa-solid fa-circle"></i> Capture</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const BASE = <?= json_encode(BASE_APP) ?>;
    const userKK = '<?= $user_kode_kantor ?>';
    const isAO = <?= $is_ao ? 'true' : 'false' ?>;

    // ===================== CABANG DROPDOWN (with search) =====================
    const cabangData = [
        {k:'001',n:'Kc. Utama'},{k:'002',n:'Kc. Rembang'},{k:'003',n:'Kc. Pati'},{k:'004',n:'Kc. Demak'},
        {k:'005',n:'Kc. Kendal'},{k:'006',n:'Kc. Salatiga'},{k:'007',n:'Kc. Kab. Semarang'},
        {k:'008',n:'Kc. Wonogiri'},{k:'009',n:'Kc. Kota Surakarta'},{k:'010',n:'Kc. Karanganyar'},
        {k:'011',n:'Kc. Sukoharjo'},{k:'012',n:'Kc. Sragen'},{k:'013',n:'Kc. Boyolali'},{k:'014',n:'Kc. Magelang'},
        {k:'015',n:'Kc. Wonosobo'},{k:'016',n:'Kc. Purworejo'},{k:'017',n:'Kc. Kebumen'},
        {k:'018',n:'Kc. Banjarnegara'},{k:'019',n:'Kc. Purbalingga'},{k:'020',n:'Kc. Banyumas'},{k:'021',n:'Kc. Cilacap'},
        {k:'022',n:'Kc. Kab. Tegal'},{k:'023',n:'Kc. Brebes'},{k:'024',n:'Kc. Kota Tegal'},
        {k:'025',n:'Kc. Pemalang'},{k:'026',n:'Kc. Kota Pekalongan'},{k:'027',n:'Kc. Kab. Pekalongan'},{k:'028',n:'Kc. Batang'}
    ];
    const cabangInput = document.getElementById('inp-cabang-search');
    const cabangHidden = document.getElementById('sel-cabang');
    const cabangCombo = document.getElementById('combo-cabang');
    const cabangMenu = document.getElementById('menu-cabang');
    bindSearchSelect(cabangCombo, cabangInput, cabangMenu, cabangData, {
        getLabel: c => `${c.k} - ${c.n}`,
        onSelect: c => { cabangHidden.value = c.k; },
        onClear: value => {
            const selected = findCabang(value);
            cabangHidden.value = selected ? selected.k : '';
        }
    });
    if (userKK !== '000') {
        const current = cabangData.find(c => c.k === userKK);
        if (current) {
            cabangInput.value = `${current.k} - ${current.n}`;
            cabangHidden.value = current.k;
        }
    }
    cabangInput.addEventListener('input', function() {
        const selected = findCabang(this.value);
        cabangHidden.value = selected ? selected.k : '';
    });
    cabangInput.addEventListener('blur', function() {
        const selected = findCabang(this.value);
        if (selected) this.value = `${selected.k} - ${selected.n}`;
    });

    function findCabang(value) {
        const text = String(value || '').toLowerCase().trim();
        if (!text) return null;
        return cabangData.find(c => `${c.k} - ${c.n}`.toLowerCase() === text)
            || cabangData.find(c => c.k.toLowerCase() === text || c.n.toLowerCase() === text)
            || cabangData.find(c => `${c.k} ${c.n}`.toLowerCase().includes(text));
    }

    // ===================== WILAYAH (searchable) =====================
    const API_W = 'https://www.emsifa.com/api-wilayah-indonesia/api';
    const wilayah = { provinces: [], regencies: [], districts: [], villages: [] };
    const provInput = document.getElementById('inp-provinsi');
    const kabInput = document.getElementById('inp-kabkota');
    const kecInput = document.getElementById('inp-kecamatan');
    const desaInput = document.getElementById('inp-desa');
    const provHidden = document.getElementById('sel-provinsi');
    const kabHidden = document.getElementById('sel-kabkota');
    const kecHidden = document.getElementById('sel-kecamatan');
    const desaHidden = document.getElementById('sel-desa');
    const provId = document.getElementById('sel-provinsi-id');
    const kabId = document.getElementById('sel-kabkota-id');
    const kecId = document.getElementById('sel-kecamatan-id');
    const desaId = document.getElementById('sel-desa-id');
    const provCombo = document.getElementById('combo-provinsi');
    const kabCombo = document.getElementById('combo-kabkota');
    const kecCombo = document.getElementById('combo-kecamatan');
    const desaCombo = document.getElementById('combo-desa');
    const provMenu = document.getElementById('menu-provinsi');
    const kabMenu = document.getElementById('menu-kabkota');
    const kecMenu = document.getElementById('menu-kecamatan');
    const desaMenu = document.getElementById('menu-desa');

    const provSelect = bindSearchSelect(provCombo, provInput, provMenu, wilayah.provinces, {
        getLabel: row => row.name,
        onSelect: setProvince,
        onClear: value => { provHidden.value = value.trim(); provId.value = ''; clearWilayah(1); }
    });
    const kabSelect = bindSearchSelect(kabCombo, kabInput, kabMenu, wilayah.regencies, {
        getLabel: row => row.name,
        onSelect: setRegency,
        onClear: value => { kabHidden.value = value.trim(); kabId.value = ''; clearWilayah(2); }
    });
    const kecSelect = bindSearchSelect(kecCombo, kecInput, kecMenu, wilayah.districts, {
        getLabel: row => row.name,
        onSelect: setDistrict,
        onClear: value => { kecHidden.value = value.trim(); kecId.value = ''; clearWilayah(3); }
    });
    const desaSelect = bindSearchSelect(desaCombo, desaInput, desaMenu, wilayah.villages, {
        getLabel: row => row.name,
        onSelect: row => { desaHidden.value = row.name; desaId.value = row.id; },
        onClear: value => { desaHidden.value = value.trim(); desaId.value = ''; }
    });

    function bindSearchSelect(combo, input, menu, rowsRef, config) {
        const api = {
            rows: rowsRef,
            setRows(rows) {
                api.rows = rows;
                renderOptions(input.value);
            },
            close() {
                combo.classList.remove('open');
            },
            open() {
                if (input.disabled) return;
                renderOptions(input.value);
                combo.classList.add('open');
            }
        };

        function renderOptions(query) {
            const needle = String(query || '').toLowerCase().trim();
            const rows = api.rows
                .filter(row => config.getLabel(row).toLowerCase().includes(needle))
                .slice(0, 80);

            if (rows.length === 0) {
                menu.innerHTML = '<div class="search-empty">Tidak ada hasil</div>';
                return;
            }

            menu.innerHTML = rows.map((row, idx) => {
                const label = escapeHtml(config.getLabel(row));
                return `<button type="button" class="search-option" data-idx="${idx}">${label}</button>`;
            }).join('');

            menu.querySelectorAll('.search-option').forEach((btn, idx) => {
                btn.addEventListener('mousedown', e => e.preventDefault());
                btn.addEventListener('click', () => {
                    const row = rows[idx];
                    input.value = config.getLabel(row);
                    config.onSelect(row);
                    api.close();
                });
            });
        }

        input.addEventListener('focus', api.open);
        input.addEventListener('click', api.open);
        input.addEventListener('input', function() {
            config.onClear(this.value);
            api.open();
        });
        input.addEventListener('blur', function() {
            const selected = api.rows.find(row => config.getLabel(row).toLowerCase() === this.value.toLowerCase().trim());
            if (selected) {
                input.value = config.getLabel(selected);
                config.onSelect(selected);
            }
            setTimeout(api.close, 120);
        });

        return api;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function clearWilayah(level) {
        if (level <= 1) {
            kabInput.value = ''; kabInput.disabled = true; kabCombo.classList.add('disabled'); kabHidden.value = ''; kabId.value = ''; wilayah.regencies = []; kabSelect.setRows([]);
        }
        if (level <= 2) {
            kecInput.value = ''; kecInput.disabled = true; kecCombo.classList.add('disabled'); kecHidden.value = ''; kecId.value = ''; wilayah.districts = []; kecSelect.setRows([]);
        }
        if (level <= 3) {
            desaInput.value = ''; desaInput.disabled = true; desaCombo.classList.add('disabled'); desaHidden.value = ''; desaId.value = ''; wilayah.villages = []; desaSelect.setRows([]);
        }
    }

    fetch(API_W + '/provinces.json').then(r => r.json()).then(rows => {
        wilayah.provinces = rows;
        provSelect.setRows(rows);

        const jateng = rows.find(row => row.name.toUpperCase() === 'JAWA TENGAH');
        if (jateng) {
            provInput.value = jateng.name;
            setProvince(jateng);
        }
    }).catch(() => {});

    function setProvince(row) {
        provInput.value = row.name;
        provHidden.value = row.name;
        provId.value = row.id;
        clearWilayah(1);
        kabInput.disabled = false;
        kabCombo.classList.remove('disabled');
        kabInput.placeholder = 'Memuat kab/kota...';
        fetch(API_W + '/regencies/' + row.id + '.json').then(r => r.json()).then(rows => {
            wilayah.regencies = rows;
            kabSelect.setRows(rows);
            kabInput.placeholder = 'Ketik kab/kota';
        }).catch(() => { kabInput.placeholder = 'Gagal memuat kab/kota'; });
    }

    function setRegency(row) {
        kabInput.value = row.name;
        kabHidden.value = row.name;
        kabId.value = row.id;
        clearWilayah(2);
        kecInput.disabled = false;
        kecCombo.classList.remove('disabled');
        kecInput.placeholder = 'Memuat kecamatan...';
        fetch(API_W + '/districts/' + row.id + '.json').then(r => r.json()).then(rows => {
            wilayah.districts = rows;
            kecSelect.setRows(rows);
            kecInput.placeholder = 'Ketik kecamatan';
        }).catch(() => { kecInput.placeholder = 'Gagal memuat kecamatan'; });
    }

    function setDistrict(row) {
        kecInput.value = row.name;
        kecHidden.value = row.name;
        kecId.value = row.id;
        clearWilayah(3);
        desaInput.disabled = false;
        desaCombo.classList.remove('disabled');
        desaInput.placeholder = 'Memuat desa/kelurahan...';
        fetch(API_W + '/villages/' + row.id + '.json').then(r => r.json()).then(rows => {
            wilayah.villages = rows;
            desaSelect.setRows(rows);
            desaInput.placeholder = 'Ketik desa/kelurahan';
        }).catch(() => { desaInput.placeholder = 'Gagal memuat desa/kelurahan'; });
    }

    // ===================== GEOTAGGING =====================
    let map = null, marker = null;
    document.getElementById('btn-gps').addEventListener('click', function() {
        const btn = this;
        const el = document.getElementById('geo-alamat');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        el.value = 'Mencari lokasi...';

        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            document.getElementById('inp-lat').value = lat;
            document.getElementById('inp-lng').value = lng;

            // Show map
            const mapEl = document.getElementById('static-map');
            mapEl.style.display = 'block';
            if (!map) {
                map = L.map('static-map', {zoomControl:false, attributionControl:false}).setView([lat,lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(map);
            } else { map.setView([lat,lng], 16); }
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat,lng]).addTo(map).bindPopup('Lokasi Anda').openPopup();

            // Reverse geocode
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {headers:{'Accept-Language':'id'}})
                .then(r=>r.json()).then(data => {
                    const addr = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    el.value = addr;
                    document.getElementById('inp-geo-addr').value = addr;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                    btn.classList.add('done');
                }).catch(() => {
                    el.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
                });
        }, () => {
            el.value = 'Gagal. Aktifkan GPS.';
            btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
        }, {enableHighAccuracy:true, timeout:15000});
    });

    // ===================== FOTO =====================
    const preview = document.getElementById('foto-preview');
    const placeholder = document.getElementById('foto-placeholder');
    const fotoArea = document.getElementById('foto-area');
    const b64Input = document.getElementById('inp-foto-b64');
    const statusEl = document.getElementById('foto-status');

    window.handleFileUpload = function(inp) {
        if (!inp.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => showPreview(e.target.result, inp.files[0].name);
        reader.readAsDataURL(inp.files[0]);
    };

    function showPreview(dataUrl, label) {
        preview.src = dataUrl; preview.style.display = 'block';
        placeholder.style.display = 'none';
        fotoArea.classList.add('has-foto');
        b64Input.value = dataUrl;
        statusEl.textContent = label || 'Foto dari kamera';
        statusEl.style.display = 'block';
    }

    // Camera
    let stream = null, facing = 'environment';
    const video = document.getElementById('cam-video');
    const canvas = document.getElementById('cam-canvas');
    const modal = document.getElementById('modalCam');

    document.getElementById('btn-camera').addEventListener('click', () => new bootstrap.Modal(modal).show());
    modal.addEventListener('show.bs.modal', startCam);
    modal.addEventListener('hidden.bs.modal', stopCam);
    document.getElementById('btn-flip').addEventListener('click', () => { facing = facing==='environment'?'user':'environment'; startCam(); });
    document.getElementById('btn-snap').addEventListener('click', () => {
        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        showPreview(canvas.toDataURL('image/jpeg', 0.8), 'Foto dari kamera (' + new Date().toLocaleTimeString() + ')');
        bootstrap.Modal.getInstance(modal).hide();
    });

    function startCam() { stopCam(); navigator.mediaDevices.getUserMedia({video:{facingMode:facing}}).then(s=>{stream=s;video.srcObject=s;}).catch(()=>{}); }
    function stopCam() { if(stream){stream.getTracks().forEach(t=>t.stop());stream=null;} }

    // ===================== FORM SUBMIT =====================
    document.getElementById('form-prospek').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';

        const fd = new FormData(this);
        const payload = {};
        fd.forEach((v,k) => { if(v) payload[k] = v; });

        const selectedCabang = findCabang(cabangInput.value);
        if (!selectedCabang) {
            showToast('Pilih cabang tujuan dari daftar', 'warning');
            cabangInput.focus();
            btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Simpan Prospek';
            return;
        }

        payload.kode_kantor = selectedCabang.k;
        payload.provinsi = provHidden.value || provInput.value.trim();
        payload.kab_kota = kabHidden.value || kabInput.value.trim();
        payload.kecamatan = kecHidden.value || kecInput.value.trim();
        payload.desa = desaHidden.value || desaInput.value.trim();
        payload.prospect_type = mapProductToProspectType(payload.rekomendasi_produk || '');
        payload.is_ao_input = isAO;

        try {
            const res = await fetch(BASE + '/api/?action=prospect_create', {
                method: 'POST', credentials: 'include',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(payload)
            });
            const body = await res.json();
            if (body.status === 200 || body.status === 201) {
                showToast('<i class="fa-solid fa-check-circle me-2"></i>Prospek disimpan!', 'success');
                setTimeout(() => { window.location.href = BASE + '/daftar-prospek'; }, 800);
            } else {
                showToast(body.message || 'Gagal menyimpan', 'danger');
            }
        } catch(err) {
            showToast('Tidak bisa terhubung ke server', 'danger');
        } finally {
            btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Simpan Prospek';
        }
    });

    // Auto-delegasi badge
    document.getElementById('sel-produk').addEventListener('change', function() {
        const badge = document.getElementById('badge-auto');
        document.getElementById('inp-prospect-type').value = mapProductToProspectType(this.value);
        if (badge && isAO) badge.style.display = this.value ? 'flex' : 'none';
    });

    function mapProductToProspectType(product) {
        const map = {Kredit: 'KREDIT', Tabungan: 'TABUNGAN', Deposito: 'DEPOSITO', Aset: 'PEMBELI_ASET'};
        return map[product] || '';
    }
})();
</script>
