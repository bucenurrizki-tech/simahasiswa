<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$mahasiswaModel = new Mahasiswa();
$nilaiModel     = new Nilai();

$id = (int) ($_GET['id'] ?? 0);
$m  = $mahasiswaModel->getById($id);

if(!$m) {
    echo "<div class='card'><p>Data tidak ditemukan</p></div>";
    include 'includes/footer.php';
    exit;
}

$status      = $m['ipk'] >= 2.75 ? "✅ Lulus" : "❌ Belum Lulus";
$statusColor = $m['ipk'] >= 2.75 ? "#2ecc71" : "#e74c3c";

// Ringkasan IPS per semester
$transkrip = $nilaiModel->getTranskrip($id);
?>

<div class="card">
    <h2>📋 Detail Mahasiswa</h2>

    <div style="text-align: center; margin-bottom: 20px;">
        <img src="assets/uploads/<?= $m['foto'] ?: 'default.png' ?>"
             style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #1abc9c;">
    </div>

    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="width: 150px; font-weight: bold;">NIM</td>
            <td>: <?= sanitizeOutput($m['nim']) ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Nama Lengkap</td>
            <td>: <?= sanitizeOutput($m['nama']) ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Program Studi</td>
            <td>: <?= sanitizeOutput($m['prodi']) ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">IPK</td>
            <td>: <?= number_format($m['ipk'], 2) ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status Kelulusan</td>
            <td>: <span style="color: <?= $statusColor ?>; font-weight: bold;"><?= $status ?></span></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tanggal Dibuat</td>
            <td>: <?= date('d-m-Y H:i:s', strtotime($m['created_at'])) ?></td>
        </tr>
    </table>

    <?php if(!empty($transkrip['semesters'])): ?>
        <h3 style="margin-top:25px;">📈 Ringkasan IP Semester (IPS)</h3>
        <table>
            <thead><tr><th>Semester</th><th>Total SKS</th><th>IPS</th></tr></thead>
            <tbody>
            <?php foreach($transkrip['semesters'] as $smt => $data): ?>
                <tr>
                    <td>Semester <?= $smt ?></td>
                    <td><?= $data['total_sks'] ?></td>
                    <td><strong><?= number_format($data['ips'], 2) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align: center;">
        <a href="mahasiswa.php" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">← Kembali</a>
        <a href="nilai.php?id=<?= $m['id'] ?>" style="background: #1abc9c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">📝 Input Nilai</a>
        <a href="transkrip.php?id=<?= $m['id'] ?>" style="background: #9b59b6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">🎓 Transkrip</a>
        <a href="edit.php?id=<?= $m['id'] ?>" style="background: #f39c12; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">✏️ Edit</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
