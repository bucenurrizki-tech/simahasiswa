<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$mahasiswaModel = new Mahasiswa();
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!validateCSRFToken($_POST['csrf_token'] ?? '')) die('CSRF token invalid');

    $nim   = trim($_POST['nim'] ?? '');
    $nama  = trim($_POST['nama'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $ipk   = floatval($_POST['ipk'] ?? 0);

    if(empty($nim)) {
        $error = "NIM tidak boleh kosong!";
    } elseif(empty($nama)) {
        $error = "Nama tidak boleh kosong!";
    } elseif(empty($prodi)) {
        $error = "Program Studi tidak boleh kosong!";
    } elseif($ipk < 0 || $ipk > 4) {
        $error = "IPK harus antara 0 - 4!";
    } else {
        $foto = uploadFoto($_FILES['foto'] ?? null);
        try {
            $mahasiswaModel->create($nim, $nama, $prodi, $ipk, $foto);
            header("Location: mahasiswa.php?success=1");
            exit;
        } catch(PDOException $e) {
            if($e->errorInfo[1] == 1062) {
                $error = "NIM sudah terdaftar! Gunakan NIM yang berbeda.";
            } else {
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="card">
    <h2>➕ Tambah Mahasiswa Baru</h2>

    <?php if($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>" placeholder="Contoh: 220101001" required>
            <small style="color: #666;">Contoh: 220101001</small>
        </div>

        <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" placeholder="Contoh: Budi Santoso" required>
        </div>

        <div class="form-group">
            <label>Program Studi *</label>
            <select name="prodi" required>
                <option value="">Pilih Prodi</option>
                <option value="Teknik Informatika" <?= (($_POST['prodi'] ?? '') == 'Teknik Informatika') ? 'selected' : '' ?>>Teknik Informatika</option>
                <option value="Sistem Informasi" <?= (($_POST['prodi'] ?? '') == 'Sistem Informasi') ? 'selected' : '' ?>>Sistem Informasi</option>
                <option value="Teknik Elektro" <?= (($_POST['prodi'] ?? '') == 'Teknik Elektro') ? 'selected' : '' ?>>Teknik Elektro</option>
                <option value="Teknik Mesin" <?= (($_POST['prodi'] ?? '') == 'Teknik Mesin') ? 'selected' : '' ?>>Teknik Mesin</option>
                <option value="Teknik Sipil" <?= (($_POST['prodi'] ?? '') == 'Teknik Sipil') ? 'selected' : '' ?>>Teknik Sipil</option>
            </select>
        </div>

        <div class="form-group">
            <label>IPK Awal</label>
            <input type="number" step="0.01" min="0" max="4" name="ipk" value="<?= htmlspecialchars($_POST['ipk'] ?? '0') ?>" placeholder="Contoh: 3.50">
            <small style="color: #666;">Boleh dikosongkan (0.00) — IPK akan dihitung otomatis dari nilai mata kuliah</small>
        </div>

        <div class="form-group">
            <label>Foto Profil</label>
            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif" onchange="previewImage(event)">
            <img id="preview" class="foto-preview" style="display: none;">
            <small style="color: #666; display: block;">Format: JPG, PNG, GIF (Max 2MB)</small>
        </div>

        <button type="submit">💾 Simpan</button>
        <a href="mahasiswa.php" style="background: #95a5a6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;">↩️ Batal</a>
    </form>
</div>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block';
        output.style.width = '100px';
        output.style.height = '100px';
        output.style.objectFit = 'cover';
        output.style.borderRadius = '50%';
        output.style.marginTop = '10px';
        output.style.border = '1px solid #ddd';
    };
    if(event.target.files && event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
