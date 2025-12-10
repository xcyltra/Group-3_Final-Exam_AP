<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php"); exit;
}

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM applications WHERE application_id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: application.php?msg=deleted");
exit;
?>