<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $limiter = Illuminate\Support\Facades\RateLimiter::limiter('mailtrap');
    if ($limiter) {
        echo "mailtrap rate limiter IS defined\n";
    } else {
        echo "mailtrap rate limiter is NOT defined (returns null)\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
