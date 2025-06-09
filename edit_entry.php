<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['entry_id']) || !is_numeric($_GET['entry_id'])) {
    die("Nieprawidłowy ID.");
}
$id = intval($_GET['entry_id']);



$stmt = $pdo->prepare("SELECT * FROM entries WHERE entry_id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    die("Nie znaleziono wpisu.");
}

// Pobierz dostępnych pracodawców
$employers = $pdo->query("SELECT employer_id, employer_name FROM employers")->fetchAll(PDO::FETCH_ASSOC);
// Pobierz dostępne zmiany
$shifts = $pdo->query("SELECT shift_id, shift_name FROM shifts")->fetchAll(PDO::FETCH_ASSOC);
// Pobierz dostępne stawki
$rates = $pdo->query("SELECT rate_id, name, rate, currency FROM rate")->fetchAll(PDO::FETCH_ASSOC);




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_date = $_POST['entry_date'];
    $employer_id = $_POST['employer_id'];
    $shift_id = $_POST['shift_id'];
    $rate_id = $_POST['rate_id'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE entries SET entry_date = ?, employer_id = ?, shift_id = ?, rate_id = ?, description = ? WHERE entry_id = ? AND user_id = ?");
    if ($stmt->execute([$entry_date, $employer_id, $shift_id, $rate_id, $description, $id, $user_id])) {
        header("Location: day_details.php?date=" . urlencode($entry_date) . "&id=" . $id);
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
    <label>Data:</label>
    <input type="date" name="entry_date" value="<?= htmlspecialchars($entry['entry_date']) ?>" required>

    <label>Pracodawca:</label>
    <select name="employer_id" required>
        <?php foreach ($employers as $employer): ?>
            <option value="<?= $employer['employer_id'] ?>" <?= $employer['employer_id'] == $entry['employer_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($employer['employer_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Zmiana:</label>
    <select name="shift_id" required>
        <?php foreach ($shifts as $shift): ?>
            <option value="<?= $shift['shift_id'] ?>" <?= $shift['shift_id'] == $entry['shift_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($shift['shift_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Stawka:</label>
    <select name="rate_id" required>
        <?php foreach ($rates as $rate): ?>
            <option value="<?= $rate['rate_id'] ?>" <?= $rate['rate_id'] == $entry['rate_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($rate['name']) ?> - <?= number_format($rate['rate'], 2) ?> <?= $rate['currency'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Zapisz zmiany</button>
</form>

    </div>
</body>

</html>