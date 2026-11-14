<x-layouts.app title="Catálogo — Luxury Store"
    description="Catálogo de relojes, ropa, calzado y accesorios de lujo de edición limitada.">
    @php
        $query = request()->query();
        $activeFilters = [];

        if ($filters['categoria'] !== '') {
            $category = $categories->firstWhere('slug', $filters['categoria']);
            $activeFilters[] = [
                'label' => 'Categoría: '.($category?->name ?? $filters['categoria']),
                'url' => route('catalog.index', array_filter(array_merge($query, ['categoria' => null, 'page' => null]))),
            ];
        }

        if ($filters['talla'] !== '') {
            $activeFilters[] = [
                'label' => 'Talla: '.$filters['talla'],
                'url' => route('catalog.index', array_filter(array_merge($query, ['talla' => null, 'page' => null]))),
            ];
        }

        if ($filters['color'] !== '') {
            $activeFilters[] = [
                'label' => 'Color: '.$filters['color'],
                'url' => route('catalog.index', array_filter(array_merge($query, ['color' => null, 'page' => null]))),
            ];
        }

        if ($filters['precio_min'] !== null || $filters['precio_max'] !== null) {
            $min = $filters['precio_min'] !== null ? '$'.number_format($filters['precio_min']) : '0';
            $max = $filters['precio_max'] !== null ? '$'.number_format($filters['precio_max']) : '∞';

            $activeFilters[] = [
                'label' => 'Precio: '.$min.' – '.$max,
                'url' => route('catalog.index', array_filter(array_merge($query, ['precio_min' => null, 'precio_max' => null, 'page' => null]))),
            ];
        }
    @endphp
    <section class="container-store py-14 lg:py-20">
        {{-- Encabezado --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">Colección Otoño 2026</p>
                <h1 class="font-display mt-3 text-4xl sm:text-5xl">Catálogo</h1>
            </div>
            <p class="text-muted text-xs uppercase tracking-[0.18em]">
                {{ $products->total() }} {{ $products->total() === 1 ? 'pieza' : 'piezas' }}
            </p>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-[250px_1fr] lg:gap-14">
            {{-- Filtros --}}
            <aside>
                <details open class="border-line bg-surface border p-6">
                    <summary
                        class="text-gold-deep flex cursor-pointer items-center justify-between text-xs font-semibold uppercase tracking-[0.2em]">
                        Filtros
                        <span aria-hidden="true" class="text-lg leading-none">+</span>
                    </summary>

                    <form method="GET" action="{{ route('catalog.index') }}" class="mt-6 grid gap-7">
                        {{-- Categoría --}}
                        <fieldset>
                            <legend class="text-muted mb-3 text-[11px] font-semibold uppercase tracking-[0.18em]">
                                Categoría
                            </legend>
                            <div class="grid gap-2 text-sm">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="categoria" value="" @checked($filters['categoria'] === '')
                                        class="accent-gold">
                                    Todas
                                </label>

                                @foreach ($categories as $category)
                                    <label class="flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-2">
                                            <input type="radio" name="categoria" value="{{ $category->slug }}"
                                                @checked($filters['categoria'] === $category->slug) class="accent-gold">
                                            {{ $category->name }}
                                        </span>
                                        <span class="text-muted text-xs">{{ $category->products_count }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- Talla --}}
                        @if ($sizes->isNotEmpty())
                            <fieldset>
                                <legend class="text-muted mb-3 text-[11px] font-semibold uppercase tracking-[0.18em]">
                                    Talla / medida
                                </legend>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($sizes->prepend('') as $size)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="talla" value="{{ $size }}"
                                                @checked($filters['talla'] === $size) class="peer sr-only">
                                            <span
                                                class="border-line text-muted hover:border-gold peer-checked:border-gold peer-checked:bg-gold/10 peer-checked:text-gold-deep block border px-3 py-1.5 text-xs uppercase tracking-[0.12em] transition">
                                                {{ $size === '' ? 'Todas' : $size }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        {{-- Color --}}
                        @if ($colors->isNotEmpty())
                            <fieldset>
                                <legend class="text-muted mb-3 text-[11px] font-semibold uppercase tracking-[0.18em]">
                                    Color
                                </legend>
                                <div class="grid gap-2">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="" @checked($filters['color'] === '')
                                            class="peer sr-only">
                                        <span
                                            class="border-line text-muted hover:border-gold peer-checked:border-gold peer-checked:text-gold-deep flex items-center gap-2 border px-3 py-1.5 text-xs transition">
                                            Todos
                                        </span>
                                    </label>

                                    @foreach ($colors as $color)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="color" value="{{ $color->color }}"
                                                @checked($filters['color'] === $color->color) class="peer sr-only">
                                            <span
                                                class="border-line text-muted hover:border-gold peer-checked:border-gold peer-checked:text-gold-deep flex items-center gap-2 border px-3 py-1.5 text-xs transition">
                                                <span class="border-line h-3 w-3 rounded-full border"
                                                    style="background: {{ $color->color_hex ?? '#cccccc' }}"></span>
                                                {{ $color->color }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif

                        {{-- Precio --}}
                        <fieldset>
                            <legend class="text-muted mb-3 text-[11px] font-semibold uppercase tracking-[0.18em]">
                                Precio (USD)
                            </legend>
                            <div class="flex items-center gap-2">
                                <input type="number" name="precio_min" min="0" step="1"
                                    value="{{ $filters['precio_min'] }}" placeholder="Mín."
                                    class="border-line bg-ivory focus:border-gold h-10 w-full border px-3 text-sm focus:outline-none">
                                <span class="text-muted">—</span>
                                <input type="number" name="precio_max" min="0" step="1"
                                    value="{{ $filters['precio_max'] }}" placeholder="Máx."
                                    class="border-line bg-ivory focus:border-gold h-10 w-full border px-3 text-sm focus:outline-none">
                            </div>
                        </fieldset>

                        {{-- Orden --}}
                        <fieldset>
                            <legend class="text-muted mb-3 text-[11px] font-semibold uppercase tracking-[0.18em]">
                                Ordenar por
                            </legend>
                            <select name="orden"
                                class="border-line bg-ivory focus:border-gold h-10 w-full border px-3 text-sm focus:outline-none">
                                <option value="destacados" @selected($filters['orden'] === 'destacados')>Destacados</option>
                                <option value="precio-asc" @selected($filters['orden'] === 'precio-asc')>Precio: menor a mayor</option>
                                <option value="precio-desc" @selected($filters['orden'] === 'precio-desc')>Precio: mayor a menor</option>
                                <option value="novedades" @selected($filters['orden'] === 'novedades')>Novedades</option>
                            </select>
                        </fieldset>

                        <div class="grid gap-3">
                            <button type="submit" class="btn btn-gold w-full">Aplicar filtros</button>
                            <a href="{{ route('catalog.index') }}" class="link-arrow justify-center text-xs">
                                Limpiar filtros
                            </a>
                        </div>
                    </form>
                </details>
            </aside>

            {{-- Resultados --}}
            <div>
                @if (count($activeFilters) > 0)
                    <div class="mb-6 flex flex-wrap items-center gap-2">
                        @foreach ($activeFilters as $chip)
                            <a href="{{ $chip['url'] }}"
                                class="border-line bg-ivory text-muted hover:border-gold hover:text-gold-deep inline-flex items-center gap-2 border px-3 py-1.5 text-xs transition">
                                {{ $chip['label'] }}
                                <span aria-hidden="true">×</span>
                            </a>
                        @endforeach

                        <a href="{{ route('catalog.index') }}"
                            class="text-gold-deep ml-1 text-xs underline underline-offset-4">
                            Limpiar todo
                        </a>
                    </div>
                @endif

                @if ($products->isEmpty())
                    <div class="border-line bg-surface border px-8 py-16 text-center">
                        <p class="font-display text-2xl">Sin resultados</p>
                        <p class="text-muted mt-3 text-sm">No hay piezas con esos filtros. Prueba a ampliar la búsqueda.
                        </p>
                        <a href="{{ route('catalog.index') }}" class="btn btn-outline mt-6">Limpiar filtros</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-x-5 gap-y-10 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
