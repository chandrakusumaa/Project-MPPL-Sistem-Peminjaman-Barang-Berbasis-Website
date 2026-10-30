@props(['context' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Organization Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>
<body>
    @if($context === 'workspace')
        {{ $slot }}
    @elseif($context === 'public' || !auth()->check() || request()->routeIs('organization.public'))
        @include('components.layouts.public')
    @else
        @include('components.layouts.account')
    @endif
    @livewireScripts
    @fluxScripts
</body>
</html>
