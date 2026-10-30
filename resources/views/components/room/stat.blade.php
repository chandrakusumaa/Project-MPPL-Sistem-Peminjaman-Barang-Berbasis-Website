@props(['label', 'value', 'note', 'icon'])
<article class="oi-stat-card">
    <div class="oi-stat-top"><span>{{ $label }}</span><span class="oi-stat-icon"><x-icon :name="$icon" /></span></div>
    <div class="oi-stat-number">{{ number_format($value, 0, ',', '.') }}</div>
    <div class="oi-stat-note">{{ $note }}</div>
</article>
