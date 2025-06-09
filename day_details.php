<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? $_GET['entry_date'] ?? null;

if (!$date) {
    echo "Brak daty.";
    exit;
}

// Pobranie wszystkich wpisów dla danego dnia i użytkownika
$stmt = $pdo->prepare("
    SELECT en.entry_id, en.entry_date AS date, s.shift_name, s.start_time, s.end_time, s.total_hours, 
           r.name AS rate_name, r.rate, r.currency, en.earned AS earned, 
           e.employer_name, e.notes
    FROM entries en
    JOIN shifts s ON en.shift_id = s.shift_id
    JOIN rate r ON en.rate_id = r.rate_id
    JOIN employers e ON en.employer_id = e.employer_id
    WHERE en.user_id = ? AND en.entry_date = ?
");
$stmt->execute([$user_id, $date]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Napiwki
$tipsStmt = $pdo->prepare("SELECT total FROM tips WHERE date = ?");
$tipsStmt->execute([$date]);
$tip = $tipsStmt->fetchColumn() ?? 0.00;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Szczegóły dnia</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container form-login">
        <h2>Szczegóły dnia: <?= htmlspecialchars($date) ?></h2>

        <?php if ($entries): ?>
            <?php foreach ($entries as $details): ?>
                <div class="entry-details">
                    <p><strong>Pracodawca:</strong> <?= htmlspecialchars($details['employer_name']) ?></p>
                    <p><strong>Zmiana:</strong> <?= $details['shift_name'] ?> (<?= $details['start_time'] ?> - <?= $details['end_time'] ?>)</p>
                    <p><strong>Stawka:</strong> <?= $details['rate'] . ' ' . $details['currency'] ?> (<?= $details['rate_name'] ?>)</p>
                    <p><strong>Łącznie zarobione:</strong> <?= number_format($details['earned'], 2) . ' ' . $details['currency'] ?></p>
                    <p><strong>Napiwki:</strong> <?= number_format($tip, 2) . ' ' . $details['currency'] ?></p>
                    <a class="button" href="edit_entry.php?entry_id=<?= urlencode($details['entry_id']) ?>">Edytuj</a>
                    <a class="button delete" href="delete_entry.php?entry_id=<?= urlencode($details['entry_id']) ?>" onclick="return confirm('Na pewno chcesz usunąć ten wpis?');">Usuń</a>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Brak danych dla tego dnia.</p>
        <?php endif; ?>

        <nav>
            <a class="button" href="calendar.php">← Powrót do kalendarza</a>
        </nav>
    </div>
</body>
</html>
