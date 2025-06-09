<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$entry_date = $_POST['entry_date'];
$employer_id = $_POST['employer_id'];
$shift_id = $_POST['shift_id'];
$rate_id = $_POST['rate_id'];

$shiftStmt = $pdo->prepare("SELECT start_time, end_time, total_hours FROM shifts WHERE shift_id = ?");
$shiftStmt->execute([$shift_id]);
$shift = $shiftStmt->fetch();

$rateStmt = $pdo->prepare("SELECT rate FROM rate WHERE rate_id = ?");
$rateStmt->execute([$rate_id]);
$rate = $rateStmt->fetchColumn();

$earned = $shift['total_hours'] * $rate;

try {
    $pdo->beginTransaction();

    $stmt1 = $pdo->prepare("INSERT INTO entries (employer_id, user_id, entry_date, shift_id, rate_id, earned	
) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt1->execute([$employer_id, $user_id, $entry_date, $shift_id, $rate_id, $earned]);

    
    $pdo->commit();
    header("Location: calendar.php?success=1");
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Błąd zapisu: " . $e->getMessage();
}
header("Location: calendar.php");
exit;

