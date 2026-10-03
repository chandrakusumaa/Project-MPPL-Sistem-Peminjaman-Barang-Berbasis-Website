<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Volt::route('/explore', 'explore.organization-list')->name('explore.index');
Volt::route('/explore/{organization:slug}', 'explore.organization-profile')->name('explore.show');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Route::get('/verify-pending-email/{user}', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
        if (! $request->hasValidSignature()) {
            return redirect()->route('settings.profile')->with('error', 'Verifikasi gagal atau link sudah kedaluwarsa.');
        }
        
        if ($user->pending_email === $request->email) {
            $user->email = $user->pending_email;
            $user->pending_email = null;
            $user->email_verified_at = now();
            $user->save();
            return redirect()->route('settings.profile')->with('success', 'Email berhasil diperbarui!');
        }
        
        return redirect()->route('settings.profile')->with('error', 'Verifikasi gagal.');
    })->name('profile.verify-pending-email');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Volt::route('/organizations', 'organizations.index')->name('organizations.index');
    Volt::route('/organizations/create', 'organizations.create')->name('organizations.create');

    Volt::route('/my-borrowings', 'my-borrowings.index')->name('my-borrowings.index');
    Volt::route('/my-borrowings/{borrowing}', 'my-borrowings.show')->name('my-borrowings.show');

    Volt::route('/notifications', 'notifications.index')->name('notifications.index');
    Volt::route('/notifications/{id}', 'notifications.show')->name('notifications.show');
});

Volt::route('/invitations/{token}', 'invitations.accept')->name('invitations.accept');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('/o/{organization:slug}', 'catalog.index')->name('organization.catalog')->middleware('can:accessCatalog,organization');
    Volt::route('/o/{organization:slug}/assets/{asset:code}', 'catalog.show')->name('organization.asset.show')->middleware('can:accessCatalog,organization');

    Volt::route('/scan', 'scan.index')->name('scan');

    Route::prefix('o/{organization:slug}/manage')->name('manage.')->middleware('can:manage,organization')->group(function () {
        Volt::route('/dashboard', 'manage.dashboard')->name('dashboard');

        Volt::route('/categories', 'manage.categories.index')->name('categories.index');
        Volt::route('/inventory', 'manage.inventory.index')->name('inventory.index');
        Volt::route('/inventory/create', 'manage.inventory.create')->name('inventory.create');
        Volt::route('/inventory/{asset:code}', 'manage.inventory.show')->name('inventory.show');
        Volt::route('/inventory/{asset:code}/edit', 'manage.inventory.edit')->name('inventory.edit');
        Volt::route('/inventory/{asset:code}/print', 'manage.inventory.print')->name('inventory.print');

        Volt::route('/borrowings', 'manage.borrowings.index')->name('borrowings.index');
        Volt::route('/borrowings/{borrowing}', 'manage.borrowings.show')->name('borrowings.show');

        Volt::route('/damage-reports', 'manage.damage-reports.index')->name('damage-reports.index');
        Volt::route('/damage-reports/{damageReport}', 'manage.damage-reports.show')->name('damage-reports.show');

        Route::middleware('can:manageMembers,organization')->group(function () {
            Volt::route('/members', 'manage.members.index')->name('members.index');
        });

        // Reports route (authorization is inside the component)
        Volt::route('/reports', 'manage.reports')->name('reports.index');

        Route::middleware('can:manageSettings,organization')->group(function () {
            Volt::route('/settings', 'manage.settings.index')->name('settings.index');
        });
    });
});

require __DIR__.'/auth.php';
