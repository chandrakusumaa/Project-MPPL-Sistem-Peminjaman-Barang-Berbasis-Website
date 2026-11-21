@props(['label', 'value', 'note', 'icon', 'prefix' => ''])
<article class="metric-card oi-stat-card">
    <div class="metric-top oi-stat-top">
        <span class="metric-label">{{ $label }}</span>
        <span class="metric-icon oi-stat-icon"><x-icon :name="$icon" /></span>
    </div>
    <div class="metric-value oi-stat-number">{{ $prefix }}{{ number_format($value, 0, ',', '.') }}</div>
    <div class="metric-sub oi-stat-note">{{ $note }}</div>
</article>

