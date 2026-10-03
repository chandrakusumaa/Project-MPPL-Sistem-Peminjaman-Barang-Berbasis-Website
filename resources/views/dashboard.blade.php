<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col items-center justify-center gap-4 rounded-xl">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Selamat datang, {{ auth()->user()->name }}</h1>
    </div>
</x-layouts.app>
