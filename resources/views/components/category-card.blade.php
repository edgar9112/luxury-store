@props(['category'])

@php
    $art = match ($category->slug) {
        'relojes' => 'watch',
        'ropa' => 'coat',
        'calzado' => 'sneaker',
        default => 'bag',
    };

    $tone = match ($category->slug) {
        'relojes' => 'tone-charcoal',
        'ropa' => 'tone-stone',
        'calzado' => 'tone-sand',
        default => 'tone-clay',
    };
@endphp

<a href="{{ route('catalog.index', ['categoria' => $category->slug]) }}" class="group block">
    <div class="media {{ $tone }} flex aspect-[4/5] items-center justify-center">
        <svg class="h-1/2 w-1/2 text-ink/70 transition duration-500 ease-out group-hover:scale-105" viewBox="0 0 200 200"
            aria-hidden="true">
            <use href="#{{ $art }}"></use>
        </svg>
    </div>
    <h3 class="mt-4 font-display text-2xl">{{ $category->name }}</h3>
    <span class="link-arrow mt-1 text-xs">Explorar</span>
</a>
