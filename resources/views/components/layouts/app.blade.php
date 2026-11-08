@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description"
        content="{{ $description ?? 'Relojes, ropa, calzado y accesorios de lujo. Ediciones limitadas y materiales nobles.' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    @include('partials.icons')

    {{-- Barra de anuncio --}}
    <div class="border-b border-line bg-dark text-center text-[10.5px] font-medium uppercase tracking-[0.22em] text-[#6b5416]">
        <div class="container-store py-2.5">
            Envío asegurado y gratuito desde
            {{ \App\Support\Money::format((int) config('store.free_shipping_threshold_cents')) }}
            <span class="mx-2 opacity-60">·</span>
            Devoluciones en 30 días
        </div>
    </div>

    {{-- Cabecera --}}
    <header x-data="{ open: false }" @keydown.escape.window="open = false"
        class="sticky top-0 z-50 border-b border-line bg-cream/90 backdrop-blur-md">
        <div class="container-store flex h-[76px] items-center justify-between gap-6">
            <x-logo />

            <nav class="hidden items-center gap-8 text-sm lg:flex" aria-label="Principal">
                @foreach ($navCategories as $category)
                    <a href="{{ route('catalog.index', ['categoria' => $category->slug]) }}" class="nav-link">
                        {{ $category->name }}
                    </a>
                @endforeach
                <a href="{{ route('catalog.index') }}" class="nav-link">Maison</a>
            </nav>

            <div class="flex items-center gap-1">
                <a href="{{ route('catalog.index') }}"
                    class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-gold/10"
                    aria-label="Buscar">
                    <x-ui-icon name="search" />
                </a>
                <a href="#" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-gold/10"
                    aria-label="Mi cuenta">
                    <x-ui-icon name="user" />
                </a>
                <a href="#" class="relative grid h-10 w-10 place-items-center rounded-full transition hover:bg-gold/10"
                    aria-label="Bolsa de compra">
                    <x-ui-icon name="bag" />
                    <span
                        class="absolute right-0.5 top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-gold-deep px-1 text-[10px] font-semibold text-ivory">0</span>
                </a>
                <button type="button" class="grid h-10 w-10 place-items-center rounded-full transition hover:bg-gold/10 lg:hidden"
                    @click="open = !open" :aria-expanded="open.toString()" aria-label="Abrir menú">
                    <span x-show="!open"><x-ui-icon name="menu" :size="22" /></span>
                    <span x-show="open" x-cloak><x-ui-icon name="close" :size="22" /></span>
                </button>
            </div>
        </div>

        {{-- Menú móvil --}}
        <div x-show="open" x-cloak x-transition.opacity class="border-t border-line bg-cream lg:hidden">
            <nav class="container-store flex flex-col gap-4 py-6 text-sm uppercase tracking-[0.14em]" aria-label="Principal móvil">
                @foreach ($navCategories as $category)
                    <a href="{{ route('catalog.index', ['categoria' => $category->slug]) }}" class="py-1">
                        {{ $category->name }}
                    </a>
                @endforeach
                <a href="{{ route('catalog.index') }}" class="py-1">Maison</a>
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    {{-- Pie de página --}}
    <footer class="mt-24 border-t border-line bg-dark text-ink">
        <div class="container-store grid gap-10 py-16 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.5fr]">
            <div>
                <x-logo />
                <p class="mt-5 max-w-[28ch] text-sm leading-relaxed text-muted">
                    Relojes, ropa, calzado y accesorios de lujo. Diseñado en Madrid, hecho en Europa.
                </p>
            </div>

            <div>
                <h4 class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gold-deep">Tienda</h4>
                <ul class="mt-4 grid gap-2.5 text-sm">
                    @foreach ($navCategories as $category)
                        <li>
                            <a href="{{ route('catalog.index', ['categoria' => $category->slug]) }}"
                                class="text-[#5a4b33] transition hover:text-gold-deep">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gold-deep">Ayuda</h4>
                <ul class="mt-4 grid gap-2.5 text-sm text-[#5a4b33]">
                    <li><a href="#" class="transition hover:text-gold-deep">Envíos</a></li>
                    <li><a href="#" class="transition hover:text-gold-deep">Devoluciones</a></li>
                    <li><a href="#" class="transition hover:text-gold-deep">Guía de tallas</a></li>
                    <li><a href="#" class="transition hover:text-gold-deep">Contacto</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gold-deep">Newsletter</h4>
                <p class="mt-4 text-sm leading-relaxed text-muted">
                    Novedades, colecciones privadas y acceso anticipado. Sin ruido.
                </p>

                <form action="{{ route('newsletter.store') }}" method="POST" class="mt-4 flex">
                    @csrf
                    <label for="newsletter-email" class="sr-only">Correo electrónico</label>
                    <input id="newsletter-email" type="email" name="email" required placeholder="tu@email.com"
                        class="h-11 min-w-0 flex-1 border border-line bg-ivory px-3 text-sm placeholder:text-muted/60 focus:border-gold focus:outline-none">
                    <button type="submit" class="btn btn-gold h-11 px-5 text-[10px]">Suscribirme</button>
                </form>

                @error('email')
                    <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                @enderror

                @if (session('newsletter_status'))
                    <p class="mt-2 text-xs text-gold-deep">{{ session('newsletter_status') }}</p>
                @endif
            </div>
        </div>

        <div class="border-t border-line">
            <div class="container-store flex flex-col gap-3 py-6 text-xs text-muted sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Luxury Store. Todos los derechos reservados.</p>
                <nav class="flex gap-5" aria-label="Legal">
                    <a href="#" class="transition hover:text-gold-deep">Privacidad</a>
                    <a href="#" class="transition hover:text-gold-deep">Términos</a>
                    <a href="#" class="transition hover:text-gold-deep">Cookies</a>
                </nav>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>

</html>
