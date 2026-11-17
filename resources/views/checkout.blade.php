@use('App\Support\Money')

@php
    $country = strtoupper((string) config('store.country', 'US'));
@endphp

<x-layouts.app title="Finalizar compra — Luxury Store" description="Introduce tus datos de envío y finaliza tu pedido.">
    <nav class="container-store flex flex-wrap items-center gap-2 py-6 text-[11px] uppercase tracking-[0.16em] text-muted"
        aria-label="Migas de pan">
        <a href="{{ route('home') }}" class="transition hover:text-gold-deep">Inicio</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('cart.index') }}" class="transition hover:text-gold-deep">Bolsa</a>
        <span class="opacity-40">/</span>
        <span class="text-ink">Checkout</span>
    </nav>

    <section class="container-store grid gap-10 pb-20 lg:grid-cols-[1.5fr_0.9fr] lg:gap-14">
        <div>
            <p class="eyebrow"> Último paso</p>
            <h1 class="font-display mt-3 text-4xl sm:text-5xl">Finalizar compra</h1>
            <p class="text-muted mt-4 max-w-xl text-sm leading-relaxed">
                Revisa tus datos de envío. El pago se procesa de forma segura con Stripe;
                en local se usa el modo demostración y no se realiza ningún cobro.
            </p>

            @error('payment')
                <p class="mt-6 border border-red-700/30 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
            @enderror

            <form action="{{ route('checkout.store') }}" method="POST" class="mt-10 grid gap-8">
                @csrf

                {{-- Contacto --}}
                <fieldset>
                    <legend class="text-gold-deep text-xs font-semibold uppercase tracking-[0.2em]">1 · Contacto</legend>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Nombre y apellidos
                            </label>
                            <input id="customer_name" name="customer_name" type="text" required autocomplete="name"
                                value="{{ old('customer_name', $user?->name) }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                            @error('customer_name')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_email" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Correo electrónico
                            </label>
                            <input id="customer_email" name="customer_email" type="email" required autocomplete="email"
                                value="{{ old('customer_email', $user?->email) }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                            @error('customer_email')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Teléfono (opcional)
                            </label>
                            <input id="customer_phone" name="customer_phone" type="tel" autocomplete="tel"
                                value="{{ old('customer_phone') }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                            @error('customer_phone')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                {{-- Envío --}}
                <fieldset class="border-line border-t pt-8">
                    <legend class="text-gold-deep text-xs font-semibold uppercase tracking-[0.2em]">2 · Dirección de envío</legend>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="shipping_line1" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Dirección
                            </label>
                            <input id="shipping_line1" name="shipping_line1" type="text" required autocomplete="address-line1"
                                value="{{ old('shipping_line1') }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                            @error('shipping_line1')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="shipping_line2" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Piso, puerta o referencia (opcional)
                            </label>
                            <input id="shipping_line2" name="shipping_line2" type="text" autocomplete="address-line2"
                                value="{{ old('shipping_line2') }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                        </div>

                        <div>
                            <label for="shipping_city" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Ciudad
                            </label>
                            <input id="shipping_city" name="shipping_city" type="text" required autocomplete="address-level2"
                                value="{{ old('shipping_city') }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                            @error('shipping_city')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_province" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Estado o provincia
                            </label>
                            <input id="shipping_province" name="shipping_province" type="text" autocomplete="address-level1"
                                value="{{ old('shipping_province') }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                        </div>

                        <div>
                            <label for="shipping_postal_code" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Código postal
                            </label>
                            <input id="shipping_postal_code" name="shipping_postal_code" type="text" required autocomplete="postal-code"
                                value="{{ old('shipping_postal_code') }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm focus:outline-none">
                            @error('shipping_postal_code')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_country" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                País (ISO)
                            </label>
                            <input id="shipping_country" name="shipping_country" type="text" required maxlength="2" autocomplete="country"
                                value="{{ old('shipping_country', $country) }}"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm uppercase focus:outline-none">
                            @error('shipping_country')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                {{-- Extras --}}
                <fieldset class="border-line border-t pt-8">
                    <legend class="text-gold-deep text-xs font-semibold uppercase tracking-[0.2em]">3 · Opcional</legend>

                    <div class="mt-5 grid gap-5">
                        <div>
                            <label for="coupon_code" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Código de descuento
                            </label>
                            <input id="coupon_code" name="coupon_code" type="text" maxlength="40"
                                value="{{ old('coupon_code') }}" placeholder="Ej. BIENVENIDA10"
                                class="border-line bg-ivory focus:border-gold h-12 w-full border px-4 text-sm uppercase focus:outline-none">
                            @error('coupon_code')
                                <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notes" class="text-muted mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em]">
                                Notas del pedido
                            </label>
                            <textarea id="notes" name="notes" rows="3" maxlength="500"
                                class="border-line bg-ivory focus:border-gold w-full border px-4 py-3 text-sm focus:outline-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="border-line flex flex-wrap items-center justify-between gap-4 border-t pt-8">
                    <a href="{{ route('cart.index') }}" class="link-arrow text-xs">Volver a la bolsa</a>
                    <button type="submit" class="btn btn-gold min-w-56">
                        <x-ui-icon name="lock" :size="17" />
                        Pagar {{ Money::format($totals['total']) }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Resumen --}}
        <aside aria-label="Resumen del pedido">
            <div class="border-line bg-surface sticky top-24 border p-6 sm:p-8">
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em]">Tu pedido</h2>

                <ul class="border-line mt-6 grid gap-4 border-b pb-6">
                    @foreach ($items as $item)
                        <li class="flex gap-3">
                            <div class="media {{ $item->variant?->product?->toneClass() ?? 'tone-clay' }} grid h-14 w-12 shrink-0 place-items-center">
                                <svg class="h-1/2 w-1/2 text-ink/70" viewBox="0 0 200 200" aria-hidden="true">
                                    <use href="#{{ $item->variant?->product?->artName() ?? 'bag' }}"></use>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1 text-xs">
                                <p class="truncate font-medium">{{ $item->variant?->product?->name ?? 'Producto' }}</p>
                                <p class="text-muted mt-0.5">{{ $item->variant?->name() }} · ×{{ $item->quantity }}</p>
                            </div>
                            <p class="text-xs">{{ Money::format($item->subtotalCents()) }}</p>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-6 grid gap-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted">Subtotal</dt>
                        <dd>{{ Money::format($totals['subtotal']) }}</dd>
                    </div>
                    @if ($totals['discount'] > 0)
                        <div class="flex items-center justify-between gap-4 text-gold-deep">
                            <dt>Descuento</dt>
                            <dd>−{{ Money::format($totals['discount']) }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted">Envío asegurado</dt>
                        <dd>{{ $totals['shipping'] === 0 ? 'Gratis' : Money::format($totals['shipping']) }}</dd>
                    </div>
                    <div class="border-line mt-2 flex items-baseline justify-between gap-4 border-t pt-4">
                        <dt class="text-xs font-semibold uppercase tracking-[0.16em]">Total</dt>
                        <dd class="font-display text-2xl">{{ Money::format($totals['total']) }}</dd>
                    </div>
                </dl>

                <p class="text-muted mt-4 text-[11px] leading-relaxed">
                    Los impuestos se calculan en Stripe antes de confirmar el pago.
                </p>
            </div>
        </aside>
    </section>
</x-layouts.app>
