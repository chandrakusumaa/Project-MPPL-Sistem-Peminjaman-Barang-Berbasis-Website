<?php

use App\Actions\ManageWorkspace;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Notification;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
Notification::fake();
[$file, $userId, $organizationId, $barrier] = $argv;
$user = User::findOrFail($userId);
$organization = Organization::findOrFail($organizationId);
echo "READY\n";
fflush(STDOUT);
while (! is_file($barrier)) {
    usleep(1000);
}
$membership = app(ManageWorkspace::class)->join($user, $organization);
echo 'MEMBERSHIP:'.$membership->id."\n";
