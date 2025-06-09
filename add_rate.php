<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $rate = $_POST['rate'] ?? '';
    $currency = $_POST['currency'] ?? '';

    if (!empty($name) && is_numeric($rate) && !empty($currency)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO rate (name, rate, currency) VALUES (?, ?, ?)");
            $stmt->execute([$name, $rate, $currency]);
            header("Location: calendar.php");
            exit;
        } catch (PDOException $e) {
            echo "Błąd zapisu: " . $e->getMessage();
        }
    } else {
        echo "Wszystkie pola muszą być poprawnie wypełnione.";
    }
}

?>