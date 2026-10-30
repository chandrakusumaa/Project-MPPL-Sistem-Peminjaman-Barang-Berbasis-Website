@props(['name', 'size' => 18])
@php
    $paths = [
        'grid' => 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
        'box' => 'M3 7l9-4 9 4-9 4-9-4z M3 7v10l9 4 9-4V7 M12 11v10 M7 5l10 4',
        'swap' => 'M4 7h16 M16 3l4 4-4 4 M20 17H4 M8 13l-4 4 4 4',
        'warning' => 'M12 3L2 21h20L12 3z M12 9v5 M12 17v.1',
        'tool' => 'M14 5a5 5 0 0 0-5 7L3 18a2 2 0 0 0 3 3l6-6a5 5 0 0 0 7-5l-4 2-3-3 2-4z',
        'layers' => 'M3 6l9-4 9 4-9 4-9-4z M3 11l9 4 9-4 M3 16l9 4 9-4',
        'pin' => 'M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0z M15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0',
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0 M22 21v-2a4 4 0 0 0-3-4 M16 3a4 4 0 0 1 0 8',
        'report' => 'M4 3v18h17 M8 16v-4 M13 16V7 M18 16v-7',
        'home' => 'M3 10l9-7 9 7 M5 9v12h14V9 M9 21v-7h6v7',
        'clock' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0 M12 7v5l3 2',
        'bell' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9 M10 21h4',
        'user' => 'M20 21v-2a7 7 0 0 0-14 0v2 M17 7a5 5 0 1 1-10 0 5 5 0 0 1 10 0',
        'check' => 'M5 12l4 4L20 5',
        'plus' => 'M12 5v14 M5 12h14',
        'camera' => 'M3 7h4l2-3h6l2 3h4v14H3z M16 13a4 4 0 1 1-8 0 4 4 0 0 1 8 0',
        'laptop' => 'M5 3h14v13H5z M2 20l3-4h14l3 4H2z',
        'projector' => 'M3 3h18v12H3z M12 15v6 M7 21l5-4 5 4 M8 7l6 3-6 2V7z',
        'speaker' => 'M11 4L6 8H2v8h4l5 4V4z M15 8a6 6 0 0 1 0 8 M18 5a10 10 0 0 1 0 14',
        'chair' => 'M5 3h14v10H5z M3 17h18 M5 13v8 M19 13v8',
        'mic' => 'M16 7a4 4 0 0 1-8 0V5a4 4 0 0 1 8 0v2z M5 8a7 7 0 0 0 14 0 M12 15v6 M8 21h8',
        'book' => 'M3 3h7l2 2 2-2h7v16h-7l-2 2-2-2H3V3z M12 5v16',
        'tripod' => 'M7 3h10v6H7z M12 9v12 M12 9L4 21 M12 9l8 12',
        'menu' => 'M4 6h16 M4 12h16 M4 18h16',
        'close' => 'M6 6l12 12 M18 6L6 18',
        'external' => 'M14 3h7v7 M21 3l-11 11 M10 3H3v18h18v-7',
        'arrow-left' => 'M19 12H5 M11 6l-6 6 6 6',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'oi-icon']) }} viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['grid'] }}"/></svg>
