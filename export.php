<?php
require_once __DIR__ . '/includes/db.php';  

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Data', 'Godzina startu', 'Godzina końca', 'Stawka', 'Liczba godzin', 'Zarobek']);

$query = "
SELECT 
    e.entry_date, 
    s.start_time, 
    s.end_time, 
    r.rate
FROM entries e
JOIN shifts s ON e.shift_id = s.shift_id
JOIN rate r ON e.rate_id = r.rate_id
ORDER BY e.entry_date ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $start = new DateTime($row['start_time']);
    $end = new DateTime($row['end_time']);
    $interval = $start->diff($end);
    $hours = $interval->h + $interval->i / 60;
    $earned = round($hours * $row['rate'], 2);

    fputcsv($output, [
        $row['entry_date'],
        $row['start_time'],
        $row['end_time'],
        $row['rate'],
        number_format($hours, 2),
        number_format($earned, 2)
    ]);
}

fclose($output);
exit;
