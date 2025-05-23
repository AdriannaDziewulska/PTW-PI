<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
    die("Niepoprawna data.");
}
$date = $_GET['date'];

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt_del = $pdo->prepare("DELETE FROM entries WHERE id = ? AND user_id = ?");
    $stmt_del->execute([$delete_id, $user_id]);
    header("Location: day_details.php?date=$date");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM entries WHERE user_id = ? AND entry_date = ?");
$stmt->execute([$user_id, $date]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_hours = 0;
$total_earned = 0;
foreach ($entries as $entry) {
    $total_hours += $entry['total_hours'];
    $total_earned += $entry['total_earned'];
}

?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8" />
    <title>Szczegóły dnia <?= htmlspecialchars($date) ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="day_details">
        <a href="calendar.php" class="button">&larr; Powrót do kalendarza</a>

        <h2>Szczegóły dnia <?= htmlspecialchars($date) ?></h2>

        <?php if (count($entries) === 0): ?>
            <p>Brak wpisów w tym dniu.</p>
            <p><a href="add_entry.php?date=<?= $date ?>" class="button">Dodaj nowy wpis</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Godzina rozpoczęcia</th>
                        <th>Godzina zakończenia</th>
                        <th>Stawka (zł)</th>
                        <th>Łączne godziny</th>
                        <th>Dochód (zł)</th>
                        <th>Opis</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars(substr($entry['start_time'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars(substr($entry['end_time'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars(number_format($entry['rate'], 2, ',', ' ')) ?></td>
                            <td><?= htmlspecialchars(number_format($entry['total_hours'], 2, ',', ' ')) ?></td>
                            <td><?= htmlspecialchars(number_format($entry['total_earned'], 2, ',', ' ')) ?></td>
                            <td><?= nl2br(htmlspecialchars($entry['description'])) ?></td>
                            <td>
                                <a href="edit_entry.php?id=<?= $entry['id'] ?>">Edytuj</a>
                                <a href="day_details.php?date=<?= $date ?>&delete_id=<?= $entry['id'] ?>" onclick="return confirm('Na pewno usunąć ten wpis?');" class="btn-delete">Usuń</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin: 15px 0;">
                Suma godzin: <?= number_format($total_hours, 2, ',', ' ') ?> <br>
                Suma dochodu: <?= number_format($total_earned, 2, ',', ' ') ?> zł
            </div>

            <p><a href="add_entry.php?date=<?= $date ?>" class="button">Dodaj nowy wpis</a></p>
        <?php endif; ?>
    </div>
</body>

</html>