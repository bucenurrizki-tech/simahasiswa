<?php
require_once 'config/init.php';
$auth->requireLogin();

$mahasiswaModel = new Mahasiswa();
$rows = $mahasiswaModel->getForExport(
    $_GET['search'] ?? '',
    $_GET['prodi'] ?? '',
    $_GET['ipk'] ?? ''
);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=mahasiswa.csv');
$output = fopen('php://output', 'w');
fputcsv($output, ['NIM','Nama','Prodi','IPK']);
foreach($rows as $row) fputcsv($output, $row);
fclose($output);
exit;
