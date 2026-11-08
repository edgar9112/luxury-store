@props(['href' => null])

<a href="{{ $href ?? route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 text-gold']) }}
    aria-label="Luxury Store, inicio">
    <svg class="h-[38px] w-[38px] shrink-0" viewBox="0 0 52 52" aria-hidden="true">
        <use href="#logo-mark"></use>
    </svg>
    <span class="grid gap-[5px] leading-none uppercase">
        <b class="font-display text-[16px] font-normal tracking-[0.3em] text-ink">Luxury</b>
        <small class="font-sans text-[8px] font-medium tracking-[0.56em] text-gold-deep">Store</small>
    </span>
</a>
