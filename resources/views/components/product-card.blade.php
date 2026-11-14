@props(['product'])

<a href="{{ route('product.show', $product) }}" class="group block">
    <div class="media {{ $product->toneClass() }} flex aspect-[4/5] items-center justify-center">
        @if ($product->compare_price_cents)
            <span class="absolute left-3 top-3 z-10 bg-ivory/90 px-3 py-1 text-[10px] uppercase tracking-[0.16em] text-gold-deep">
                Oferta
            </span>
        @endif

        <svg class="h-1/2 w-1/2 text-ink/70 transition duration-500 ease-out group-hover:scale-105" viewBox="0 0 200 200"
            aria-hidden="true">
            <use href="#{{ $product->artName() }}"></use>
        </svg>
    </div>

    <div class="mt-4 flex items-start justify-between gap-4">
        <div>
            <h3 class="font-display text-lg leading-tight">{{ $product->name }}</h3>
            <p class="mt-1 text-[11px] uppercase tracking-[0.16em] text-muted">
                {{ $product->category?->name }} · {{ $product->material }}
            </p>
        </div>
        <p class="whitespace-nowrap text-sm">{{ $product->formattedPrice() }}</p>
    </div>
</a>
