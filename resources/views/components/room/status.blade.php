@props(['value'])
@php
    [$label, $color] = match($value) {
        'available' => ['Tersedia', 'green'],
        'borrowed' => ['Dipinjam', 'blue'],
        'overdue' => ['Terlambat', 'red'],
        'maintenance' => ['Maintenance', 'amber'],
        'lost' => ['Hilang', 'red'],
        'pending' => ['Menunggu', 'amber'],
        default => [ucfirst($value), 'neutral'],
    };
@endphp
<span class="oi-tag oi-tag-{{ $color }}">{{ $label }}</span>
