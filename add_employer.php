<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['employer_name'] ?? '';
    $color = $_POST['employer_background_color'] ?? '#000000';
    $notes = $_POST['notes'] ?? '';

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO employers (employer_name, employer_background_color, notes) VALUES (?, ?, ?)");
            $stmt->execute([$name, $color, $notes]);
            header("Location: calendar.php");
            exit;
        } catch (PDOException $e) {
            echo "Błąd zapisu: " . $e->getMessage();
        }
    } else {
        echo "Nazwa pracodawcy nie może być pusta.";
    }
}
header("Location: calendar.php");
exit;

?>