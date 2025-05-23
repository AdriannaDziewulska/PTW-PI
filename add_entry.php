<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$default_date = $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $entry_date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $rate = floatval($_POST['rate']);

    if (strtotime($end_time) <= strtotime($start_time)) {
        $message = "Godzina zakończenia musi być późniejsza niż rozpoczęcia.";
    } else {
        $hours = (strtotime($end_time) - strtotime($start_time)) / 3600;
        $total_earned = $hours * $rate;

        $stmt = $pdo->prepare("INSERT INTO entries (user_id, entry_date, start_time, end_time, rate, total_hours, total_earned) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $entry_date, $start_time, $end_time, $rate, $hours, $total_earned])) {
            header("Location: calendar.php");
            exit;
        } else {
            $message = "Błąd dodawania wpisu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Dodaj wpis</title>
    <link rel="stylesheet" href="css/styles.css">

</head>

<body>
    <div class="day_details">
        
        <a href="calendar.php" class="button">← Powrót do kalendarza</a>
        <h2>Dodaj wpis do pracomierza</h2>
        <form method="POST">
            <label>Data:</label>
            <input type="date" name="date" value="<?= htmlspecialchars($default_date) ?>" required>

            <label>Godzina rozpoczęcia:</label>
            <input type="time" name="start_time" required>

            <label>Godzina zakończenia:</label>
            <input type="time" name="end_time" required>

            <label>Stawka (zł/h):</label>
            <input type="number" step="0.01" name="rate" required>

            <button type="submit">Zapisz wpis</button>
        </form>

        <?php if ($message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

    </div>
</body>

</html>