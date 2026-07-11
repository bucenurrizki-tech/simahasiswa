<?php
require_once 'config/init.php';
$auth->requireLogin();
include 'includes/header.php';

$mahasiswaModel = new Mahasiswa();

$search      = $_GET['search'] ?? '';
$prodiFilter = $_GET['prodi'] ?? '';
$ipkFilter   = $_GET['ipk'] ?? '';
$page        = max(1, (int) ($_GET['page'] ?? 1));

$result     = $mahasiswaModel->getAll($search, $prodiFilter, $ipkFilter, $page);
$mahasiswa  = $result['data'];
$totalPages = $result['totalPages'];
$listProdi  = $mahasiswaModel->getListProdi();
?>
<div class="card">
    <div style="display:flex; justify-content:space-between; flex-wrap:wrap;">
        <h2>📋 Data Mahasiswa</h2>
        <a href="tambah.php" style="background:#1abc9c; padding:8px 16px; text-decoration:none; color:white; border-radius:6px;">+ Tambah Mahasiswa</a>
    </div>
    <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Cari nama/NIM" value="<?= sanitizeOutput($search) ?>">
        <select name="prodi">
            <option value="">Semua Prodi</option>
            <?php foreach($listProdi as $p): ?>
                <option value="<?= sanitizeOutput($p) ?>" <?= $prodiFilter==$p?'selected':'' ?>><?= sanitizeOutput($p) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="ipk">
            <option value="">Semua IPK</option>
            <option value=">=3" <?= $ipkFilter=='>=3'?'selected':'' ?>>IPK ≥ 3.00</option>
            <option value="2.5-3" <?= $ipkFilter=='2.5-3'?'selected':'' ?>>2.50 - 2.99</option>
            <option value="<2.5" <?= $ipkFilter=='<2.5'?'selected':'' ?>>IPK < 2.50</option>
        </select>
        <button type="submit">Filter</button>
        <a href="export.php?<?= http_build_query($_GET) ?>" class="btn-secondary" style="display:inline-block; background:#3498db; padding:8px 16px; text-decoration:none; color:white; border-radius:6px;">📎 Ekspor CSV</a>
    </form>

    <table>
        <thead>
            <tr><th>Foto</th><th>NIM</th><th>Nama</th><th>Prodi</th><th>IPK</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach($mahasiswa as $m): ?>
            <tr>
                <td><img src="assets/uploads/<?= $m['foto'] ?: 'default.png' ?>" width="50" height="50" style="object-fit:cover; border-radius:50%;"></td>
                <td><?= sanitizeOutput($m['nim']) ?></td>
                <td><?= sanitizeOutput($m['nama']) ?></td>
                <td><?= sanitizeOutput($m['prodi']) ?></td>
                <td><?= $m['ipk'] ?></td>
                <td>
                    <a href="detail.php?id=<?= $m['id'] ?>" title="Detail">👁️</a>
                    <a href="nilai.php?id=<?= $m['id'] ?>" title="Input Nilai">📝</a>
                    <a href="transkrip.php?id=<?= $m['id'] ?>" title="Transkrip">🎓</a>
                    <a href="edit.php?id=<?= $m['id'] ?>" title="Edit">✏️</a>
                    <a href="hapus.php?id=<?= $m['id'] ?>" onclick="return confirm('Yakin hapus?')" title="Hapus">🗑️</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&prodi=<?= urlencode($prodiFilter) ?>&ipk=<?= urlencode($ipkFilter) ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
