<style>
    /* FIX BUG TAMPILAN HEADER & AVATAR (Retained) */
    .header-profile {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 40px 20px 110px 20px; text-align: center;
        border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; position: relative;
    }
    
    .profile-card {
        background-color: #ffffff; border-radius: 20px; margin: -60px 20px 20px 20px; 
        padding: 0 20px 20px 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        position: relative; z-index: 10; text-align: center;
    }

    /* --- NEW AVATAR DYNAMIC UPLOAD STYLING --- */
    .avatar-dynamic {
        position: relative;
        width: 100px;
        height: 100px;
        margin: -50px auto 15px auto; /* Ditarik setengahnya ke atas */
        z-index: 10;
    }

    .label-photo-trigger {
        display: block;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        cursor: pointer;
        position: relative;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        overflow: hidden; /* Ensure crop for preview is correct */
        background-color: white;
        transition: transform 0.2s ease;
    }
    
    .label-photo-trigger:active {
        transform: scale(0.95);
    }

    .profile-img-dynamic {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Camera Icon Overlay on Hover/Active */
    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background-color: rgba(10, 25, 49, 0.7); /* semi-transparan navy */
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .label-photo-trigger:hover .photo-overlay {
        opacity: 1;
    }
    
    /* STYLING TAB CUSTOM (ICON BASED) */
    .icon-tabs {
        display: flex; background: #ffffff; border-radius: 15px; padding: 5px;
        margin: 0 20px 20px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .icon-tab-btn {
        flex: 1; text-align: center; padding: 10px 0; background: transparent; border: none; 
        border-radius: 12px; color: #94A3B8; transition: all 0.3s ease; cursor: pointer;
    }
    .icon-tab-btn.active {
        background-color: var(--color-primary); color: white;
        box-shadow: 0 4px 10px rgba(10, 25, 49, 0.2);
    }
    .icon-tab-btn i { font-size: 1.2rem; }
    .icon-tab-btn span { display: block; font-size: 0.65rem; font-weight: 700; margin-top: 3px; }
    
    /* Logic Tampilan Konten */
    .tab-content { display: none; animation: fadeIn 0.3s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* STYLING SECTION */
    .section-card {
        background-color: #ffffff; border-radius: 20px; margin: 0 20px 20px 20px; 
        padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .section-title {
        font-size: 0.85rem; font-weight: 800; color: var(--color-primary);
        text-transform: uppercase; margin-bottom: 15px; border-bottom: 2px solid #F4F7F6; padding-bottom: 10px;
    }

    /* ID Card Digital Styling */
    .id-card-digital {
        background: linear-gradient(to right, #F8F9FA, #FFFFFF); border: 1px solid #E2E8F0;
        border-left: 5px solid var(--color-primary); border-radius: 12px; padding: 15px; text-align: left;
    }
    .id-label { font-size: 0.65rem; color: #94A3B8; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;}
    .id-value { font-size: 0.9rem; font-weight: 700; color: #1E293B; display: block; margin-bottom: 12px; }
    .id-value:last-child { margin-bottom: 0; }

    /* Custom Input & Password Toggle */
    .input-custom { background-color: #F4F7F6; border: 1px solid #E0E0E0; border-radius: 12px; padding: 12px 15px; font-size: 0.9rem; width: 100%; }
    .input-custom:focus { border-color: var(--color-primary); box-shadow: none; outline: none; }
    .password-wrapper { position: relative; }
    .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94A3B8; z-index: 10; }

    /* Buttons */
    .btn-sync { background-color: #E6EDF5; color: var(--color-primary); border-radius: 12px; font-size: 0.85rem; font-weight: 700; border: none;}
    .btn-sync:hover { background-color: var(--color-primary); color: white; }
    .btn-logout { background-color: #FFF0EB; color: #D32F2F; border: 1px solid #FFE5E5; border-radius: 12px; font-weight: bold; text-decoration: none; display: block; text-align: center;}
</style>

<div class="header-profile">
    <h5 class="fw-bold mb-0">Profil Akun</h5>
</div>

<div class="profile-card">
    
    <div class="avatar-dynamic mb-3">
         <form action="<?= BASE_APP ?>/profile-update-photo" method="POST" enctype="multipart/form-data" id="form-upload-photo">
             <label for="profile_photo_input" class="label-photo-trigger" title="Ubah Foto Profil">
                 <img src="https://ui-avatars.com/api/?name=Budi+Santoso&background=FF7B54&color=fff&size=200" alt="Profile" class="profile-img-dynamic" id="profile_preview_img">
                 <div class="photo-overlay">
                     <i class="fa-solid fa-camera"></i>
                 </div>
             </label>
             <input type="file" name="profile_photo" id="profile_photo_input" accept="image/*" class="d-none">
         </form>
    </div>

    <h4 class="fw-bold text-dark mb-1">Budi Santoso</h4>
    <span class="badge bg-primary px-3 py-2 rounded-pill mb-2" style="background-color: var(--color-primary)!important;">AO Remedial</span> 
</div>

<div class="icon-tabs">
    <button class="icon-tab-btn active" onclick="switchProfileTab('info', this)">
        <i class="fa-solid fa-id-card-clip"></i>
        <span>Informasi</span>
    </button>
    <button class="icon-tab-btn" onclick="switchProfileTab('setting', this)">
        <i class="fa-solid fa-gear"></i>
        <span>Pengaturan</span>
    </button>
</div>

<div id="tab-info" class="tab-content active pb-5">
    <div class="section-card">
        <h6 class="section-title"><i class="fa-solid fa-id-card me-2 text-accent"></i>ID Card Pegawai</h6>
        
        <div class="id-card-digital shadow-sm">
            <div class="row">
                <div class="col-6">
                    <span class="id-label">ID Pegawai (NIK)</span>
                    <span class="id-value">19920101001</span>
                </div>
                <div class="col-6 text-end">
                    <i class="fa-solid fa-building-user text-muted opacity-25 fs-1"></i>
                </div>
                <div class="col-12">
                    <span class="id-label">Unit Kerja</span>
                    <span class="id-value">Divisi Penyelamatan Kredit</span>
                    
                    <span class="id-label">Jabatan</span>
                    <span class="id-value">Account Officer (AO) Remedial</span>
                    
                    <span class="id-label">Kantor Penempatan</span>
                    <span class="id-value mb-0">Cabang Utama</span>
                </div>
            </div>
        </div>
        
        <div class="mt-3 px-2 d-flex align-items-center">
            <i class="fa-solid fa-phone text-muted me-3"></i>
            <div>
                <span class="id-label mb-0">Nomor HP Terdaftar</span>
                <span class="fw-bold text-dark" style="font-size: 0.9rem;">0812-3456-7890</span>
            </div>
        </div>
    </div>
</div>

<div id="tab-setting" class="tab-content pb-5">
    
    <div class="section-card">
        <h6 class="section-title"><i class="fa-solid fa-cloud-arrow-down me-2 text-accent"></i>Sinkronisasi SIMPEG</h6>
        <p class="small text-muted mb-3">Tarik data terbaru (Jabatan, Unit Kerja, dsb) dari sistem kepegawaian pusat.</p>
        <button class="btn btn-sync w-100 py-2 shadow-sm">
            <i class="fa-solid fa-arrows-rotate me-2"></i> Tarik Data Sekarang
        </button>
        <div class="text-center mt-2">
            <small class="text-muted" style="font-size: 0.65rem;">Terakhir sinkron: Hari ini, 08:00 WIB</small>
        </div>
    </div>

    <div class="section-card">
        <h6 class="section-title"><i class="fa-solid fa-lock me-2 text-accent"></i>Ganti Password</h6>
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.75rem; font-weight: 700; color: #64748B;">Password Saat Ini</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control input-custom" id="pass_old" placeholder="Masukkan password saat ini">
                    <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePassword('pass_old', this)"></i>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.75rem; font-weight: 700; color: #64748B;">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control input-custom" id="pass_new" placeholder="Minimal 6 karakter">
                    <i class="fa-regular fa-eye-slash toggle-password" onclick="togglePassword('pass_new', this)"></i>
                </div>
            </div>

            <button type="button" class="btn w-100 py-2 fw-bold text-white shadow-sm mt-2" style="background-color: var(--color-primary); border-radius: 12px; border:none;">
                Simpan Password Baru
            </button>
        </form>
    </div>

    <div class="px-4 mb-3">
        <a href="<?= BASE_APP ?>/login" class="btn btn-logout w-100 py-3 shadow-sm">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Keluar / Logout
        </a>
    </div>
</div>

<script>
// Script untuk Dynamic Profile Photo Preview & Auto Submit Form
document.getElementById('profile_photo_input').onchange = function (evt) {
    const [file] = profile_photo_input.files
    if (file) {
        // Preview foto langsung ganti
        profile_preview_img.src = URL.createObjectURL(file)
        
        // Nanti kalau di BE sudah jadi, form otomatis disubmit di sini:
        // document.getElementById('form-upload-photo').submit();
        
        // Untuk FE test, kita cuma preview saja.
    }
}

// Script untuk Tab Profile
function switchProfileTab(tabId, btnElement) {
    const buttons = document.querySelectorAll('.icon-tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    btnElement.classList.add('active');
    
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
}

// Script untuk Icon Mata Password
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        iconElement.classList.remove("fa-eye-slash");
        iconElement.classList.add("fa-eye");
        iconElement.style.color = "var(--color-primary)"; 
    } else {
        input.type = "password";
        iconElement.classList.remove("fa-eye");
        iconElement.classList.add("fa-eye-slash");
        iconElement.style.color = "#94A3B8"; 
    }
}
</script>