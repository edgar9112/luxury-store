@props(['name', 'size' => 20])

<svg {{ $attributes->merge(['width' => $size, 'height' => $size, 'aria-hidden' => 'true']) }}>
    <use href="#i-{{ $name }}"></use>
</svg>
