<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['shift_name'] ?? '';
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';

    if (!empty($name) && !empty($start) && !empty($end)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO shifts (shift_name, start_time, end_time) VALUES (?, ?, ?)");
            $stmt->execute([$name, $start, $end]);
            header("Location: settings.php?success=1");
            exit;
        } catch (PDOException $e) {
            echo "Błąd zapisu: " . $e->getMessage();
        }
    } else {
        echo "Wszystkie pola muszą być poprawnie wypełnione.";
    }
}
?>
