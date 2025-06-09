<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Ustawienia</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        form {
            margin-bottom: 30px;
        }

        input,
        select,
        textarea {
            margin: 4px;
            padding: 6px;
        }

        .holiday-color {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid #444;
        }

        .btn-delete {
            background: #c00;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #a00;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>⚙️ Ustawienia</h1>
        <nav>
            <a href="calendar.php" class="button">← Powrót do kalendarza</a>
        </nav>

        <div class="container form-login">
        <h2>Pracodawcy</h2>
        <form action="add_employer.php" method="POST">
            <input type="text" name="employer_name" placeholder="Nazwa pracodawcy" required>
            <input type="color" name="employer_background_color" value="#222222" required>
            <textarea name="notes" placeholder="Notatki (opcjonalne)"></textarea>
            <button type="submit">Dodaj pracodawcę</button>
        </form>
        </div>

        <div class="container form-login">
            <h2>Święta</h2>
            <form action="add_holiday.php" method="POST">
                <input type="date" name="date" required>
                <input type="text" name="name" placeholder="Nazwa święta" required>
                <input type="color" name="holidays_background_color" value="#880000" required>
                <button type="submit">Dodaj święto</button>
            </form>

            <?php
            // Pobierz wszystkie święta do listy
            $stmt = $pdo->query("SELECT holiday_id, date, name, holidays_background_color FROM holidays ORDER BY date ASC");
            $holidays_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <h3>Lista świąt</h3>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Nazwa</th>
                        <th>Kolor</th>
                        <th>Usuń</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays_list as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['date']) ?></td>
                            <td><?= htmlspecialchars($h['name']) ?></td>
                            <td>
                                <span class="holiday-color" style="background:<?= htmlspecialchars($h['holidays_background_color']) ?>"></span>
                            </td>
                            <td>
                                <a href="delete_holiday.php?holiday_id=<?= $h['holiday_id'] ?>" class="btn-delete" onclick="return confirm('Na pewno usunąć to święto?');">Usuń</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="container form-login">
        <h2>Stawki</h2>
        <form action="add_rate.php" method="POST">
            <input type="text" name="name" placeholder="Nazwa (np. Nocna)" required>
            <input type="number" name="rate" step="0.01" placeholder="Stawka" required>
            <select name="currency" required>
                <option value="PLN">PLN</option>
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
            </select>
            <button type="submit">Dodaj stawkę</button>
        </form>
        </div>

        <div class="container form-login">
        <h2>Zmiany</h2>
        <form action="add_shift.php" method="POST">
            <input type="text" name="shift_name" placeholder="Nazwa zmiany (np. Nocna)" required>
            <input type="time" name="start_time" required>
            <input type="time" name="end_time" required>
            <button type="submit">Dodaj zmianę</button>
        </form>
        </div>
    </div>
</body>

</html>