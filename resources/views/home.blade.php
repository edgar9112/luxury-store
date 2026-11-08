<x-layouts.app title="Luxury Store — Relojes, ropa y accesorios de lujo">
    {{-- Portada --}}
    <section class="container-store grid items-center gap-12 py-14 lg:grid-cols-2 lg:gap-16 lg:py-24">
        <div>
            <p class="eyebrow">Colección privada · Otoño 2026</p>

            <h1 class="mt-5 font-display text-5xl leading-[0.95] sm:text-6xl lg:text-7xl">
                El lujo<br>de lo esencial.
            </h1>

            <p class="mt-6 max-w-md text-base leading-relaxed text-muted">
                Calzado y ropa de edición limitada, hechos con materiales nobles y un acabado que no pasa de moda.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-6">
                <a href="{{ route('catalog.index') }}" class="btn btn-gold">
                    Descubrir la colección
                    <x-ui-icon name="arrow" :size="17" />
                </a>
                <a href="#destacados" class="link-arrow">Ver el lookbook</a>
            </div>

            <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-xs text-muted">
                <li class="inline-flex items-center gap-2">
                    <x-ui-icon name="truck" :size="15" /> Envío asegurado y gratuito
                </li>
                <li class="inline-flex items-center gap-2">
                    <x-ui-icon name="return" :size="15" /> 30 días de devolución
                </li>
                <li class="inline-flex items-center gap-2">
                    <x-ui-icon name="lock" :size="15" /> Pago cifrado
                </li>
            </ul>
        </div>

        <div class="media tone-charcoal relative aspect-[4/5] w-full">
            <svg class="absolute inset-0 h-full w-full p-14 text-ink/80 sm:p-20" viewBox="0 0 200 200" aria-hidden="true">
                <use href="#watch"></use>
            </svg>
            <span class="media-tag">Alta relojería · Edición numerada</span>
        </div>
    </section>

    {{-- Categorías --}}
    <section class="container-store py-10" aria-label="Categorías">
        <div class="flex items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Explora</p>
                <h2 class="mt-2 font-display text-3xl sm:text-4xl">Por categoría</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="link-arrow hidden text-xs sm:inline-flex">Ver todo</a>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-5 lg:grid-cols-4">
            @foreach ($categories as $category)
                <x-category-card :category="$category" />
            @endforeach
        </div>
    </section>

    {{-- Destacados --}}
    @if ($featured->isNotEmpty())
        <section id="destacados" class="container-store py-20">
            <div class="flex items-end justify-between gap-6">
                <div>
                    <p class="eyebrow">Selección</p>
                    <h2 class="mt-2 font-display text-3xl sm:text-4xl">Piezas destacadas</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="link-arrow hidden text-xs sm:inline-flex">Ver el catálogo</a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Manifiesto --}}
    <section class="container-store py-10">
        <div class="border border-line bg-surface px-8 py-14 text-center sm:px-16">
            <p class="eyebrow">La maison</p>
            <blockquote class="mx-auto mt-5 max-w-3xl font-display text-2xl leading-snug sm:text-3xl">
                “No hacemos temporadas. Hacemos piezas que se heredan.”
            </blockquote>
            <p class="mt-5 text-xs uppercase tracking-[0.24em] text-gold-deep">Diseñado en Madrid · Hecho en Europa</p>
        </div>
    </section>
</x-layouts.app>
