@use('App\Support\Money')

@php
    $itemCount = (int) $items->sum('quantity');
    $freeThreshold = (int) config('store.free_shipping_threshold_cents');
    $remainingForFreeShipping = $freeThreshold > 0 ? max(0, $freeThreshold - $subtotal) : 0;
@endphp

<x-layouts.app title="Tu bolsa — Luxury Store" description="Revisa las piezas de tu bolsa antes de finalizar la compra.">
    <nav class="container-store flex flex-wrap items-center gap-2 py-6 text-[11px] uppercase tracking-[0.16em] text-muted"
        aria-label="Migas de pan">
        <a href="{{ route('home') }}" class="transition hover:text-gold-deep">Inicio</a>
        <span class="opacity-40">/</span>
        <span class="text-ink">Bolsa</span>
    </nav>

    <section class="container-store pb-20">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Bolsa de compra</p>
                <h1 class="font-display mt-3 text-4xl sm:text-5xl">Tu bolsa</h1>
            </div>
            <p class="text-muted text-xs uppercase tracking-[0.18em]">
                {{ $itemCount }} {{ $itemCount === 1 ? 'pieza' : 'piezas' }}
            </p>
        </div>

        @if (session('cart_status'))
            <p class="mt-6 flex items-center gap-2 border border-gold/40 bg-ivory px-4 py-3 text-xs text-gold-deep">
                <x-ui-icon name="check" :size="15" />
                {{ session('cart_status') }}
            </p>
        @endif

        @if ($items->isEmpty())
            <div class="border-line bg-surface mt-10 border px-8 py-16 text-center">
                <p class="font-display text-2xl">Tu bolsa está vacía</p>
                <p class="text-muted mx-auto mt-3 max-w-md text-sm leading-relaxed">
                    Descubre relojes, ropa, calzado y accesorios de edición limitada. Cada pieza se envía
                    asegurada y con devolución gratuita durante 30 días.
                </p>
                <a href="{{ route('catalog.index') }}" class="btn btn-gold mt-8">Ver el catálogo</a>
            </div>
        @else
            <div class="mt-10 grid gap-10 lg:grid-cols-[1.6fr_0.9fr] lg:gap-14">
                {{-- Piezas --}}
                <div>
                    <ul class="border-line divide-line divide-y border-y">
                        @foreach ($items as $item)
                            @php
                                $variant = $item->variant;
                                $product = $variant?->product;
                            @endphp

                            @if ($product)
                                @php
                                    $maxQuantity = max(1, min(10, (int) $variant->stock));
                                @endphp

                                <li class="flex gap-4 py-6 sm:gap-6">
                                    <a href="{{ route('product.show', $product) }}"
                                        class="media {{ $product->toneClass() }} grid h-28 w-24 shrink-0 place-items-center sm:h-32 sm:w-28"
                                        aria-label="{{ $product->name }}">
                                        <svg class="h-1/2 w-1/2 text-ink/70" viewBox="0 0 200 200" aria-hidden="true">
                                            <use href="#{{ $product->artName() }}"></use>
                                        </svg>
                                    </a>

                                    <div class="flex min-w-0 flex-1 flex-col justify-between gap-4 sm:flex-row">
                                        <div class="min-w-0">
                                            <a href="{{ route('product.show', $product) }}"
                                                class="font-display block text-lg leading-snug transition hover:text-gold-deep">
                                                {{ $product->name }}
                                            </a>
                                            <p class="text-muted mt-1.5 text-[11px] uppercase tracking-[0.14em]">
                                                {{ $variant->name() }}
                                                <span class="mx-1 opacity-40">·</span>
                                                Ref. {{ $variant->sku }}
                                            </p>

                                            @if ($variant->stock <= 3)
                                                <p class="mt-2 text-[11px] text-gold-deep">
                                                    Quedan {{ $variant->stock }} unidades
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-end justify-between gap-4 sm:flex-col sm:items-end">
                                            <p class="font-display text-xl">{{ Money::format($item->subtotalCents()) }}</p>

                                            <div class="flex items-center gap-4">
                                                <form action="{{ route('cart.update', $item) }}" method="POST"
                                                    class="border-line flex items-center border">
                                                    @csrf
                                                    @method('PATCH')

                                                    <label for="quantity-{{ $item->id }}" class="sr-only">
                                                        Cantidad de {{ $product->name }}
                                                    </label>
                                                    <input id="quantity-{{ $item->id }}" type="number" name="quantity"
                                                        min="1" max="{{ $maxQuantity }}" value="{{ $item->quantity }}"
                                                        class="text-ink h-10 w-16 border-0 bg-transparent px-3 text-center text-sm focus:outline-none">

                                                    <button type="submit"
                                                        class="text-gold-deep h-10 border-l border-line px-3 text-[10px] uppercase tracking-[0.14em] transition hover:bg-gold/10">
                                                        Actualizar
                                                    </button>
                                                </form>

                                                <form action="{{ route('cart.destroy', $item) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-muted text-xs underline underline-offset-4 transition hover:text-gold-deep">
                                                        Quitar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
                        <a href="{{ route('catalog.index') }}" class="link-arrow text-xs">Seguir comprando</a>
                        <p class="text-muted text-xs">Impuestos y envío se confirman en el checkout.</p>
                    </div>
                </div>

                {{-- Resumen --}}
                <aside aria-label="Resumen de la bolsa">
                    <div class="border-line bg-surface border p-6 sm:p-8">
                        <h2 class="text-xs font-semibold uppercase tracking-[0.2em]">Resumen</h2>

                        <dl class="border-line mt-6 grid gap-3 border-b pb-6 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Subtotal</dt>
                                <dd>{{ Money::format($subtotal) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Envío asegurado</dt>
                                <dd>
                                    @if ($shipping === 0)
                                        <span class="text-gold-deep">Gratis</span>
                                    @else
                                        {{ Money::format($shipping) }}
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex items-baseline justify-between gap-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]">Total</p>
                            <p class="font-display text-2xl">{{ Money::format($total) }}</p>
                        </div>
                        <p class="text-muted mt-1 text-[11px]">Impuestos calculados en el checkout.</p>

                        @if ($freeThreshold > 0)
                            <div class="border-line mt-6 border-t pt-4 text-xs leading-relaxed text-muted">
                                @if ($remainingForFreeShipping === 0)
                                    <p class="text-gold-deep">
                                        Tu pedido ya tiene envío asegurado y gratuito.
                                    </p>
                                @else
                                    <p>
                                        Te faltan
                                        <strong class="text-ink">{{ Money::format($remainingForFreeShipping) }}</strong>
                                        para el envío gratuito.
                                    </p>
                                @endif
                            </div>
                        @endif

                        <a href="{{ route('checkout.index') }}" class="btn btn-gold mt-6 w-full">
                            <x-ui-icon name="lock" :size="17" />
                            Finalizar compra
                        </a>
                        <p class="text-muted mt-3 text-center text-[11px]">
                            Pago seguro con Stripe en USD. En local se usa modo demostración.
                        </p>
                    </div>

                    <ul class="text-muted mt-6 grid gap-3 text-[11px] uppercase tracking-[0.14em]">
                        <li class="flex items-center gap-2">
                            <x-ui-icon name="truck" :size="16" class="text-gold-deep" /> Envío asegurado con firma
                        </li>
                        <li class="flex items-center gap-2">
                            <x-ui-icon name="return" :size="16" class="text-gold-deep" /> 30 días para devoluciones
                        </li>
                        <li class="flex items-center gap-2">
                            <x-ui-icon name="lock" :size="16" class="text-gold-deep" /> Pago cifrado
                        </li>
                    </ul>
                </aside>
            </div>
        @endif
    </section>
</x-layouts.app>
