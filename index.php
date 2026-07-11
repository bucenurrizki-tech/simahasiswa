<?php
require_once 'config/init.php';

if ($auth->isLoggedIn()) $auth->redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) die('CSRF token invalid');

    if ($auth->login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        $auth->redirect('dashboard.php');
    } else {
        $error = 'Username atau password salah';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login SIMahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background:#2c3e50; display:flex; align-items:center; justify-content:center; min-height:100vh;">
<div style="background:white; padding:30px; border-radius:12px; width:350px;">
    <h2>Login Admin</h2>
    <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
