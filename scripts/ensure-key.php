<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! config('app.key')) {
    Artisan::call('key:generate', ['--force' => true]);
    echo "Application key generated.\n";
} else {
    echo "Existing application key preserved.\n";
}
