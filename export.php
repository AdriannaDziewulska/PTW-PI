<?php
require_once __DIR__ . '/includes/db.php';  

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="export.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Data', 'Godzina startu', 'Godzina końca', 'Stawka', 'Liczba godzin', 'Zarobek']);

$query = "SELECT entry_date, start_time, end_time, rate, total_hours, total_earned FROM entries ORDER BY entry_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
