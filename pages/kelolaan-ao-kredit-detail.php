<?php
$no_rekening = $_GET['id'] ?? '';
$closing_date = $_GET['closing_date'] ?? date('Y-m-t', strtotime('last month'));
$harian_date = $_GET['harian_date'] ?? date('Y-m-d');
$kode_kantor = $_GET['kode_kantor'] ?? '';
$ao_employee_id = $_GET['ao_employee_id'] ?? '';
$search = $_GET['search'] ?? '';
$back_query = http_build_query([
    'closing_date' => $closing_date,
    'harian_date' => $harian_date,
    'kode_kantor' => $kode_kantor,
    'ao_employee_id' => $ao_employee_id,
    'search' => $search,
]);
?>

<style>
    .portfolio-header {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: #fff;
        padding: 26px 20px 52px;
        border-bottom-left-radius: 26px;
        border-bottom-right-radius: 26px;
    }
    .portfolio-container {
        margin: -28px 16px 90px;
        position: relative;
        z-index: 10;
    }
    .portfolio-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 14px;
        box-shadow: 0 5px 16px rgba(15,23,42,.04);
    }
    .portfolio-title {
        font-size: .88rem;
        font-weight: 900;
        color: #102A43;
        text-transform: uppercase;
        margin-bottom: 12px;
    }
    .portfolio-name {
        font-size: 1.1rem;
        font-weight: 900;
        color: #102A43;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .portfolio-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .portfolio-mini {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 10px 12px;
    }
    .portfolio-mini-label {
        font-size: .58rem;
        color: #94A3B8;
        text-transform: uppercase;
        font-weight: 800;
    }
    .portfolio-mini-value {
        font-size: .8rem;
        color: #0F172A;
        font-weight: 900;
        margin-top: 4px;
    }
    .portfolio-mini-value.money { color: #0F766E; }
    .portfolio-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #F1F5F9;
    }
    .portfolio-row:last-child { border-bottom: 0; }
    .portfolio-row-label {
        font-size: .66rem;
        font-weight: 800;
        color: #64748B;
        text-transform: uppercase;
    }
    .portfolio-row-value {
        font-size: .8rem;
        font-weight: 800;
        color: #102A43;
        text-align: right;
    }
    .portfolio-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 10px;
    }
    .portfolio-btn {
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-size: .82rem;
        font-weight: 800;
    }
    .portfolio-btn.primary { background: #0A1931; color: #fff; }
    .portfolio-btn.secondary { background: #E6EDF5; color: #0A1931; }
    .activity-item {
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
        background: #fff;
    }
    .activity-meta { font-size: .68rem; color: #64748B; }
    .activity-photo {
        width: 100%;
        max-height: 240px;
        object-fit: cover;
        border-radius: 12px;
        margin-top: 10px;
    }
    .input-custom {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        padding: 10px 12px;
        width: 100%;
        font-size: .84rem;
        font-weight: 600;
        color: #1E293B;
    }
    .input-custom[readonly] {
        background: #F8FAFC;
        color: #64748B;
    }
    .form-label-custom {
        font-size: .66rem;
        font-weight: 800;
        color: #64748B;
        margin-bottom: 5px;
        display: block;
        text-transform: uppercase;
    }
    @media (min-width:768px) {
        .portfolio-container {
            margin-left: 24px;
            margin-right: 24px;
        }
    }
</style>

<div class="portfolio-header">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/kelolaan-ao-kredit<?= $back_query !== '' ? '?' . htmlspecialchars($back_query, ENT_QUOTES, 'UTF-8') : '' ?>" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
            <h5 class="fw-bold mb-0">Detail Kelolaan AO Kredit</h5>
            <p class="small text-white-50 mb-0" style="font-size:.72rem;">Rekening: <?= htmlspecialchars($no_rekening ?: '-') ?></p>
        </div>
    </div>
</div>

<div class="portfolio-container">
    <div class="portfolio-card" id="portfolio-loading">
        <div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat detail kelolaan...</div>
    </div>

    <div id="portfolio-content" style="display:none;">
        <div class="portfolio-card">
            <div class="portfolio-name" id="d-nama">-</div>
            <div class="activity-meta mb-3" id="d-subtitle">-</div>
            <div class="portfolio-info-grid">
                <div class="portfolio-mini">
                    <div class="portfolio-mini-label">Baki Debet</div>
                    <div class="portfolio-mini-value money" id="d-bd">Rp0</div>
                </div>
                <div class="portfolio-mini">
                    <div class="portfolio-mini-label">Totung Sekarang</div>
                    <div class="portfolio-mini-value money" id="d-totung">Rp0</div>
                </div>
                <div class="portfolio-mini">
                    <div class="portfolio-mini-label">Target Pokok Awal Bulan</div>
                    <div class="portfolio-mini-value money" id="d-target-pokok">Rp0</div>
                </div>
                <div class="portfolio-mini">
                    <div class="portfolio-mini-label">Target Bunga Awal Bulan</div>
                    <div class="portfolio-mini-value money" id="d-target-bunga">Rp0</div>
                </div>
            </div>
        </div>

        <div class="portfolio-card">
            <div class="portfolio-title">Informasi Rekening</div>
            <div class="portfolio-row"><span class="portfolio-row-label">AO Kredit</span><span class="portfolio-row-value" id="d-ao">-</span></div>
            <div class="portfolio-row"><span class="portfolio-row-label">Cabang</span><span class="portfolio-row-value" id="d-cabang">-</span></div>
            <div class="portfolio-row"><span class="portfolio-row-label">Alamat</span><span class="portfolio-row-value" id="d-alamat">-</span></div>
            <div class="portfolio-row"><span class="portfolio-row-label">Bucket Awal</span><span class="portfolio-row-value" id="d-bucket-awal">-</span></div>
            <div class="portfolio-row"><span class="portfolio-row-label">Bucket Sekarang</span><span class="portfolio-row-value" id="d-bucket-sekarang">-</span></div>
            <div class="portfolio-row"><span class="portfolio-row-label">Status Bayar/JT</span><span class="portfolio-row-value" id="d-status-bayar">-</span></div>
            <div class="portfolio-row"><span class="portfolio-row-label">Pergerakan</span><span class="portfolio-row-value" id="d-pergerakan">-</span></div>
        </div>

        <div class="portfolio-card">
            <div class="portfolio-actions">
                <button class="portfolio-btn primary" data-bs-toggle="modal" data-bs-target="#modalPipeline"><i class="fa-solid fa-chart-column me-2"></i>Pipeline Awal Bulan</button>
                <button class="portfolio-btn secondary" data-bs-toggle="modal" data-bs-target="#modalActivity"><i class="fa-solid fa-phone-volume me-2"></i>Kunjungan / Call</button>
            </div>
        </div>

        <div class="portfolio-card">
            <div class="portfolio-title">Riwayat Aktivitas</div>
            <div id="activity-list"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPipeline" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Pipeline Awal Bulan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-pipeline-target">
                    <div class="mb-3">
                        <label class="form-label-custom">Periode Bulan</label>
                        <input class="input-custom" name="periode_bulan" id="target-periode" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Target Pokok</label>
                        <input class="input-custom" type="number" min="0" step="0.01" name="target_pokok_awal_bulan" id="target-pokok" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Target Bunga</label>
                        <input class="input-custom" type="number" min="0" step="0.01" name="target_bunga_awal_bulan" id="target-bunga" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Catatan</label>
                        <textarea class="input-custom" rows="2" name="catatan" id="target-catatan" placeholder="Catatan target awal bulan"></textarea>
                    </div>
                    <button type="submit" class="portfolio-btn primary w-100">Simpan Pipeline</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalActivity" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Form Kunjungan / Call</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-activity">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label-custom">Jenis</label>
                            <select class="input-custom" name="jenis_tindakan" required>
                                <option value="KUNJUNGAN">Kunjungan</option>
                                <option value="CALL">Call</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-custom">Kode</label>
                            <select class="input-custom" name="kode_tindakan" required>
                                <option value="">Pilih</option>
                                <option value="PTP">PTP</option>
                                <option value="PET">PET</option>
                                <option value="PPK">PPK</option>
                                <option value="LNS">LNS</option>
                                <option value="RKS">RKS</option>
                                <option value="SKP">SKP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label-custom">Lokasi</label>
                            <select class="input-custom" name="lokasi_tindakan">
                                <option value="">Pilih</option>
                                <option value="Rumah">Rumah</option>
                                <option value="Tempat Usaha">Tempat Usaha</option>
                                <option value="Kantor">Kantor</option>
                                <option value="Telepon">Telepon</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-custom">Orang Ditemui</label>
                            <select class="input-custom" name="orang_ditemui">
                                <option value="">Pilih</option>
                                <option value="Debitur">Debitur</option>
                                <option value="Pasangan">Pasangan</option>
                                <option value="Keluarga">Keluarga</option>
                                <option value="Tetangga">Tetangga</option>
                                <option value="Tidak Ada">Tidak Ada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label-custom">Nominal Janji</label>
                            <input class="input-custom" type="number" min="0" step="0.01" name="nominal_janji" placeholder="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label-custom">Tanggal Janji</label>
                            <input class="input-custom" type="date" name="tanggal_janji">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-custom">Keterangan</label>
                        <textarea class="input-custom" rows="3" name="keterangan" placeholder="Catatan hasil kunjungan / call" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label-custom">Alamat / Geo</label>
                        <input class="input-custom" name="geo_address" id="geo-address" placeholder="Bisa diisi manual atau ambil GPS">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-custom">Latitude</label>
                            <input class="input-custom" name="latitude" id="geo-lat" placeholder="-6.9">
                        </div>
                        <div class="col-6">
                            <label class="form-label-custom">Longitude</label>
                            <input class="input-custom" name="longitude" id="geo-lng" placeholder="110.4">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="portfolio-btn secondary flex-grow-1" onclick="captureGeoLocation()">Ambil GPS</button>
                        <label class="portfolio-btn secondary flex-grow-1 mb-0 text-center" style="cursor:pointer;">
                            Pilih Foto
                            <input type="file" accept="image/*" id="activity-photo" style="display:none;">
                        </label>
                    </div>
                    <div class="small text-muted mb-3" id="activity-photo-label">Belum ada foto dipilih</div>
                    <button type="submit" class="portfolio-btn primary w-100">Simpan Aktivitas</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const accountNumber = <?= json_encode($no_rekening) ?>;
    const detailFilters = {
        closing_date: <?= json_encode($closing_date) ?>,
        harian_date: <?= json_encode($harian_date) ?>,
        kode_kantor: <?= json_encode($kode_kantor) ?>,
        ao_employee_id: <?= json_encode($ao_employee_id) ?>,
        search: <?= json_encode($search) ?>
    };
    let portfolioData = null;
    let selectedPhoto = null;

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(value || 0));
    }

    function fmtDate(value) {
        if (!value) {
            return '-';
        }
        return new Date(value).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
    }

    function fmtDateTime(value) {
        if (!value) {
            return '-';
        }
        return new Date(value).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>\"']/g, function(ch) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#039;'}[ch];
        });
    }

    function showToastSafe(message, type) {
        if (typeof showToast === 'function') {
            showToast(message, type || 'info');
            return;
        }
        alert(message);
    }

    async function loadDetail() {
        const params = new URLSearchParams({
            action: 'ao_credit_portfolio_detail',
            no_rekening: accountNumber,
            closing_date: detailFilters.closing_date || '',
            harian_date: detailFilters.harian_date || '',
            kode_kantor: detailFilters.kode_kantor || '',
            ao_employee_id: detailFilters.ao_employee_id || ''
        });
        const res = await fetch(BASE_APP + '/api/?' + params.toString(), {credentials: 'include'});
        const body = await res.json();
        if (body.status !== 200 || !body.data?.portfolio) {
            throw new Error(body.message || 'Gagal memuat detail');
        }
        portfolioData = body.data.portfolio;
        renderDetail(body.data.portfolio, body.data.activities || [], body.data.filters || {});
    }

    function renderDetail(row, activities, filters) {
        document.getElementById('portfolio-loading').style.display = 'none';
        document.getElementById('portfolio-content').style.display = 'block';
        document.getElementById('d-nama').textContent = row.nama_nasabah || '-';
        document.getElementById('d-subtitle').textContent = (row.no_rekening || '-') + ' | ' + (row.branch_name || row.kode_kantor || '-');
        document.getElementById('d-bd').textContent = formatRupiah(row.bd_closing || 0);
        document.getElementById('d-totung').textContent = formatRupiah(row.totung_skrg || 0);
        document.getElementById('d-target-pokok').textContent = formatRupiah(row.target_pokok_awal_bulan || 0);
        document.getElementById('d-target-bunga').textContent = formatRupiah(row.target_bunga_awal_bulan || 0);
        document.getElementById('d-ao').textContent = row.nama_ao || '-';
        document.getElementById('d-cabang').textContent = (row.kode_kantor || '') + (row.branch_name ? ' - ' + row.branch_name : '');
        document.getElementById('d-alamat').textContent = row.alamat || '-';
        document.getElementById('d-bucket-awal').textContent = row.bucket_awal || '-';
        document.getElementById('d-bucket-sekarang').textContent = row.bucket_sekarang || '-';
        document.getElementById('d-status-bayar').textContent = row.status_bayar_jt || '-';
        document.getElementById('d-pergerakan').textContent = row.pergerakan_status || '-';

        document.getElementById('target-periode').value = filters.periode_bulan || '';
        document.getElementById('target-pokok').value = row.target_pokok_awal_bulan || 0;
        document.getElementById('target-bunga').value = row.target_bunga_awal_bulan || 0;
        document.getElementById('target-catatan').value = row.target_catatan || '';

        document.getElementById('activity-list').innerHTML = activities.length ? activities.map(function(item) {
            return `
                <div class="activity-item">
                    <div class="d-flex justify-content-between gap-2 mb-1">
                        <strong>${escapeHtml(item.jenis_tindakan || '-')}${item.kode_tindakan ? ' | ' + escapeHtml(item.kode_tindakan) : ''}</strong>
                        <span class="activity-meta">${fmtDateTime(item.created_at)}</span>
                    </div>
                    <div class="activity-meta mb-2">${escapeHtml(item.lokasi_tindakan || '-')} | ${escapeHtml(item.orang_ditemui || '-')}</div>
                    <div style="font-size:.82rem;color:#334155;">${escapeHtml(item.keterangan || '-')}</div>
                    ${Number(item.nominal_janji || 0) > 0 ? `<div class="activity-meta mt-2">Janji bayar ${formatRupiah(item.nominal_janji)}${item.tanggal_janji ? ' | ' + fmtDate(item.tanggal_janji) : ''}</div>` : ''}
                    ${item.foto_path ? `<img class="activity-photo" src="${BASE_APP}/api/?action=file_upload&path=${encodeURIComponent(item.foto_path)}" alt="Foto aktivitas">` : ''}
                </div>
            `;
        }).join('') : '<div class="text-muted small">Belum ada riwayat aktivitas.</div>';
    }

    document.getElementById('activity-photo').addEventListener('change', function() {
        selectedPhoto = this.files && this.files[0] ? this.files[0] : null;
        document.getElementById('activity-photo-label').textContent = selectedPhoto ? selectedPhoto.name : 'Belum ada foto dipilih';
    });

    function fileToDataUrl(file) {
        return new Promise(function(resolve, reject) {
            const reader = new FileReader();
            reader.onload = function() { resolve(String(reader.result || '')); };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    window.captureGeoLocation = function() {
        if (!navigator.geolocation) {
            showToastSafe('Browser tidak mendukung GPS', 'warning');
            return;
        }

        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('geo-lat').value = pos.coords.latitude.toFixed(8);
            document.getElementById('geo-lng').value = pos.coords.longitude.toFixed(8);
            showToastSafe('Koordinat berhasil diambil', 'success');
        }, function() {
            showToastSafe('Gagal mengambil koordinat', 'danger');
        }, {enableHighAccuracy: true, timeout: 10000});
    };

    document.getElementById('form-pipeline-target').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!portfolioData) {
            return;
        }

        const fd = new FormData(this);
        const payload = {
            no_rekening: portfolioData.no_rekening,
            kode_kantor: portfolioData.kode_kantor,
            kode_ao: portfolioData.kode_ao,
            ao_employee_id: portfolioData.ao_employee_id,
            periode_bulan: fd.get('periode_bulan'),
            closing_date: detailFilters.closing_date || '',
            harian_date: detailFilters.harian_date || '',
            target_pokok_awal_bulan: parseFloat(fd.get('target_pokok_awal_bulan') || '0'),
            target_bunga_awal_bulan: parseFloat(fd.get('target_bunga_awal_bulan') || '0'),
            catatan: fd.get('catatan') || ''
        };

        const res = await fetch(BASE_APP + '/api/?action=ao_credit_portfolio_save_pipeline', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const body = await res.json();

        if (body.status === 200) {
            showToastSafe(body.message || 'Pipeline tersimpan', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalPipeline')).hide();
            loadDetail();
        } else {
            showToastSafe(body.message || 'Gagal menyimpan pipeline', 'danger');
        }
    });

    document.getElementById('form-activity').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!portfolioData) {
            return;
        }

        const fd = new FormData(this);
        let fotoBase64 = '';
        let fotoMimeType = '';

        if (selectedPhoto) {
            fotoMimeType = selectedPhoto.type || '';
            fotoBase64 = await fileToDataUrl(selectedPhoto);
        }

        const payload = {
            no_rekening: portfolioData.no_rekening,
            kode_kantor: portfolioData.kode_kantor,
            kode_ao: portfolioData.kode_ao,
            ao_employee_id: portfolioData.ao_employee_id,
            jenis_tindakan: fd.get('jenis_tindakan'),
            kode_tindakan: fd.get('kode_tindakan'),
            lokasi_tindakan: fd.get('lokasi_tindakan'),
            orang_ditemui: fd.get('orang_ditemui'),
            nominal_janji: parseFloat(fd.get('nominal_janji') || '0'),
            tanggal_janji: fd.get('tanggal_janji'),
            keterangan: fd.get('keterangan'),
            latitude: fd.get('latitude'),
            longitude: fd.get('longitude'),
            geo_address: fd.get('geo_address'),
            foto_base64: fotoBase64,
            foto_mime_type: fotoMimeType
        };

        const res = await fetch(BASE_APP + '/api/?action=ao_credit_portfolio_save_activity', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const body = await res.json();

        if (body.status === 201) {
            showToastSafe(body.message || 'Aktivitas tersimpan', 'success');
            this.reset();
            selectedPhoto = null;
            document.getElementById('activity-photo-label').textContent = 'Belum ada foto dipilih';
            bootstrap.Modal.getInstance(document.getElementById('modalActivity')).hide();
            loadDetail();
        } else {
            showToastSafe(body.message || 'Gagal menyimpan aktivitas', 'danger');
        }
    });

    loadDetail().catch(function(err) {
        document.getElementById('portfolio-loading').innerHTML = '<div class="text-center text-danger py-4">' + escapeHtml(err.message || 'Gagal memuat detail') + '</div>';
    });
})();
</script>
