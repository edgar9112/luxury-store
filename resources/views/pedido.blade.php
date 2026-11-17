@use('App\Support\Money')

<x-layouts.app :title="'Pedido '.$order->number.' — Luxury Store'" description="Detalle y estado de tu pedido.">
    <nav class="container-store flex flex-wrap items-center gap-2 py-6 text-[11px] uppercase tracking-[0.16em] text-muted"
        aria-label="Migas de pan">
        <a href="{{ route('home') }}" class="transition hover:text-gold-deep">Inicio</a>
        <span class="opacity-40">/</span>
        <span class="text-ink">Pedido {{ $order->number }}</span>
    </nav>

    <section class="container-store pb-20">
        <div class="border-line bg-surface border px-6 py-10 text-center sm:px-10">
            <p class="eyebrow">Pedido {{ $order->number }}</p>

            @if ($order->payment_status->value === 'paid')
                <h1 class="font-display mt-3 text-4xl sm:text-5xl">Gracias por tu compra</h1>
                <p class="text-muted mx-auto mt-4 max-w-lg text-sm leading-relaxed">
                    Hemos recibido tu pedido y prepararemos cada pieza con el cuidado que merece.
                    Recibirás las novedades sobre el envío en {{ $order->customer_email }}.
                </p>
            @elseif ($order->status->value === 'cancelled')
                <h1 class="font-display mt-3 text-4xl sm:text-5xl">Pago cancelado</h1>
                <p class="text-muted mx-auto mt-4 max-w-lg text-sm leading-relaxed">
                    No se ha realizado ningún cargo. Las piezas siguen reservadas en tu bolsa
                    para que puedas intentarlo de nuevo.
                </p>
            @else
                <h1 class="font-display mt-3 text-4xl sm:text-5xl">Pago pendiente</h1>
                <p class="text-muted mx-auto mt-4 max-w-lg text-sm leading-relaxed">
                    Estamos esperando la confirmación del pago. Esta página se actualizará
                    automáticamente en cuanto Stripe nos avise.
                </p>
            @endif

            <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                <span class="border border-gold/40 bg-ivory px-4 py-2 text-[11px] uppercase tracking-[0.16em] text-gold-deep">
                    Estado: {{ $order->status->label() }}
                </span>
                <span class="border border-line bg-ivory px-4 py-2 text-[11px] uppercase tracking-[0.16em] text-muted">
                    Pago: {{ $order->payment_status->label() }}
                </span>
            </div>

            @if (session('order_status'))
                <p class="mx-auto mt-6 max-w-lg border border-gold/40 bg-ivory px-4 py-3 text-xs text-gold-deep">
                    {{ session('order_status') }}
                </p>
            @endif

            @if ($order->payment_method === 'demo')
                <p class="mx-auto mt-4 max-w-lg text-[11px] uppercase tracking-[0.14em] text-muted">
                    Modo demostración · sin cobro real
                </p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if ($order->status->value === 'cancelled')
                    <a href="{{ route('cart.index') }}" class="btn btn-gold">Volver a la bolsa</a>
                @endif
                <a href="{{ route('catalog.index') }}" class="btn btn-outline">Seguir explorando</a>
            </div>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-[1.4fr_0.9fr] lg:gap-14">
            {{-- Piezas --}}
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em]">Piezas del pedido</h2>

                <ul class="border-line divide-line mt-5 divide-y border-y">
                    @foreach ($order->items as $item)
                        <li class="flex gap-4 py-5">
                            <div class="media {{ $item->variant?->product?->toneClass() ?? 'tone-clay' }} grid h-20 w-16 shrink-0 place-items-center">
                                <svg class="h-1/2 w-1/2 text-ink/70" viewBox="0 0 200 200" aria-hidden="true">
                                    <use href="#{{ $item->variant?->product?->artName() ?? 'bag' }}"></use>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-display text-base">{{ $item->product_name }}</p>
                                <p class="text-muted mt-1 text-[11px] uppercase tracking-[0.14em]">
                                    {{ $item->variant_name ?: 'Pieza única' }} · Ref. {{ $item->sku }}
                                </p>
                                <p class="text-muted mt-1 text-xs">Cantidad: {{ $item->quantity }}</p>
                            </div>

                            <p class="text-sm">{{ $item->totalFormatted() }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Detalles --}}
            <aside class="grid gap-6">
                <div class="border-line bg-surface border p-6">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em]">Resumen</h2>

                    <dl class="mt-5 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">Subtotal</dt>
                            <dd>{{ Money::format($order->subtotal_cents, $order->currency) }}</dd>
                        </div>
                        @if ($order->discount_cents > 0)
                            <div class="flex justify-between gap-4 text-gold-deep">
                                <dt>Descuento
                                    @if ($order->coupon)
                                        ({{ $order->coupon->code }})
                                    @endif
                                </dt>
                                <dd>−{{ Money::format($order->discount_cents, $order->currency) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">Envío</dt>
                            <dd>{{ $order->shipping_cents === 0 ? 'Gratis' : Money::format($order->shipping_cents, $order->currency) }}</dd>
                        </div>
                        <div class="border-line mt-2 flex items-baseline justify-between gap-4 border-t pt-4">
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em]">Total</dt>
                            <dd class="font-display text-2xl">{{ $order->totalFormatted() }}</dd>
                        </div>
                    </dl>

                    @if ($order->paid_at)
                        <p class="text-muted mt-5 text-[11px]">
                            Pagado el {{ $order->paid_at->translatedFormat('d \d\e F \d\e Y, H:i') }}
                        </p>
                    @endif
                </div>

                <div class="border-line border p-6 text-xs leading-relaxed text-muted">
                    <h2 class="text-ink mb-3 text-xs font-semibold uppercase tracking-[0.2em]">Envío a</h2>
                    <p class="text-ink font-medium">{{ $order->customer_name }}</p>
                    <p class="mt-1">{{ $order->shippingAddressLine() }}</p>
                    @if ($order->customer_phone)
                        <p class="mt-1">Tel. {{ $order->customer_phone }}</p>
                    @endif
                    @if ($order->notes)
                        <p class="border-line mt-4 border-t pt-3">Notas: {{ $order->notes }}</p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
