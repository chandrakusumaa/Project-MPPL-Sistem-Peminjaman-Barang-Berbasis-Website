<?php

use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Process\Process;

it('commits one membership and audit from two simultaneous MySQL joins', function () {
    Notification::fake();
    $this->seed();
    $user = User::where('email', 'other@example.test')->firstOrFail();
    $org = Organization::firstOrFail();
    $connection = DB::connection()->getConfig();
    $environment = [
        'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'CACHE_STORE' => 'array',
        'DB_HOST' => $connection['host'], 'DB_PORT' => (string) $connection['port'],
        'DB_DATABASE' => $connection['database'], 'DB_USERNAME' => $connection['username'], 'DB_PASSWORD' => $connection['password'],
        'MAIL_MAILER' => 'log',
    ];
    $barrier = tempnam(sys_get_temp_dir(), 'membership-race-');
    unlink($barrier);
    $processes = [];
    try {
        for ($index = 0; $index < 2; $index++) {
            $process = new Process([PHP_BINARY, base_path('tests/Support/membership-worker.php'), (string) $user->id, (string) $org->id, $barrier], base_path(), $environment);
            $process->setTimeout(30);
            $process->start();
            $processes[] = $process;
        }
        foreach ($processes as $process) {
            expect($process->waitUntil(fn () => str_contains($process->getOutput(), 'READY')))->toBeTrue();
        }
        touch($barrier);
        $ids = [];
        foreach ($processes as $process) {
            $process->wait();
            expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
            preg_match('/MEMBERSHIP:(\d+)/', $process->getOutput(), $match);
            $ids[] = (int) $match[1];
        }
        expect($ids[0])->toBe($ids[1]);
        $memberships = Membership::where('organization_id', $org->id)->where('user_id', $user->id)->get();
        expect($memberships)->toHaveCount(1)->and($memberships->first()->role)->toBe('member')
            ->and($memberships->first()->status)->toBe('active')
            ->and(AuditLog::where('action', 'membership.joined')->count())->toBe(1);
    } finally {
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                $process->stop();
            }
        }
        if (is_file($barrier)) {
            unlink($barrier);
        }
    }
});
