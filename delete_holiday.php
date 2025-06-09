<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['holiday_id']) || !is_numeric($_GET['holiday_id'])) {
    header('Location: settings.php?error=Nieprawidłowe ID święta');
    exit;
}

$holiday_id = intval($_GET['holiday_id']);

$stmt = $pdo->prepare("DELETE FROM holidays WHERE holiday_id = ?");
$stmt->execute([$holiday_id]);

header('Location: settings.php?success=Święto usunięte');
exit;
