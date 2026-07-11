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

$transkrip = $nilaiModel->getTranskrip($id);
?>

<div class="card" id="transkrip">
    <div style="text-align:center; border-bottom:2px solid #2c3e50; padding-bottom:15px; margin-bottom:20px;">
        <h2>🎓 TRANSKRIP NILAI AKADEMIK</h2>
        <p>Sistem Informasi Mahasiswa</p>
    </div>

    <table style="width:100%; margin-bottom:25px;">
        <tr><td style="width:150px; font-weight:bold;">NIM</td><td>: <?= sanitizeOutput($m['nim']) ?></td></tr>
        <tr><td style="font-weight:bold;">Nama</td><td>: <?= sanitizeOutput($m['nama']) ?></td></tr>
        <tr><td style="font-weight:bold;">Program Studi</td><td>: <?= sanitizeOutput($m['prodi']) ?></td></tr>
    </table>

    <?php if(empty($transkrip['semesters'])): ?>
        <p style="text-align:center; color:#888;">Belum ada nilai yang diinput. <a href="nilai.php?id=<?= $id ?>">Input nilai sekarang</a>.</p>
    <?php endif; ?>

    <?php foreach($transkrip['semesters'] as $smt => $data): ?>
        <h3 style="background:#2c3e50; color:white; padding:8px 12px; border-radius:6px;">Semester <?= $smt ?></h3>
        <table style="margin-bottom:10px;">
            <thead>
                <tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Bobot</th><th>Mutu (Bobot × SKS)</th></tr>
            </thead>
            <tbody>
            <?php foreach($data['matkul'] as $n): ?>
                <tr>
                    <td><?= sanitizeOutput($n['kode']) ?></td>
                    <td><?= sanitizeOutput($n['mata_kuliah']) ?></td>
                    <td><?= $n['sks'] ?></td>
                    <td><?= sanitizeOutput($n['nilai_huruf']) ?></td>
                    <td><?= number_format($n['bobot'], 2) ?></td>
                    <td><?= number_format($n['bobot'] * $n['sks'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p style="text-align:right; font-weight:bold;">
            Total SKS Semester: <?= $data['total_sks'] ?> &nbsp;|&nbsp;
            IPS (Indeks Prestasi Semester): <span style="color:#1abc9c;"><?= number_format($data['ips'], 2) ?></span>
        </p>
    <?php endforeach; ?>

    <?php if(!empty($transkrip['semesters'])): ?>
        <div style="background:#ecf0f1; padding:15px; border-radius:8px; margin-top:20px; text-align:right;">
            <h3 style="margin:0;">
                Total SKS: <?= $transkrip['total_sks'] ?> &nbsp;|&nbsp;
                IPK (Indeks Prestasi Kumulatif): <span style="color:#e67e22;"><?= number_format($transkrip['ipk'], 2) ?></span>
            </h3>
        </div>
    <?php endif; ?>

    <div style="margin-top:30px; text-align:center;" class="no-print">
        <button onclick="window.print()" style="background:#3498db;">🖨️ Cetak Transkrip</button>
        <a href="nilai.php?id=<?= $id ?>" style="background:#f39c12; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;">📝 Input Nilai</a>
        <a href="mahasiswa.php" style="background:#95a5a6; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;">↩️ Kembali</a>
    </div>
</div>

<style>
@media print {
    .header, .no-print { display: none !important; }
    .card { box-shadow: none !important; }
}
</style>

<?php include 'includes/footer.php'; ?>
