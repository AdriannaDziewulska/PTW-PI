<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Dodawanie napiwku
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $total = $_POST['total'];

    $check = $pdo->prepare("SELECT tip_id FROM tips WHERE user_id = ? AND date = ?");
    $check->execute([$user_id, $date]);
    if ($check->fetch()) {
        $error = "Napiwek dla tej daty już istnieje.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tips (user_id, date, total) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $date, $total]);
        $success = "Napiwek dodany!";
    }
}

// Wyświetlanie napiwków tylko zalogowanego użytkownika
$stmt = $pdo->prepare("SELECT date, total FROM tips WHERE user_id = ? ORDER BY date DESC");
$stmt->execute([$user_id]);
$tips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Napiwki</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container form-login">
        <h2>Napiwki</h2>

        <h3>Dodaj napiwek</h3>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!empty($success)): ?>
            <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Data:</label>
            <input type="date" name="date" required>
            <label>Kwota napiwku:</label>
            <input type="number" name="total" step="0.01" required>
            <button type="submit" class="button">Dodaj napiwek</button>
        </form>

        <h3 style="margin-top:40px">Lista napiwków</h3>
        <table style="width:100%">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Kwota</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tips as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['date']) ?></td>
                        <td><?= number_format($t['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <a class="button" href="calendar.php">← Powrót do kalendarza</a>
    </div>
</body>
</html>
