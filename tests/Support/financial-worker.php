<?php

use App\Actions\BorrowAsset;
use App\Actions\ManageMembers;
use App\Actions\SettleCharge;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
[$file,$actor,$org,$charge,$action,$target,$amount,$barrier] = $argv;
while (! is_file($barrier)) {
    usleep(1000);
}
try {
    if ($action === 'archive') {
        app(ManageMembers::class)->archive(User::findOrFail($actor), Organization::findOrFail($org));
    } elseif ($action === 'approve') {
        app(BorrowAsset::class)->decide(User::findOrFail($actor), Organization::findOrFail($org), (int) $target, 'approve');
    } else {
        app(SettleCharge::class)->change(User::findOrFail($actor), Organization::findOrFail($org), (int) $charge, $action, ['payment_id' => (int) $target, 'amount' => (int) $amount, 'idempotency_key' => 'concurrent:'.$action.':'.$target, 'reason' => 'Koreksi melalui uji paralel']);
    }
    echo 'committed';
} catch (ValidationException $e) {
    echo 'rejected';
    exit(2);
}
