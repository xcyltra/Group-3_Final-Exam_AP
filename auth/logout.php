<?php
session_start();

// 1. Kosongkan semua variabel session
$_SESSION = [];

// 2. Hapus cookie session (Langkah keamanan ekstra)
// Ini memastikan browser melupakan ID session lama
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session di server
session_destroy();

// 4. Redirect ke halaman Login dengan pesan notifikasi
echo "<script>
        alert('Anda berhasil keluar dari sistem.');
        window.location = '../index.php';
      </script>";
exit;
?>