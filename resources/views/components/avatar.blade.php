@props(['name', 'image' => null])
<span {{ $attributes->merge(['class' => 'oi-avatar']) }} aria-hidden="true">
    @if($image)
        <img src="{{ asset('storage/'.$image) }}" alt="">
    @else
        {{ collect(preg_split('/\s+/', trim($name)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('') }}
    @endif
</span>
