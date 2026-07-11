<?php
require_once 'config/init.php';
$auth->requireLogin();

if(!isset($_GET['id'])) $auth->redirect('mahasiswa.php');

$mahasiswaModel = new Mahasiswa();
$mahasiswaModel->delete((int) $_GET['id']);

$auth->redirect('mahasiswa.php');
