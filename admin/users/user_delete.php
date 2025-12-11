<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Mencegah menghapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        echo "Anda tidak dapat menghapus akun Anda sendiri.";
        exit;
    }

    try {
        // Hapus Data terkait (Optional: Jika user dihapus, aplikasinya dihapus juga)
        $conn->prepare("DELETE FROM applications WHERE user_id = ?")->execute([$id]);
        $conn->prepare("DELETE FROM scoring WHERE user_id = ?")->execute([$id]);
        
        // Hapus User
        $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->execute([$id]);
        
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        echo "Gagal menghapus user. User ini mungkin memiliki data penting lain.";
    }
} else {
    header("Location: index.php");
}
?>