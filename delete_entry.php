<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['entry_id']) || !is_numeric($_GET['entry_id'])) {
    die("Nieprawidłowy ID.");
}
$entry_id = intval($_GET['entry_id']);

$stmt = $pdo->prepare("DELETE FROM entries WHERE entry_id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);

header('Location: calendar.php');
exit;
?>
