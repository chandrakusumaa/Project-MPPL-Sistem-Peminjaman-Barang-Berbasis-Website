@props(['organization'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxStyles
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            
            <a href="#" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo class="size-8" href="#"></x-app-logo>
                <span class="font-bold text-lg">Manage</span>
            </a>

            @php
                $org = $organization ?? request()->route('organization');
                if (is_string($org)) {
                    $org = \App\Models\Organization::where('slug', $org)->first();
                }
                $org = $org ?? new \App\Models\Organization;
                $isStaff = auth()->check() && auth()->user()->isStaffOf($org);
            @endphp

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Manajemen">
                    <x-nav-item icon="chart-bar" href="{{ $org->slug ? route('manage.dashboard', $org) : '#' }}">Dashboard</x-nav-item>
                    <x-nav-item icon="cube" href="{{ $org->slug ? route('manage.inventory.index', $org) : '#' }}">Inventory</x-nav-item>
                    <x-nav-item icon="tag" href="{{ $org->slug ? route('manage.categories.index', $org) : '#' }}">Categories</x-nav-item>
                    <x-nav-item icon="clipboard-document-list" href="{{ $org->slug ? route('manage.borrowings.index', $org) : '#' }}">Borrowing</x-nav-item>
                    <x-nav-item icon="users" href="{{ !$isStaff && $org->slug ? route('manage.members.index', $org) : '#' }}" :disabled="$isStaff">Members</x-nav-item>
                    <x-nav-item icon="exclamation-triangle" href="{{ $org->slug ? route('manage.damage-reports.index', $org) : '#' }}">Damage Reports</x-nav-item>
                    <x-nav-item icon="document-chart-bar" href="{{ !$isStaff && $org->slug ? route('manage.reports.index', $org) : '#' }}" :disabled="$isStaff">Reports</x-nav-item>
                    <x-nav-item icon="cog-6-tooth" href="{{ !$isStaff && $org->slug ? route('manage.settings.index', $org) : '#' }}" :disabled="$isStaff">Settings</x-nav-item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <div class="flex items-center gap-2">
                <livewire:notifications.bell />
                <flux:dropdown position="bottom" align="start">
                    <flux:profile :name="auth()->user()->name ?? 'User'" :initials="auth()->user()?->initials() ?? 'U'" icon-trailing="chevrons-up-down" />
                <flux:menu class="w-[220px]">
                    <flux:menu.item href="#" icon="user" wire:navigate>Profile</flux:menu.item>
                    <flux:menu.item href="{{ route('organizations.index') }}" icon="arrow-left" wire:navigate>Back to Member Area</flux:menu.item>
                    <form method="POST" action="#" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">Log Out</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            </div>
        </flux:sidebar>
        
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        </flux:header>

        <flux:main>
            {{ $slot }}
        </flux:main>

        @fluxScripts
        <x-toast-wrapper />
    </body>
</html>
