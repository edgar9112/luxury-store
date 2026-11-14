@use('App\Support\Money')
@use('Illuminate\Support\Js')

@php
    $sizeOrder = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6];

    $variants = $product->activeVariants
        ->sortBy(function ($variant) use ($sizeOrder) {
            if (blank($variant->size)) {
                return 0;
            }

            if (isset($sizeOrder[$variant->size])) {
                return 100 + $sizeOrder[$variant->size];
            }

            if (preg_match('/(\d+)/', $variant->size, $matches)) {
                return 200 + (int) $matches[1];
            }

            return 300;
        })
        ->values();

    $defaultVariant = $variants->first(fn ($variant) => $variant->stock > 0) ?? $variants->first();

    $variantsData = $variants
        ->map(fn ($variant) => [
            'id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
            'hex' => $variant->color_hex,
            'price' => $variant->effectivePriceCents(),
            'formatted_price' => Money::format($variant->effectivePriceCents()),
            'stock' => $variant->stock,
        ])
        ->values();

    $colors = $variants
        ->filter(fn ($variant) => filled($variant->color))
        ->unique('color')
        ->map(fn ($variant) => [
            'name' => $variant->color,
            'hex' => $variant->color_hex ?: '#c9a961',
        ])
        ->values();

    $sizes = $variants->pluck('size')->filter()->unique()->values();

    $galleryTones = [$product->toneClass(), 'tone-clay', 'tone-stone', 'tone-sand'];
    $galleryLabels = [
        $product->collection ?: 'Edición limitada',
        'Detalle de confección',
        'Textura y acabado',
        'Vista en detalle',
    ];

    $discount = $product->compare_price_cents
        ? (int) round(100 - ($product->price_cents / $product->compare_price_cents) * 100)
        : null;
@endphp

