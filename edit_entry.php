<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Nieprawidłowy ID.");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM entries WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    die("Nie znaleziono wpisu.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $rate = floatval($_POST['rate']);
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE entries SET start_time = ?, end_time = ?, rate = ?, description = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$start_time, $end_time, $rate, $description, $id, $user_id])) {
        header("Location: day_details.php?date=" . $entry['entry_date']);
        exit;
    } else {
        echo "Błąd aktualizacji.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Edytuj wpis</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="day_details">
        
        <a href="calendar.php" class="button">← Powrót do kalendarza</a>
        <h2>Edytuj wpis z dnia <?= htmlspecialchars($entry['entry_date']) ?></h2>

        <form method="POST">
            <label>Godzina rozpoczęcia:</label>
            <input type="time" name="start_time" value="<?= htmlspecialchars($entry['start_time']) ?>" required>

            <label>Godzina zakończenia:</label>
            <input type="time" name="end_time" value="<?= htmlspecialchars($entry['end_time']) ?>" required>

            <label>Stawka (zł/h):</label>
            <input type="number" step="0.01" name="rate" value="<?= htmlspecialchars($entry['rate']) ?>" required>

            <label>Opis:</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($entry['description']) ?></textarea>

            <button type="submit">Zapisz zmiany</button>
        </form>
    </div>
</body>

</html>