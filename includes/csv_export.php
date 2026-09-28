<?php
// Streams an array of rows straight to the browser as a downloadable CSV - no library needed.
// $headers = ['Column A', 'Column B']; $rows = [['a1','b1'], ['a2','b2'], ...]
function csv_download(string $filename, array $headers, array $rows): void {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
