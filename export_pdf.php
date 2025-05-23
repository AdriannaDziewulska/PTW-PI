<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/tcpdf/tcpdf/tcpdf.php';

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Pracomierz');
$pdf->SetTitle('Eksport danych');
$pdf->SetHeaderData('', 0, 'Pracomierz - Eksport danych', '');
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

$html = '<h2>Eksport danych</h2>
<table border="1" cellpadding="4">
    <thead>
        <tr style="background-color:#f5f5f5;">
            <th><b>Data</b></th>
            <th><b>Start</b></th>
            <th><b>Koniec</b></th>
            <th><b>Stawka</b></th>
            <th><b>Godzin</b></th>
            <th><b>Zarobek</b></th>
        </tr>
    </thead>
    <tbody>';

$query = "SELECT entry_date, start_time, end_time, rate, total_hours, total_earned FROM entries ORDER BY entry_date ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $html .= '<tr>';
    foreach ($row as $cell) {
        $html .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('pracomierz_export.pdf', 'I');
