<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pobierz dostępnych pracodawców
$employers = $pdo->query("SELECT employer_id, employer_name FROM employers")->fetchAll(PDO::FETCH_ASSOC);

// Pobierz dostępne zmiany
$shifts = $pdo->query("SELECT shift_id, shift_name FROM shifts")->fetchAll(PDO::FETCH_ASSOC);

// Pobierz dostępne stawki
$rates = $pdo->query("SELECT rate_id, name, rate, currency FROM rate")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Dodaj wpis</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container form-login">
        <form class="form-entry" method="post" action="save_entry.php">
            <h2>Dodaj wpis</h2>

            <?php
            $date = $_GET['date'] ?? '';
            ?>
            <label>Data:</label>
            <input type="date" name="entry_date" required value="<?= htmlspecialchars($date) ?>">


            <label>Pracodawca:</label>
            <select name="employer_id" required>
                <?php foreach ($employers as $employer): ?>
                    <option value="<?= $employer['employer_id'] ?>"><?= htmlspecialchars($employer['employer_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Zmiana:</label>
            <select name="shift_id" required>
                <?php foreach ($shifts as $shift): ?>
                    <option value="<?= $shift['shift_id'] ?>"><?= htmlspecialchars($shift['shift_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Stawka:</label>
            <select name="rate_id" required>
                <?php foreach ($rates as $rate): ?>
                    <option value="<?= $rate['rate_id'] ?>">
                        <?= htmlspecialchars($rate['name']) ?> - <?= number_format($rate['rate'], 2) ?> <?= $rate['currency'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Zapisz wpis</button>
            <a class="button" href="calendar.php">Anuluj</a>
        </form>

    </div>
</body>

</html>