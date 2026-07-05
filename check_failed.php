<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$failed = Illuminate\Support\Facades\DB::table('failed_jobs')->get();
foreach ($failed as $f) {
    echo "ID: {$f->id}\n";
    echo "Queue: {$f->queue}\n";
    $p = json_decode($f->payload, true);
    echo "DisplayName: " . ($p['displayName'] ?? 'N/A') . "\n";
    echo "--- Exception ---\n";
    echo substr($f->exception, 0, 2000) . "\n";
    echo str_repeat("=", 50) . "\n";
}
