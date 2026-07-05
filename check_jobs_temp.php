<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$jobs = Illuminate\Support\Facades\DB::table('jobs')->get();
foreach ($jobs as $j) {
    $p = json_decode($j->payload, true);
    echo "ID: {$j->id}, Queue: {$j->queue}\n";
    echo "  DisplayName: " . ($p['displayName'] ?? 'N/A') . "\n";
    echo "  Job: " . ($p['job'] ?? 'N/A') . "\n";
    echo "  MaxTries: " . ($p['maxTries'] ?? 'N/A') . "\n";
    echo "  Attempts: {$j->attempts}\n";
    echo "  Created: {$j->created_at}, Available: {$j->available_at}\n";
    echo "\n";
}
