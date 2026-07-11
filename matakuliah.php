<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$nilaiModel = new Nilai();
$pesan = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!validateCSRFToken($_POST['csrf_token'] ?? '')) die('CSRF token invalid');

    $kode = strtoupper(trim($_POST['kode'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $sks  = (int) ($_POST['sks'] ?? 0);

    if($kode && $nama && $sks >= 1 && $sks <= 6) {
        try {
            $nilaiModel->tambahMataKuliah($kode, $nama, $sks);
            $pesan = "✅ Mata kuliah berhasil ditambahkan.";
        } catch(PDOException $e) {
            $pesan = ($e->errorInfo[1] == 1062)
                ? "⚠️ Kode mata kuliah sudah terdaftar."
                : "⚠️ Terjadi kesalahan.";
        }
    } else {
        $pesan = "⚠️ Lengkapi semua kolom (SKS antara 1-6).";
    }
}

$mataKuliah = $nilaiModel->getAllMataKuliah();
?>

<div class="card">
    <h2>📚 Kelola Mata Kuliah</h2>

    <?php if($pesan): ?>
        <div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:20px;">
            <?= $pesan ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="filter-bar" style="align-items:flex-end;">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <div class="form-group" style="margin:0;">
            <label>Kode</label>
            <input type="text" name="kode" placeholder="IF101" required>
        </div>
        <div class="form-group" style="margin:0;">
            <label>Nama Mata Kuliah</label>
            <input type="text" name="nama" placeholder="Algoritma dan Pemrograman" required>
        </div>
        <div class="form-group" style="margin:0;">
            <label>SKS</label>
            <input type="number" name="sks" min="1" max="6" value="3" required>
        </div>
        <button type="submit">➕ Tambah</button>
    </form>
</div>

<div class="card">
    <h3>📋 Daftar Mata Kuliah</h3>
    <table>
        <thead><tr><th>Kode</th><th>Nama Mata Kuliah</th><th>SKS</th></tr></thead>
        <tbody>
        <?php foreach($mataKuliah as $mk): ?>
            <tr>
                <td><?= sanitizeOutput($mk['kode']) ?></td>
                <td><?= sanitizeOutput($mk['nama']) ?></td>
                <td><?= $mk['sks'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
