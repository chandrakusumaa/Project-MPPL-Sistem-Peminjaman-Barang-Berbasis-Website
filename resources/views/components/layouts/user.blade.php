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
                <span class="font-bold text-lg">Inventory</span>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Area Member">
                    <x-nav-item icon="home" href="#">Home</x-nav-item>
                    <x-nav-item icon="building-office" href="#">Organizations</x-nav-item>
                    <x-nav-item icon="clipboard-document-list" href="#">My Borrowing</x-nav-item>
                    <x-nav-item icon="bell" href="#">Notifications</x-nav-item>
                    <x-nav-item icon="qr-code" href="#">Scan QR</x-nav-item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:dropdown position="bottom" align="start">
                <flux:profile :name="auth()->user()->name ?? 'User'" :initials="auth()->user()?->initials() ?? 'U'" icon-trailing="chevrons-up-down" />
                <flux:menu class="w-[220px]">
                    <flux:menu.item href="#" icon="user" wire:navigate>Profile</flux:menu.item>
                    <form method="POST" action="#" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">Log Out</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
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
