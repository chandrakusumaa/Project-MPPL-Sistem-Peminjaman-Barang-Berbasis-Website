<?php

use App\Actions\ManageWorkspace;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\QrController;
use App\Livewire\ExploreOrganizations;
use App\Livewire\Home;
use App\Livewire\Notifications;
use App\Livewire\PersonalBorrowings;
use App\Livewire\PersonalCharges;
use App\Livewire\PersonalDashboard;
use App\Livewire\Profile;
use App\Livewire\Reports;
use App\Livewire\Workspace;
use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\BorrowerCharge;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', fn () => view('landing'))->name('landing');
Route::get('/organizations', ExploreOrganizations::class)->name('organizations');
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth', ['mode' => 'login']))->name('login');
    Route::get('/register', fn () => view('auth', ['mode' => 'register']))->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store')->middleware('throttle:6,1');
    Route::get('/forgot-password', fn () => view('auth', ['mode' => 'forgot-password']))->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', fn (string $token) => view('auth', ['mode' => 'reset-password', 'token' => $token]))->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update')->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/email/verify', fn () => view('verify'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/dashboard');
    })->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Email verifikasi dikirim.');
    })->middleware('throttle:3,1')->name('verification.send');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', PersonalDashboard::class)->name('dashboard');
    Route::get('/my-organizations', Home::class)->name('personal.organizations');
    Route::get('/my-charges', PersonalCharges::class)->name('personal.charges');
    Route::get('/o/{organization:slug}', Workspace::class)->name('workspace');
    Route::get('/o/{organization:slug}/assets/{asset:code}/qr', QrController::class)->name('asset.qr');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/notifications', Notifications::class)->name('notifications');
    Route::get('/evidence/{charge}/{payment?}', function (BorrowerCharge $charge, ?int $payment = null) {
        $org = Organization::findOrFail($charge->organization_id);
        Gate::authorize('view', $org);
        abort_unless($charge->borrower_id === auth()->id() || Gate::allows('operate', $org), 403);
        $path = $payment ? $charge->payments()->findOrFail($payment)->evidence_path : $charge->evidence_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    })->name('evidence');
});
Route::get('/o/{organization:slug}/export/{type}', ExportController::class)->middleware(['auth', 'verified'])->name('export');
Route::get('/scan', function (Request $request) {
    if ($request->filled('code')) {
        $asset = Asset::where('code', $request->validate(['code' => 'required|alpha_num|size:26'])['code'])->firstOrFail();
        $org = $asset->organization;
        Gate::authorize('view', $org);

        return redirect()->route('asset.show', [$org, $asset]);
    }

    return view('scan');
})->middleware(['auth', 'verified']);
Route::get('/profile/email/{user}/{hash}/{version}', function (Request $request, User $user, string $hash, int $version) {
    abort_unless(auth()->id() === $user->id, 403);
    DB::transaction(function () use ($user, $hash, $version) {
        $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
        abort_unless($user->pending_email && hash_equals(hash('sha256', $user->pending_email), $hash) && $user->pending_email_requested_at->getTimestamp() === $version, 403);
        abort_if(User::withTrashed()->where('email', $user->pending_email)->exists(), 422);
        $user->forceFill(['email' => $user->pending_email, 'email_verified_at' => now(), 'pending_email' => null, 'pending_email_requested_at' => null])->save();
    });

    return redirect('/profile')->with('status', 'Email diperbarui.');
})->middleware(['auth', 'signed'])->name('profile.email.verify');
Route::get('/o/{organization:slug}/assets/{asset:code}/timeline', function (Organization $organization, Asset $asset) {
    Gate::authorize('view', $organization);
    abort_unless($asset->organization_id === $organization->id, 404);

    return view('timeline', ['asset' => $asset, 'logs' => AssetLog::where('asset_id', $asset->id)->latest('id')->paginate(20), 'operator' => Gate::allows('operate', $organization)]);
})->middleware(['auth', 'verified'])->name('asset.timeline');
Route::get('/ruang/{organization:slug}', function (Organization $organization) {
    abort_if($organization->archived_at, 404);

    return view('organization-public', ['org' => $organization, 'memberCount' => $organization->memberships()->where('status', 'active')->count(), 'assetCount' => $organization->assets()->count()]);
})->name('organization.public');
Route::post('/ruang/{organization:slug}/join', function (Organization $organization) {
    $membership = app(ManageWorkspace::class)->join(auth()->user(), $organization);

    return redirect()->route('workspace', $organization)->with('status', $membership->wasRecentlyCreated
        ? 'Berhasil bergabung sebagai Member. Selamat datang di Organization!'
        : 'Keanggotaanmu sudah aktif. Buka Organization untuk melanjutkan.');
})->middleware(['auth', 'verified'])->name('organization.join');
Route::get('/o/{organization:slug}/assets/{asset:code}', Workspace::class)->middleware(['auth', 'verified'])->name('asset.show');
Route::get('/my-borrowings', PersonalBorrowings::class)->middleware(['auth', 'verified'])->name('personal.borrowings');
Route::get('/o/{organization:slug}/reports', Reports::class)->middleware(['auth', 'verified'])->name('reports');
