<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lines = file(storage_path('app/test_barang.csv'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
array_shift($lines);
$count = 0;
foreach ($lines as $line) {
    $delimiter = strpos($line, ';') !== false ? ';' : ',';
    $row = str_getcsv($line, $delimiter);
    if (count($row) >= 5) {
        $nama = trim(str_replace('"', '', $row[0]));
        if (empty($nama)) continue;
        echo "Nama: " . strtoupper($nama) . " - Kat: " . strtoupper(trim(str_replace('"', '', $row[1]))) . "\n";
        $count++;
    }
}
echo "Total parsed: $count\n";