<x-layouts.app :title="$product->name.' — Luxury Store'" :description="$product->short_description">
    {{-- Migas de pan --}}
    <nav class="container-store flex flex-wrap items-center gap-2 py-6 text-[11px] uppercase tracking-[0.16em] text-muted"
        aria-label="Migas de pan">
        <a href="{{ route('home') }}" class="transition hover:text-gold-deep">Inicio</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('catalog.index', ['categoria' => $product->category?->slug]) }}"
            class="transition hover:text-gold-deep">{{ $product->category?->name }}</a>
        <span class="opacity-40">/</span>
        <span class="text-ink">{{ $product->name }}</span>
    </nav>

    <div class="container-store grid gap-12 pb-8 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16">
        {{-- Galería --}}
        <section aria-label="Imágenes del producto" x-data="{ view: 0 }">
            <div class="relative aspect-[4/5] overflow-hidden border border-line">
                @foreach ($galleryTones as $index => $tone)
                    <div x-show="view === {{ $index }}" x-transition.opacity
                        class="media {{ $tone }} absolute inset-0 flex items-center justify-center">
                        <span class="media-tag">
                            {{ $galleryLabels[$index] }}
                        </span>

                        @if ($index === 0 && $discount)
                            <span
                                class="absolute right-4 top-4 z-10 border border-gold/40 bg-ivory/90 px-3 py-1 text-[10px] uppercase tracking-[0.18em] text-gold-deep">
                                −{{ $discount }}%
                            </span>
                        @endif

                        <svg class="h-2/3 w-2/3 text-ink/70 {{ $index > 0 ? 'opacity-90' : '' }}" viewBox="0 0 200 200"
                            aria-hidden="true">
                            <use href="#{{ $product->artName() }}"></use>
                        </svg>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid grid-cols-4 gap-3">
                @foreach ($galleryTones as $index => $tone)
                    <button type="button" class="media {{ $tone }} aspect-square border transition"
                        :class="view === {{ $index }} ? 'border-gold' : 'border-transparent hover:border-gold/50'"
                        @click="view = {{ $index }}"
                        :aria-pressed="(view === {{ $index }}).toString()"
                        aria-label="Vista {{ $index + 1 }} del producto">
                        <svg class="absolute inset-0 m-auto h-1/2 w-1/2 text-ink/70" viewBox="0 0 200 200"
                            aria-hidden="true">
                            <use href="#{{ $product->artName() }}"></use>
                        </svg>
                    </button>
                @endforeach
            </div>

            <ul class="mt-6 grid grid-cols-3 gap-3 border-y border-line py-4 text-[10px] uppercase tracking-[0.14em] text-muted">
                <li class="flex items-center justify-center gap-2 text-center">
                    <x-ui-icon name="truck" :size="16" /> Envío asegurado
                </li>
                <li class="flex items-center justify-center gap-2 text-center">
                    <x-ui-icon name="return" :size="16" /> 30 días
                </li>
                <li class="flex items-center justify-center gap-2 text-center">
                    <x-ui-icon name="lock" :size="16" /> Pago cifrado
                </li>
            </ul>
        </section>

        {{-- Ficha --}}
        <section aria-label="Detalles del producto"
            x-data="productOptions({{ Js::from([
                'variants' => $variantsData,
                'selected' => $defaultVariant?->id,
                'basePrice' => $product->price_cents,
                'baseFormatted' => $product->formattedPrice(),
            ]) }})">
            <p class="eyebrow">{{ $product->category?->name }} · {{ $product->collection }}</p>
            <h1 class="mt-3 font-display text-4xl leading-tight sm:text-5xl">{{ $product->name }}</h1>

            <div class="mt-4 flex items-center gap-3 text-xs text-muted">
                <span class="flex items-center gap-0.5 text-gold" aria-hidden="true">
                    @for ($i = 0; $i < 5; $i++)
                        <x-ui-icon name="star" :size="13" />
                    @endfor
                </span>
                <span>4,9 · 64 valoraciones</span>
            </div>

            <div class="mt-6 flex flex-wrap items-baseline gap-3" aria-live="polite">
                <p class="font-display text-3xl" x-text="formattedPrice">{{ $product->formattedPrice() }}</p>

                @if ($product->compare_price_cents)
                    <p class="text-sm text-muted line-through">{{ $product->formattedComparePrice() }}</p>
                @endif

                <p class="text-[11px] uppercase tracking-[0.14em] text-muted">Impuestos en el checkout</p>
            </div>

            <p class="mt-6 max-w-xl leading-relaxed text-muted">
                {{ $product->short_description }}
            </p>

            <hr class="my-7 border-line">

            {{-- Acabado --}}
            @if ($colors->isNotEmpty())
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]">Acabado</p>
                    <p class="text-xs text-muted" x-text="color ?? '—'">{{ $defaultVariant?->color }}</p>
                </div>

                <div class="mt-3 flex flex-wrap gap-3" role="group" aria-label="Acabado">
                    @foreach ($colors as $color)
                        <button type="button"
                            class="grid h-9 w-9 place-items-center rounded-full border border-line transition"
                            style="background-color: {{ $color['hex'] }}"
                            :class="{
                                'ring-2 ring-gold ring-offset-2 ring-offset-cream': color === @js($color['name']),
                                'opacity-40': !hasColor(@js($color['name']))
                            }"
                            @click="selectColor(@js($color['name']))"
                            :aria-pressed="(color === @js($color['name'])).toString()"
                            aria-label="{{ $color['name'] }}"
                            title="{{ $color['name'] }}"></button>
                    @endforeach
                </div>
            @endif

            {{-- Talla / diámetro --}}
            @if ($sizes->isNotEmpty())
                <div class="mt-7 flex items-center justify-between gap-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em]">
                        {{ $product->category?->slug === 'relojes' ? 'Diámetro' : 'Talla' }}
                    </p>
                    <a href="#guia-medidas" class="text-xs text-gold-deep underline underline-offset-4">
                        Guía de medidas
                    </a>
                </div>

                <div class="mt-3 flex flex-wrap gap-2" role="group"
                    aria-label="{{ $product->category?->slug === 'relojes' ? 'Diámetro' : 'Talla' }}">
                    @foreach ($sizes as $size)
                        <button type="button"
                            class="min-w-14 border border-line bg-ivory px-4 py-2.5 text-sm transition hover:border-gold"
                            :class="{
                                'border-gold-deep bg-gold-deep text-ivory hover:border-gold-deep': size === @js($size),
                                'opacity-40 line-through': !hasSize(@js($size))
                            }"
                            @click="selectSize(@js($size))"
                            :disabled="!hasSize(@js($size))"
                            :aria-pressed="(size === @js($size)).toString()">
                            {{ $size }}
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Compra --}}
            <form action="{{ route('cart.store') }}" method="POST" class="mt-8">
                @csrf
                <input type="hidden" name="variant_id" :value="selected ?? ''">
                <input type="hidden" name="quantity" :value="quantity">

                <div class="flex flex-wrap items-stretch gap-3">
                    <div class="flex h-[52px] items-center border border-line bg-ivory">
                        <button type="button" class="grid h-full w-11 place-items-center text-lg text-muted transition hover:text-ink"
                            @click="decrease()" :disabled="quantity <= 1" aria-label="Quitar una unidad">
                            &minus;
                        </button>
                        <span class="w-9 text-center text-sm" x-text="quantity" aria-live="polite">1</span>
                        <button type="button" class="grid h-full w-11 place-items-center text-lg text-muted transition hover:text-ink"
                            @click="increase()" :disabled="quantity >= stock" aria-label="Añadir una unidad">
                            +
                        </button>
                    </div>

                    <button type="submit" class="btn btn-gold min-w-56 flex-1" :disabled="!inStock"
                        :class="!inStock ? 'cursor-not-allowed opacity-50' : ''">
                        <x-ui-icon name="bag" :size="18" />
                        <span x-text="inStock ? 'Añadir a la bolsa' : 'Agotado'">Añadir a la bolsa</span>
                    </button>

                    <button type="button" class="grid h-[52px] w-[52px] place-items-center border border-line bg-ivory text-gold-deep transition hover:border-gold"
                        aria-label="Guardar en favoritos" title="Guardar en favoritos">
                        <x-ui-icon name="heart" :size="19" />
                    </button>
                </div>

                <p class="mt-3 text-xs" :class="inStock ? 'text-gold-deep' : 'text-muted'" x-text="stockLabel">
                    @if ($defaultVariant?->isInStock())
                        En stock · {{ $defaultVariant->stock }} unidades
                    @else
                        Agotado
                    @endif
                </p>

                @if (session('cart_status'))
                    <p class="mt-3 flex items-center gap-2 border border-gold/40 bg-ivory px-4 py-3 text-xs text-gold-deep">
                        <x-ui-icon name="check" :size="15" />
                        {{ session('cart_status') }}
                    </p>
                @endif

                @error('quantity')
                    <p class="mt-3 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </form>

            <div class="mt-7 flex gap-3 border border-line bg-surface px-5 py-4 text-xs leading-relaxed text-muted">
                <x-ui-icon name="truck" :size="20" class="mt-0.5 shrink-0 text-gold-deep" />
                <p>
                    Envío asegurado y gratuito desde {{ Money::format((int) config('store.free_shipping_threshold_cents')) }}.
                    Entrega en 3–5 días laborables con firma y embalaje discreto.
                </p>
            </div>

            <div class="mt-8 border-t border-line">
                <details class="border-b border-line py-4" open>
                    <summary class="flex cursor-pointer items-center justify-between text-xs font-semibold uppercase tracking-[0.16em]">
                        Detalles
                        <span class="text-gold-deep" aria-hidden="true">+</span>
                    </summary>
                    <div class="mt-4 grid gap-2 text-sm leading-relaxed text-muted">
                        <p>{{ $product->description }}</p>

                        <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-6 gap-y-1.5 text-xs">
                            <dt class="uppercase tracking-[0.12em]">Material</dt>
                            <dd>{{ $product->material }}</dd>
                            <dt class="uppercase tracking-[0.12em]">Colección</dt>
                            <dd>{{ $product->collection }}</dd>
                            <dt class="uppercase tracking-[0.12em]">Referencia</dt>
                            <dd>{{ $product->sku }}</dd>
                        </dl>
                    </div>
                </details>

                <details class="border-b border-line py-4">
                    <summary class="flex cursor-pointer items-center justify-between text-xs font-semibold uppercase tracking-[0.16em]">
                        Envío y devoluciones
                        <span class="text-gold-deep" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-4 text-sm leading-relaxed text-muted">
                        Envío asegurado y gratuito, con firma en la entrega y embalaje discreto.
                        Devolución gratuita durante 30 días desde la recepción del pedido.
                    </p>
                </details>

                <details id="guia-medidas" class="border-b border-line py-4">
                    <summary class="flex cursor-pointer items-center justify-between text-xs font-semibold uppercase tracking-[0.16em]">
                        Guía de medidas
                        <span class="text-gold-deep" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-4 text-sm leading-relaxed text-muted">
                        @if ($product->category?->slug === 'relojes')
                            El diámetro indica el ancho de la caja sin la corona. Si dudas entre dos medidas,
                            elige la mayor para una lectura más cómoda.
                        @else
                            Tallas disponibles: {{ $sizes->implode(' · ') }}. Si dudas entre dos tallas,
                            consulta las medidas de la prenda en la descripción o escríbenos.
                        @endif
                    </p>
                </details>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-2 text-[10px] uppercase tracking-[0.14em] text-muted">
                <span class="border border-line bg-ivory px-2.5 py-1.5">Visa</span>
                <span class="border border-line bg-ivory px-2.5 py-1.5">Mastercard</span>
                <span class="border border-line bg-ivory px-2.5 py-1.5">Apple Pay</span>
                <span class="border border-line bg-ivory px-2.5 py-1.5">PayPal</span>
                <span class="inline-flex items-center gap-1.5 pl-1">
                    <x-ui-icon name="lock" :size="13" /> Pago seguro
                </span>
            </div>
        </section>
    </div>

    {{-- Recomendados --}}
    @if ($related->isNotEmpty())
        <section class="container-store py-20">
            <div class="flex items-end justify-between gap-6">
                <div>
                    <p class="eyebrow">Completa el look</p>
                    <h2 class="mt-2 font-display text-3xl sm:text-4xl">También te puede gustar</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="link-arrow hidden text-xs sm:inline-flex">Ver todo</a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 lg:grid-cols-4">
                @foreach ($related as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
