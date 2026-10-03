<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Organization Inventory</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 min-h-screen flex items-center justify-center">
        <div class="max-w-4xl mx-auto px-6 py-12 text-center">
            <h1 class="text-5xl font-bold mb-6 text-zinc-800 dark:text-zinc-100">Welcome to Organization Inventory</h1>
            <p class="text-xl mb-12 text-zinc-600 dark:text-zinc-400">
                A modern platform to manage assets, track borrowings, and maintain inventory across multiple organizations seamlessly.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Explore Card -->
                <a href="{{ route('explore.index') }}" class="block p-8 bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition-shadow group">
                    <div class="text-4xl mb-4">🔍</div>
                    <h2 class="text-2xl font-semibold mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Explore Organization</h2>
                    <p class="text-zinc-500 dark:text-zinc-400">Temukan organisasi dan lihat katalog publik mereka.</p>
                </a>

                <!-- Login Card -->
                <a href="{{ route('login') }}" class="block p-8 bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition-shadow group">
                    <div class="text-4xl mb-4">🔑</div>
                    <h2 class="text-2xl font-semibold mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Log In</h2>
                    <p class="text-zinc-500 dark:text-zinc-400">Masuk ke akun Anda untuk mengelola inventaris.</p>
                </a>

                <!-- Register Card -->
                <a href="{{ route('register') }}" class="block p-8 bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 hover:shadow-md transition-shadow group">
                    <div class="text-4xl mb-4">✨</div>
                    <h2 class="text-2xl font-semibold mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Register</h2>
                    <p class="text-zinc-500 dark:text-zinc-400">Buat akun baru dan mulai gunakan platform kami.</p>
                </a>
            </div>
        </div>
    </body>
</html>
