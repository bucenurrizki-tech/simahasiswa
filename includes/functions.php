<?php
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function uploadFoto($file) {
    // Jika tidak ada file yang diupload
    if(!$file || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    // Jika ada error upload
    if($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validasi tipe file
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, $allowed)) {
        return null;
    }
    
    // Validasi ukuran file (max 2MB)
    if($file['size'] > 2 * 1024 * 1024) {
        return null;
    }
    
    // Buat folder uploads jika belum ada
    $targetDir = __DIR__ . "/../assets/uploads/";
    if(!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Generate nama file unik
    $newName = uniqid() . "." . $ext;
    $dest = $targetDir . $newName;
    
    // Pindahkan file
    if(move_uploaded_file($file['tmp_name'], $dest)) {
        return $newName;
    }
    
    return null;
}
?>