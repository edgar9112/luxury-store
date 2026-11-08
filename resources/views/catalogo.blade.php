<x-layouts.app title="Catálogo — Luxury Store"
    description="Catálogo de relojes, ropa, calzado y accesorios de lujo de edición limitada.">
    <section class="container-store py-16">
        <p class="eyebrow">Colección Otoño 2026</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl">Catálogo</h1>

        <p class="mt-5 max-w-xl leading-relaxed text-muted">
            Aquí irá el catálogo completo con filtros por categoría, talla, color y precio.
            Lo construimos en el siguiente paso.
        </p>

        <div class="mt-10 flex flex-wrap gap-3">
            @foreach ($navCategories as $category)
                <a href="{{ route('catalog.index', ['categoria' => $category->slug]) }}"
                    class="border border-line bg-ivory px-5 py-2 text-xs uppercase tracking-[0.16em] text-muted transition hover:border-gold hover:text-gold-deep">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <a href="{{ route('home') }}" class="link-arrow mt-12 inline-flex">Volver al inicio</a>
    </section>
</x-layouts.app>
