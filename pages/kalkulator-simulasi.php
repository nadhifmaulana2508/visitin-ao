<?php
// MENGHITUNG VARIABEL DASAR (Diturunkan dari file utama)
$angsuran_pokok_bln = floor($plafon / $tenor_kredit); 
$angsuran_bunga_bln = floor(($plafon * ($suku_bunga_tahun / 100)) / 12); 
$total_tunggakan = $tunggakan_pokok + $tunggakan_bunga;
?>

<style>
    /* CSS KHUSUS KALKULATOR SIMULASI */
    .card-simulasi { background: #F0FDF4; border: 1px solid #A7F3D0; }
    .title-simulasi { color: #047857; border-bottom: 2px solid #A7F3D0; padding-bottom: 10px; margin-bottom: 15px;}
    .input-simulasi { border: 1px solid #6EE7B7; background-color: white;}
    .input-simulasi:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
    .box-hasil-simulasi { background-color: white; border-radius: 10px; padding: 15px; margin-top: 15px; border: 1px dashed #34D399; box-shadow: 0 4px 6px rgba(0,0,0,0.02);}
    .target-badge { font-size: 0.65rem; padding: 4px 8px; border-radius: 6px; font-weight: 700; background: #E2E8F0; color: #475569; margin-bottom: 5px; display: inline-block;}
</style>

<div class="form-card card-simulasi" <?= $is_kredit_jatuh_tempo ? 'style="background: #FFF5F5; border-color: #FECACA;"' : '' ?>>
    <h6 class="section-title title-simulasi fw-bold" <?= $is_kredit_jatuh_tempo ? 'style="color: #DC2626; border-color: #FECACA;"' : '' ?>>
        <i class="fa-solid fa-calculator me-2"></i>Simulasi Penurunan DPD
    </h6>
    
    <?php if($is_kredit_jatuh_tempo): ?>
        <div class="alert alert-danger p-2 small fw-bold mb-3 d-flex align-items-center" style="font-size: 0.7rem;">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-2"></i> 
            Kredit telah Jatuh Tempo. Wajib Pelunasan Baki Debet agar tidak KL/Macet!
        </div>
    <?php else: ?>
        <div class="form-check form-switch mb-3 bg-white p-2 rounded-3 border" style="border-color: #A7F3D0;">
            <input class="form-check-input ms-1" type="checkbox" id="check_jatuh_tempo" onchange="hitungSimulasiLolos()" style="cursor: pointer;">
            <label class="form-check-label ms-2 small fw-bold text-dark" for="check_jatuh_tempo" style="cursor: pointer;">
                Masuk Jatuh Tempo Bulan Ini (+1 Angsuran)
            </label>
        </div>
    <?php endif; ?>

    <div class="bg-white p-2 rounded-3 border mb-3" style="border-color: <?= $is_kredit_jatuh_tempo ? '#FECACA' : '#A7F3D0' ?>;">
        <span class="d-block form-label-custom mb-1">Target Pembayaran:</span>
        <?php if($is_kredit_jatuh_tempo): ?>
            <span class="target-badge bg-danger text-white w-100 text-center" style="font-size: 0.75rem;" id="target_lancar">Wajib Lunas: Rp 0</span>
        <?php else: ?>
            <span class="target-badge" id="target_lunas">BTC: Rp 0</span>
            <span class="target-badge bg-success text-white" id="target_lancar">Aman (L): Rp 0</span>
            <span class="target-badge bg-warning text-dark" id="target_dp">Turun DP: Rp 0</span>
        <?php endif; ?>
    </div>

    <div class="input-group mb-2">
        <span class="input-group-text-custom d-flex align-items-center justify-content-center px-3 bg-white" style="border-color: <?= $is_kredit_jatuh_tempo ? '#FCA5A5' : '#6EE7B7' ?>;">Dana Nasabah</span>
        <input type="number" class="input-custom flex-grow-1 input-simulasi fw-bold text-success" id="input_rencana_bayar" placeholder="Ketik nominal (Rp)..." onkeyup="hitungSimulasiLolos()" style="border-color: <?= $is_kredit_jatuh_tempo ? '#FCA5A5' : '#6EE7B7' ?>;">
    </div>

    <div class="box-hasil-simulasi" id="box_hasil_simulasi" style="display: none; border-color: <?= $is_kredit_jatuh_tempo ? '#FCA5A5' : '#34D399' ?>;">
        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.75rem; border-bottom: 1px dashed <?= $is_kredit_jatuh_tempo ? '#FCA5A5' : '#A7F3D0' ?>; padding-bottom: 5px;">Prediksi Setelah Bayar:</h6>
        
        <div class="row g-2 mb-2">
            <div class="col-6">
                <div class="small text-muted" style="font-size: 0.6rem;">Alokasi ke Pokok:</div>
                <div class="fw-bold text-success" style="font-size: 0.8rem;" id="sim_alokasi_pokok">Rp 0</div>
            </div>
            <div class="col-6">
                <div class="small text-muted" style="font-size: 0.6rem;">Alokasi ke Bunga:</div>
                <div class="fw-bold text-primary" style="font-size: 0.8rem;" id="sim_alokasi_bunga">Rp 0</div>
            </div>
        </div>

        <div class="row g-2 mb-2 pb-2" style="border-bottom: 1px dashed <?= $is_kredit_jatuh_tempo ? '#FCA5A5' : '#A7F3D0' ?>;">
            <div class="col-6">
                <div class="small text-muted" style="font-size: 0.6rem;">Sisa Kewajiban Pokok:</div>
                <div class="fw-bold text-danger" style="font-size: 0.8rem;" id="sim_sisa_pokok">Rp 0</div>
            </div>
            <div class="col-6">
                <div class="small text-muted" style="font-size: 0.6rem;">Sisa Kewajiban Bunga:</div>
                <div class="fw-bold text-danger" style="font-size: 0.8rem;" id="sim_sisa_bunga">Rp 0</div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
            <span class="small fw-bold text-muted">Status Flow:</span>
            <span class="fw-bold fs-6" id="sim_status_kolek">-</span>
        </div>
    </div>
</div>

<script>
    // MELEMPAR VARIABEL PHP KE JAVASCRIPT
    const baseTunggakanPokok = <?= $tunggakan_pokok ?>;
    const baseTunggakanBunga = <?= $tunggakan_bunga ?>;
    const angsuranPokokBln = <?= $angsuran_pokok_bln ?>;
    const angsuranBungaBln = <?= $angsuran_bunga_bln ?>;
    const bakiDebet = <?= $baki_debet ?>;
    const isKreditMatured = <?= $is_kredit_jatuh_tempo ? 'true' : 'false' ?>;
    const kolekSekarang = "<?= $kolektibilitas ?>";

    function formatRupiah(angka) { return new Intl.NumberFormat('id-ID').format(angka); }

    function hitungSimulasiLolos() {
        const inputBayar = document.getElementById("input_rencana_bayar").value;
        const checkJatuhTempo = document.getElementById("check_jatuh_tempo");
        const isJatuhTempoBlnIni = checkJatuhTempo ? checkJatuhTempo.checked : false;
        const boxHasil = document.getElementById("box_hasil_simulasi");
        
        let totalPokok, totalBunga;

        if (isKreditMatured) {
            totalPokok = bakiDebet;
            totalBunga = baseTunggakanBunga; 
            document.getElementById("target_lancar").innerText = "Wajib Pelunasan: Rp " + formatRupiah(totalPokok + totalBunga);
        } else {
            totalPokok = baseTunggakanPokok + (isJatuhTempoBlnIni ? angsuranPokokBln : 0);
            totalBunga = baseTunggakanBunga + (isJatuhTempoBlnIni ? angsuranBungaBln : 0);
            let targetBTC = totalPokok + totalBunga;
            let targetL = totalPokok; // Pokok aman = L (walau nunggak bunga)
            let targetDP = totalPokok - angsuranPokokBln; // Turun bucket (minimal nutup 1 angsuran pokok)
            if(targetDP < 0) targetDP = totalPokok;

            document.getElementById("target_lunas").innerText = "BTC: Rp " + formatRupiah(targetBTC);
            document.getElementById("target_lancar").innerText = "Aman (L): Rp " + formatRupiah(targetL);
            document.getElementById("target_dp").innerText = "Turun DP: Rp " + formatRupiah(targetDP);
        }

        if(inputBayar === "" || inputBayar == 0) {
            boxHasil.style.display = "none"; return;
        }
        
        boxHasil.style.display = "block";
        let uangNasabah = parseFloat(inputBayar);
        
        let alokasiPokok = 0; let alokasiBunga = 0;

        if (uangNasabah >= totalPokok) {
            alokasiPokok = totalPokok; uangNasabah -= totalPokok; 
            if (uangNasabah >= totalBunga) {
                alokasiBunga = totalBunga; uangNasabah -= totalBunga;
            } else {
                alokasiBunga = uangNasabah; uangNasabah = 0;
            }
        } else {
            alokasiPokok = uangNasabah; uangNasabah = 0;
        }

        let sisaPokok = totalPokok - alokasiPokok;
        let sisaBunga = totalBunga - alokasiBunga;

        document.getElementById("sim_alokasi_pokok").innerText = "Rp " + formatRupiah(alokasiPokok);
        document.getElementById("sim_alokasi_bunga").innerText = "Rp " + formatRupiah(alokasiBunga);
        document.getElementById("sim_sisa_pokok").innerText = "Rp " + formatRupiah(sisaPokok);
        document.getElementById("sim_sisa_bunga").innerText = "Rp " + formatRupiah(sisaBunga);

        const elStatus = document.getElementById("sim_status_kolek");
        
        if (isKreditMatured) {
            if (sisaPokok <= 0 && sisaBunga <= 0) {
                elStatus.innerHTML = '<i class="fa-solid fa-check-double text-success"></i> LUNAS (Aman)';
                elStatus.className = "fw-bold fs-6 text-success";
            } else {
                elStatus.innerHTML = '<i class="fa-solid fa-skull-crossbones text-danger"></i> MACET (Tidak Lunas)';
                elStatus.className = "fw-bold fs-6 text-danger";
            }
        } else {
            if (sisaPokok <= 0 && sisaBunga <= 0) {
                elStatus.innerHTML = '<i class="fa-solid fa-check-double text-success"></i> BTC (Bersih Semua)';
                elStatus.className = "fw-bold fs-6 text-success";
            } else if (sisaPokok <= 0 && sisaBunga > 0) {
                elStatus.innerHTML = '<i class="fa-solid fa-check-circle text-primary"></i> AMAN / L (Sisa Bunga)';
                elStatus.className = "fw-bold fs-6 text-primary";
            } else if (alokasiPokok >= angsuranPokokBln) { 
                elStatus.innerHTML = '<i class="fa-solid fa-arrow-trend-down text-warning"></i> TURUN BUCKET (DP)';
                elStatus.className = "fw-bold fs-6 text-warning";
            } else {
                elStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i> TETAP ' + kolekSekarang + ' (Dana Kurang)';
                elStatus.className = "fw-bold fs-6 text-danger";
            }
        }
    }
    
    // Inisialisasi awal saat form di-load
    window.addEventListener('DOMContentLoaded', (event) => {
        hitungSimulasiLolos();
    });
</script>