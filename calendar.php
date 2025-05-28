<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$first_day_week = date('N', strtotime("$year-$month-01"));

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$username = $stmt->fetchColumn();

// Zakres dat (dla statystyk)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : "$year-$month-01";
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : "$year-$month-" . str_pad($days_in_month, 2, '0', STR_PAD_LEFT);

// Wpisy do kalendarza (tylko ten miesiąc)
$stmt = $pdo->prepare("SELECT id, entry_date, start_time, end_time FROM entries WHERE user_id = ? AND YEAR(entry_date) = ? AND MONTH(entry_date) = ?");
$stmt->execute([$user_id, $year, $month]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$entries_by_date = [];
foreach ($entries as $entry) {
    $date = $entry['entry_date'];
    if (!isset($entries_by_date[$date])) {
        $entries_by_date[$date] = [];
    }
    $entries_by_date[$date][] = $entry;
}

// Statystyki dla wykresów (dla dowolnego zakresu)
$stats_stmt = $pdo->prepare("SELECT entry_date, total_hours FROM entries WHERE user_id = ? AND entry_date BETWEEN ? AND ?");
$stats_stmt->execute([$user_id, $start_date, $end_date]);
$stats_entries = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Grupowanie godzin po dacie
$dailyStats = [];
foreach ($stats_entries as $e) {
    $date = $e['entry_date'];
    $hours = (float)$e['total_hours'];
    if (!isset($dailyStats[$date])) $dailyStats[$date] = 0;
    $dailyStats[$date] += $hours;
}
ksort($dailyStats);

$labels = array_keys($dailyStats);
$hoursData = array_values($dailyStats);
$hourlyRate = 25;
$earningsData = array_map(fn($h) => round($h * $hourlyRate, 2), $hoursData);

$stmt = $pdo->prepare("
    SELECT 
        SUM(total_hours) AS sum_hours, 
        SUM(total_earned) AS sum_earned 
    FROM entries 
    WHERE user_id = ? AND entry_date BETWEEN ? AND ?
");
$stmt->execute([$user_id, $start_date, $end_date]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Pracomierz - Kalendarz</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0">Pracomierz - Kalendarz</h2>
            <div class="user-login">
                <span>Witaj, <?= htmlspecialchars($username) ?></span>
                <form method="post" action="logout.php">
                    <button class="button" type="submit" style="margin: 0">Wyloguj</button>
                </form>
            </div>
        </div>
        <?php
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

        // Poprzedni / następny miesiąc
        $prevMonth = $month - 1;
        $nextMonth = $month + 1;
        $prevYear = $year;
        $nextYear = $year;

        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $monthNames = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
        ?>

        <div style="text-align: center; margin-bottom: 20px; margin-top: 25px;">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" style="margin-right: 30px;">&#8592; Poprzedni</a>
            <strong><?= $monthNames[$month - 1] ?> <?= $year ?></strong>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" style="margin-left: 30px;">Następny &#8594;</a>
        </div>

        <table class="calendar">
            <tr>
                <th>Pon</th>
                <th>Wt</th>
                <th>Śr</th>
                <th>Czw</th>
                <th>Pt</th>
                <th style="background-color: #22284B">Sob</th>
                <th style="background-color: #4B2222">Nd</th>
            </tr>
            <tr>
                <?php
                for ($i = 1; $i < $first_day_week; $i++) echo '<td></td>';

                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date_str = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $is_today = ($date_str == date('Y-m-d')) ? 'today' : '';

                    echo "<td class='$is_today'>";
                    echo "<div class='day-number'>$day</div>";

                    if (isset($entries_by_date[$date_str])) {
                        foreach ($entries_by_date[$date_str] as $entry) {
                            $start = substr($entry['start_time'], 0, 5);
                            $end = substr($entry['end_time'], 0, 5);
                            echo "<a href='day_details.php?date=$date_str&id={$entry['id']}' class='entry'>$start - $end</a>";
                        }
                    } else {
                        echo "<a href='add_entry.php?date=$date_str' style='font-size:0.8em;color:#ccc;'>Dodaj wpis</a>";
                    }
                    echo "</td>";

                    if (($day + $first_day_week - 1) % 7 == 0) echo "</tr><tr>";
                }

                $last_day_of_week = ($days_in_month + $first_day_week - 1) % 7;
                if ($last_day_of_week != 0) {
                    for ($i = $last_day_of_week; $i < 7; $i++) echo '<td></td>';
                }
                ?>
            </tr>
        </table>

        <form class="range" method="get">
            <h3>Wybierz zakres dat do statystyk</h3>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <button type="submit">Zastosuj</button>
        </form>

        <h3 style="padding-top: 50px;">Statystyki</h3>
        <div class="summary">
            <div class="summary-box">
                Łącznie godzin:<br>
                <?= number_format($summary['sum_hours'] ?? 0, 2) ?>
            </div>
            <div class="summary-box">
                Łącznie zarobek:<br>
                <?= number_format($summary['sum_earned'] ?? 0, 2) ?> zł
            </div>
        </div>

        <div>
            <canvas id="hoursChart" style="margin-bottom:40px;"></canvas>
            <canvas id="earningsChart"></canvas>
        </div>


        <div class="footer" style="padding-top:30px;">
            <a href="export.php" class="button">Eksportuj dane (CSV)</a>
        </div>
        <div class="footer" style="padding-bottom: 30px;">
            <a href="export_pdf.php" class="button">Eksportuj dane (PDF)</a>
        </div>

        <script>
            const labels = <?= json_encode($labels) ?>;
            const hoursData = <?= json_encode($hoursData) ?>;
            const earningsData = <?= json_encode($earningsData) ?>;

            const chartOptions = {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + ctx.raw + (ctx.dataset.label === 'Zarobki' ? ' zł' : ' h')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'gold'
                        },
                        grid: {
                            color: '#444'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'gold'
                        },
                        grid: {
                            color: '#333'
                        }
                    }
                }
            };

            new Chart(document.getElementById('hoursChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Godziny',
                        data: hoursData,
                        backgroundColor: 'rgba(255, 215, 0, 0.6)',
                        borderColor: 'gold',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });

            new Chart(document.getElementById('earningsChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Zarobki',
                        data: earningsData,
                        borderColor: 'gold',
                        backgroundColor: 'rgba(255, 215, 0, 0.2)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: chartOptions
            });
        </script>
    </div>

    </div>
</body>

</html>