<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$mahasiswaModel = new Mahasiswa();
$nilaiModel     = new Nilai();

$id = (int) ($_GET['id'] ?? 0);
$m  = $mahasiswaModel->getById($id);

if(!$m) {
    echo "<div class='card'><p>Data mahasiswa tidak ditemukan</p></div>";
    include 'includes/footer.php';
    exit;
}

$pesan = '';

// Tambah nilai
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!validateCSRFToken($_POST['csrf_token'] ?? '')) die('CSRF token invalid');

    $mataKuliahId = (int) ($_POST['mata_kuliah_id'] ?? 0);
    $semester     = (int) ($_POST['semester'] ?? 0);
    $nilaiHuruf   = $_POST['nilai_huruf'] ?? '';

    if($mataKuliahId && $semester >= 1 && $semester <= 14 && $nilaiHuruf) {
        if($nilaiModel->tambahNilai($id, $mataKuliahId, $semester, $nilaiHuruf)) {
            $pesan = "✅ Nilai berhasil disimpan. IPK otomatis diperbarui.";
        } else {
            $pesan = "⚠️ Gagal menyimpan nilai.";
        }
    } else {
        $pesan = "⚠️ Lengkapi semua kolom dengan benar.";
    }
}

// Hapus nilai
if(isset($_GET['hapus'])) {
    $nilaiModel->hapusNilai((int) $_GET['hapus']);
    $auth->redirect("nilai.php?id=$id");
}

$daftarNilai  = $nilaiModel->getNilaiByMahasiswa($id);
$mataKuliah   = $nilaiModel->getAllMataKuliah();
$ipk          = $nilaiModel->hitungIPK($id);
?>

<div class="card">
    <h2>📝 Input Nilai — <?= sanitizeOutput($m['nama']) ?> (<?= sanitizeOutput($m['nim']) ?>)</h2>

    <?php if($pesan): ?>
        <div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:20px;">
            <?= $pesan ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="filter-bar" style="align-items:flex-end;">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <div class="form-group" style="margin:0;">
            <label>Mata Kuliah</label>
            <select name="mata_kuliah_id" required>
                <option value="">Pilih Mata Kuliah</option>
                <?php foreach($mataKuliah as $mk): ?>
                    <option value="<?= $mk['id'] ?>"><?= sanitizeOutput($mk['kode'] . ' - ' . $mk['nama'] . ' (' . $mk['sks'] . ' SKS)') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin:0;">
            <label>Semester</label>
            <select name="semester" required>
                <?php for($s=1; $s<=8; $s++): ?>
                    <option value="<?= $s ?>">Semester <?= $s ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group" style="margin:0;">
            <label>Nilai</label>
            <select name="nilai_huruf" required>
                <?php foreach(Nilai::BOBOT as $huruf => $bobot): ?>
                    <option value="<?= $huruf ?>"><?= $huruf ?> (<?= number_format($bobot, 2) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">💾 Simpan Nilai</button>
    </form>

    <p style="margin-top:15px;">Belum ada mata kuliahnya? <a href="matakuliah.php">Kelola daftar mata kuliah di sini</a>.</p>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; flex-wrap:wrap;">
        <h3>📋 Daftar Nilai</h3>
        <strong>IPK Saat Ini: <?= number_format($ipk, 2) ?></strong>
    </div>
    <table>
        <thead>
            <tr><th>Semester</th><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Bobot</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if(empty($daftarNilai)): ?>
            <tr><td colspan="7" style="text-align:center;">Belum ada nilai. Tambahkan lewat form di atas.</td></tr>
        <?php endif; ?>
        <?php foreach($daftarNilai as $n): ?>
            <tr>
                <td><?= $n['semester'] ?></td>
                <td><?= sanitizeOutput($n['kode']) ?></td>
                <td><?= sanitizeOutput($n['mata_kuliah']) ?></td>
                <td><?= $n['sks'] ?></td>
                <td><?= sanitizeOutput($n['nilai_huruf']) ?></td>
                <td><?= number_format($n['bobot'], 2) ?></td>
                <td><a href="nilai.php?id=<?= $id ?>&hapus=<?= $n['id'] ?>" onclick="return confirm('Hapus nilai ini?')">🗑️</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:20px;">
        <a href="transkrip.php?id=<?= $id ?>" style="background:#1abc9c; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;">🎓 Lihat Transkrip</a>
        <a href="mahasiswa.php" style="background:#95a5a6; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;">↩️ Kembali</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
