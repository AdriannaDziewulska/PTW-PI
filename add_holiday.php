<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Sprawdź, czy dane zostały przesłane metodą POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? null;
    $name = $_POST['name'] ?? null;
    $color = $_POST['holidays_background_color'] ?? '#880000';

    if ($date && $name && $color) {
        // Sprawdź, czy święto już istnieje dla tej daty
        $stmt = $pdo->prepare("SELECT holiday_id FROM holidays WHERE date = ?");
        $stmt->execute([$date]);
        if ($stmt->fetch()) {
            // Święto już istnieje
            header('Location: settings.php?error=Święto dla tej daty już istnieje');
            exit;
        }

        // Dodaj nowe święto
        $stmt = $pdo->prepare("INSERT INTO holidays (date, name, holidays_background_color) VALUES (?, ?, ?)");
        $stmt->execute([$date, $name, $color]);
        header('Location: settings.php?success=Święto dodane');
        exit;
    } else {
        header('Location: settings.php?error=Brak wymaganych danych');
        exit;
    }
} else {
    header('Location: settings.php');
    exit;
}
