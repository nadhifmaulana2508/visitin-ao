<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* COMPACT HEADER STYLING */
    .header-compact {
        background: linear-gradient(135deg, #1E293B, #0F172A); 
        color: white; padding: 25px 20px 45px 20px; 
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    
    .overlap-card {
        background-color: #ffffff; border-radius: 16px; margin: -25px 20px 15px 20px; 
        padding: 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05); position: relative; z-index: 10;
    }

    /* STYLING FILTER COLLAPSIBLE */
    .filter-wrapper {
        background-color: #ffffff; border-radius: 16px; margin: 0 20px 15px 20px; 
        padding: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .search-input { background-color: #F4F7F6; border: 1px solid #E0E0E0; border-radius: 10px; padding: 10px 15px; font-size: 0.85rem; width: 100%; }
    .search-input:focus { border-color: var(--color-primary); box-shadow: none; }
    .btn-toggle-filter { background-color: #E2E8F0; color: #1E293B; border: none; border-radius: 10px; padding: 0 15px; transition: 0.2s; }
    .filter-label { font-size: 0.7rem; font-weight: 700; color: #475569; margin-bottom: 5px; display: block; text-transform: uppercase; }

    /* CARD STYLING & STATUS */
    .mapping-card { border: none; border-radius: 16px; margin-bottom: 12px; background-color: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .mapping-card:active { transform: scale(0.98); }
    
    .badge-hapus-buku { background-color: #334155; color: white; }
    .badge-role { font-size: 0.7rem; padding: 6px 10px; border-radius: 6px; font-weight: 700; }
    
    .info-text { font-size: 0.75rem; color: #64748B; margin-bottom: 4px; display: flex; align-items: flex-start;}
    .info-text i { width: 16px; margin-top: 3px; color: #A0AEC0; }

    /* DUAL BUTTONS STYLING */
    .btn-action-main { background-color: #F8F9FA; color: #1E293B; border: 1px solid #E0E0E0; border-radius: 10px; font-weight: 700; transition: all 0.2s; font-size: 0.85rem; }
    .btn-action-main:hover { background-color: #1E293B; color: white; border-color: #1E293B; }
    .btn-action-history { background-color: #ffffff; color: #1E293B; border: 1px solid #E0E0E0; border-radius: 10px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
    .btn-action-history:hover { background-color: #F1F5F9; border-color: #94A3B8; color: #1E293B; }
    
    /* CUSTOM SELECT2 STYLING BIAR MATCH DENGAN TEMA */
    .select2-container .select2-selection--single {
        height: 42px !important;
        background-color: #F4F7F6 !important;
        border: 1px solid #E0E0E0 !important;
        border-radius: 10px !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
    .select2-search__field { border-radius: 6px !important; }
</style>

<?php
// SIMULASI LOGIN 
$level_user = 'pusat'; 
?>

<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/home" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <h5 class="fw-bold mb-0">Data Hapus Buku</h5>
    </div>
</div>

<div class="overlap-card d-flex justify-content-between align-items-center">
    <div>
        <p class="mb-1 small fw-bold text-muted">Total Saldo PH (HB)</p>
        <h3 class="mb-0 fw-bold text-dark">
            <span class="text-muted" style="font-size: 1.2rem;">Rp</span> 45.2<span style="font-size: 1.2rem;">M</span>
        </h3>
        <span class="badge bg-light text-secondary border mt-2">Dari 340 NOA</span>
    </div>
    <div class="bg-light p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
        <i class="fa-solid fa-file-circle-xmark fs-4" style="color: #475569;"></i>
    </div>
</div>

<div class="filter-wrapper">
    <form action="" method="GET">
        <div class="d-flex gap-2">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-light border-0" style="border-radius: 10px 0 0 10px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control bg-light border-0" placeholder="Cari nama nasabah..." style="font-size: 0.9rem; border-radius: 0 10px 10px 0;">
            </div>
            <button class="btn btn-toggle-filter" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterHB">
                <i class="fa-solid fa-sliders"></i>
            </button>
        </div>

        <div class="collapse" id="collapseFilterHB">
            <div class="row g-2 pt-3 mt-1 border-top">
                
                <?php if($level_user == 'pusat'): ?>
                <div class="col-12">
                    <label class="filter-label">Konsolidasi Cabang</label>
                    <select class="form-select select2-searchable" style="width: 100%;">
                        <option value="all" selected>Seluruh Cabang (Pusat)</option>
                        <option value="cabang_rembang">Cabang Rembang</option>
                        <option value="cabang_pati">Cabang Pati</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-12 mt-2">
                    <label class="filter-label">Kecamatan</label>
                    <select class="form-select select2-searchable" id="filter_kecamatan" style="width: 100%;">
                        <option value="" disabled selected>Ketik / Pilih Kec...</option>
                        <option value="Kaliori">Kecamatan Kaliori</option>
                        <option value="Rembang">Kecamatan Rembang</option>
                        <option value="Sumber">Kecamatan Sumber</option>
                    </select>
                </div>
                
                <div class="col-12 mt-2">
                    <label class="filter-label">Desa / Kelurahan</label>
                    <select class="form-select select2-searchable" id="filter_desa" style="width: 100%;">
                        <option value="" disabled selected>Pilih Kecamatan dulu...</option>
                        </select>
                </div>

                <div class="col-12 mt-2">
                    <label class="filter-label">Saldo PH (Rp)</label>
                    <input type="number" class="form-control search-input" placeholder="Min. Saldo PH...">
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn w-100 py-2 fw-bold text-white shadow-sm" style="background-color: #1E293B; border-radius: 10px;">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="container px-3 mt-2 mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0 text-dark">Data Hapus Buku</h6>
        <span class="text-muted small">Menampilkan <b>2</b> dari 340 NOA</span>
    </div>

    <div class="card mapping-card border-left" style="border-left: 5px solid #475569;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge badge-hapus-buku badge-role"><i class="fa-solid fa-file-circle-xmark me-1"></i> Hapus Buku</span>
            </div>
            <h6 class="fw-bold mb-1 text-dark">Bapak Supriyadi (Toko Makmur)</h6>
            
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-user-tie me-1"></i> AO: Budi Santoso
                </span>
                <span class="badge bg-light text-secondary border px-2 py-1 ms-2" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-building me-1"></i> Cab. Rembang
                </span>
            </div>

            <div class="bg-light p-2 rounded-3 mb-3">
                <span class="info-text"><i class="fa-solid fa-wallet text-dark"></i> <span class="fw-bold text-dark">Saldo PH: Rp 125.000.000</span></span>
                <span class="info-text"><i class="fa-solid fa-map-location-dot"></i> <span>Kec. Kaliori, Desa Babadan</span></span>
            </div>
            
            <div class="d-flex gap-2">
                <a href="<?= BASE_APP ?>/kunjungan-create" class="btn btn-action-main flex-grow-1 py-2">
                    <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Buat Kunjungan
                </a>
                <a href="<?= BASE_APP ?>/kunjungan-history-debitur" class="btn btn-action-history px-3 py-2" title="Lihat Riwayat">
                    <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 1. Inisialisasi Select2
        $('.select2-searchable').select2({
            width: '100%', 
            dropdownParent: $('#collapseFilterHB') 
        });

        // 2. Data Dummy Mapping Kecamatan ke Desa (Nanti diganti via API/AJAX)
        const dataWilayah = {
            "Kaliori": ["Babadan", "Banggi", "Banyudono", "Bogoharjo", "Gunungsari", "Karangsekar"],
            "Rembang": ["Gegunung", "Kabongan Kidul", "Kabongan Lor", "Karangturi", "Kutoharjo", "Leteh"],
            "Sumber": ["Bogorejo", "Grawan", "Krikilan", "Logede", "Megulung", "Pelemsari"]
        };

        // 3. Logic Dependent Dropdown: Saat Kecamatan berubah, isi Dropdown Desa
        $('#filter_kecamatan').on('change', function() {
            const selectedKec = $(this).val();
            const $desaSelect = $('#filter_desa');

            // Kosongkan desa yang lama
            $desaSelect.empty().append('<option value="" disabled selected>Ketik / Pilih Desa...</option>');

            // Masukkan desa yang baru sesuai kecamatan
            if(selectedKec && dataWilayah[selectedKec]) {
                const listDesa = dataWilayah[selectedKec];
                listDesa.forEach(function(desa) {
                    $desaSelect.append(new Option(desa, desa));
                });
            }

            // Trigger update UI Select2
            $desaSelect.trigger('change');
        });
    });
</script>