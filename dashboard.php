<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$mahasiswaModel = new Mahasiswa();
$stats = $mahasiswaModel->getStats();
?>
<div class="card">
    <h2>📊 Statistik Akademik</h2>
    <div class="stats">
        <div class="stat-card">🏫 Total Mahasiswa<br><h3><?= $stats['total'] ?></h3></div>
        <div class="stat-card">⭐ Rata-rata IPK<br><h3><?= number_format($stats['rataIPK'], 2) ?></h3></div>
    </div>
</div>

<div class="card">
    <h3>📈 Grafik Per Prodi</h3>
    <canvas id="prodiChart" width="400" height="200"></canvas>
</div>
<div class="card">
    <h3>🎓 Status Kelulusan (IPK ≥ 2.75)</h3>
    <canvas id="kelulusanChart" width="400" height="200"></canvas>
</div>

<script>
const prodiCtx = document.getElementById('prodiChart').getContext('2d');
new Chart(prodiCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($stats['prodiChart'])) ?>,
        datasets: [{ label: 'Jumlah Mahasiswa', data: <?= json_encode(array_values($stats['prodiChart'])) ?>, backgroundColor: '#3498db' }]
    }
});
const kelCtx = document.getElementById('kelulusanChart').getContext('2d');
new Chart(kelCtx, {
    type: 'pie',
    data: {
        labels: ['Lulus', 'Tidak Lulus'],
        datasets: [{ data: [<?= $stats['lulus'] ?>, <?= $stats['tidakLulus'] ?>], backgroundColor: ['#2ecc71', '#e74c3c'] }]
    }
});
</script>
<?php include 'includes/footer.php'; ?>
