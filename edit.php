<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$mahasiswaModel = new Mahasiswa();

$id = (int) ($_GET['id'] ?? 0);
$m  = $mahasiswaModel->getById($id);

if(!$m) {
    echo "<div class='card'><p>Data tidak ditemukan</p></div>";
    include 'includes/footer.php';
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!validateCSRFToken($_POST['csrf_token'] ?? '')) die('CSRF token invalid');

    $foto = uploadFoto($_FILES['foto'] ?? null) ?? $m['foto'];

    $mahasiswaModel->update(
        $id,
        trim($_POST['nim'] ?? ''),
        trim($_POST['nama'] ?? ''),
        trim($_POST['prodi'] ?? ''),
        floatval($_POST['ipk'] ?? 0),
        $foto
    );

    header("Location: mahasiswa.php");
    exit;
}
?>

<div class="card">
    <h2>✏️ Edit Mahasiswa</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <div class="form-group">
            <label>NIM</label>
            <input type="text" name="nim" value="<?= sanitizeOutput($m['nim']) ?>" required>
        </div>

        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= sanitizeOutput($m['nama']) ?>" required>
        </div>

        <div class="form-group">
            <label>Program Studi</label>
            <input type="text" name="prodi" value="<?= sanitizeOutput($m['prodi']) ?>" required>
        </div>

        <div class="form-group">
            <label>IPK</label>
            <input type="number" step="0.01" min="0" max="4" name="ipk" value="<?= $m['ipk'] ?>" required>
            <small style="color: #666;">Akan ditimpa otomatis jika mahasiswa punya nilai mata kuliah</small>
        </div>

        <div class="form-group">
            <label>Foto Profil</label>
            <?php if($m['foto']): ?>
                <img src="assets/uploads/<?= $m['foto'] ?>" class="foto-preview" style="width: 100px; display: block; margin-bottom: 10px;">
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*" onchange="previewImage(event)">
            <img id="preview" class="foto-preview" style="display: none;">
        </div>

        <button type="submit">Update</button>
        <a href="mahasiswa.php" class="btn-secondary" style="display: inline-block; background: #95a5a6; padding: 10px 20px; text-decoration: none; color: white; border-radius: 6px;">Batal</a>
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
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include 'includes/footer.php'; ?>
