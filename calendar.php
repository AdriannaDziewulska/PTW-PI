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

// Dane użytkownika
$stmt = $pdo->prepare("SELECT username, profile_photo FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $userData['username'];
$profilePhoto = $userData['profile_photo'];

// Zakres dat
$start_date = $_GET['start_date'] ?? "$year-$month-01";
$end_date = $_GET['end_date'] ?? "$year-$month-" . str_pad($days_in_month, 2, '0', STR_PAD_LEFT);

// Pobierz wpisy z bieżącego miesiąca
$stmt = $pdo->prepare("
    SELECT 
        e.entry_id, e.entry_date, 
        s.start_time, s.end_time, em.employer_background_color AS employer_color
    FROM entries e
    JOIN shifts s ON e.shift_id = s.shift_id
    JOIN employers em ON e.employer_id = em.employer_id
    WHERE e.user_id = ? AND YEAR(e.entry_date) = ? AND MONTH(e.entry_date) = ?
");

$stmt->execute([$user_id, $year, $month]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Pobierz wszystkie święta
$holidays = [];
$stmtHolidays = $pdo->query("SELECT date, name, holidays_background_color FROM holidays");
while ($row = $stmtHolidays->fetch(PDO::FETCH_ASSOC)) {
    $holidays[$row['date']] = [
        'name' => $row['name'],
        'color' => $row['holidays_background_color']
    ];
}


// Grupowanie po dacie
$entries_by_date = [];
foreach ($entries as $entry) {
    $date = $entry['entry_date'];
    if (!isset($entries_by_date[$date])) {
        $entries_by_date[$date] = [];
    }
    $entries_by_date[$date][] = $entry;
}


// STATYSTYKI - obliczenia ręczne z czasu i stawki
$stats_stmt = $pdo->prepare("
    SELECT e.entry_date, s.start_time, s.end_time, r.rate 
    FROM entries e
    JOIN shifts s ON e.shift_id = s.shift_id
    LEFT JOIN rate r ON e.rate_id = r.rate_id
    WHERE e.user_id = ? AND e.entry_date BETWEEN ? AND ?
");
$stats_stmt->execute([$user_id, $start_date, $end_date]);
$stats_entries = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);

$dailyStats = [];
$sum_hours = 0;
$sum_earned = 0;

foreach ($stats_entries as $e) {
    $date = $e['entry_date'];
    $start = new DateTime($e['start_time']);
    $end = new DateTime($e['end_time']);
    $interval = $start->diff($end);
    $hours = $interval->h + $interval->i / 60;
    $rate = $e['rate_per_hour'] ?? 0;
    $earned = round($hours * $rate, 2);

    $dailyStats[$date] = ($dailyStats[$date] ?? 0) + $hours;
    $sum_hours += $hours;
    $sum_earned += $earned;
}

ksort($dailyStats);
$labels = array_keys($dailyStats);
$hoursData = array_values($dailyStats);
$earningsData = array_map(fn($h) => round($h * 25, 2), $hoursData); // lub zamień 25 na dynamiczną stawkę

$summary = [
    'sum_hours' => $sum_hours,
    'sum_earned' => $sum_earned
];
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
                <img src="uploads/<?= htmlspecialchars($profilePhoto) ?>" alt="Avatar" class="avatar">
                <span>Witaj, <?= htmlspecialchars($username) ?></span>
                <form method="post" action="logout.php">
                    <button class="button" type="submit" style="margin: 0">Wyloguj</button>
                </form>
                <a href="settings.php">⚙️</a>
            </div>

        </div>
        <form method="get" action="day_details.php" style="margin: 40px; text-align: center;">
            <input type="date" name="date" required>
            <button type="submit">Szukaj dnia</button>
        </form>

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

                    $holiday = $holidays[$date_str] ?? null;
                    $holiday_style = $holiday ? "background-color: {$holiday['color']};" : "";
                    $holiday_title = $holiday ? htmlspecialchars($holiday['name']) : "";

                    echo "<td class='$is_today' style=\"$holiday_style\" title=\"$holiday_title\">";
                    echo "<div class='day-number'>$day</div>";
                    if ($holiday) {
                        echo "<div>" . htmlspecialchars($holiday['name']) . "</div>";
                    }

                    if (isset($entries_by_date[$date_str])) {
                        foreach ($entries_by_date[$date_str] as $entry) {
                            $start = substr($entry['start_time'], 0, 5);
                            $end = substr($entry['end_time'], 0, 5);
                            $color = htmlspecialchars($entry['employer_color'] ?? '#ccc');
                            echo "<a href='day_details.php?date=$date_str&id={$entry['entry_id']}' class='entry' style='color: $color;'>$start - $end</a>";
                        }
                    } else {
                        echo "<a href='add_entry.php?date=$date_str' class='entry' style='background-color: #222'>Dodaj wpis</a>";
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

        <a class="button" href="tips_list.php">Napiwki</a>


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