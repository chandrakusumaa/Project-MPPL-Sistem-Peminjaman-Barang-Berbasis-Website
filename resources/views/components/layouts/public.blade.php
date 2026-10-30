<div class="oi-room oi-public">
    <x-navigation.topbar />
    <main class="oi-public-content">
        @if($showFeedback)<x-feedback />@endif
        {{ $slot }}
    </main>
</div>
