import './bootstrap';

/**
 * Estado de la ficha de producto: variante, color, talla y cantidad.
 * Se inicializa desde Blade con `x-data="productOptions({...})"`.
 */
window.productOptions = function (config) {
    return {
        variants: config.variants,
        selected: config.selected,
        quantity: 1,

        get current() {
            return this.variants.find((variant) => variant.id === this.selected) ?? null;
        },
        get price() {
            return this.current ? this.current.price : config.basePrice;
        },
        get formattedPrice() {
            return this.current ? this.current.formatted_price : config.baseFormatted;
        },
        get stock() {
            return this.current ? this.current.stock : 0;
        },
        get color() {
            return this.current ? this.current.color : null;
        },
        get size() {
            return this.current ? this.current.size : null;
        },
        get inStock() {
            return this.stock > 0;
        },
        get stockLabel() {
            if (!this.inStock) {
                return 'Agotado';
            }

            return this.stock === 1 ? 'Última unidad disponible' : `En stock · ${this.stock} unidades`;
        },

        selectColor(color) {
            const match =
                this.variants.find((variant) => variant.color === color && variant.stock > 0) ??
                this.variants.find((variant) => variant.color === color);

            if (match) {
                this.selected = match.id;
                this.quantity = 1;
            }
        },

        selectSize(size) {
            const color = this.color;
            const sameColor = (variant) => !color || variant.color === color;

            const match =
                this.variants.find((variant) => variant.size === size && sameColor(variant) && variant.stock > 0) ??
                this.variants.find((variant) => variant.size === size && sameColor(variant));

            if (match) {
                this.selected = match.id;
                this.quantity = 1;
            }
        },

        hasColor(color) {
            return this.variants.some((variant) => variant.color === color && variant.stock > 0);
        },

        hasSize(size) {
            const color = this.color;

            return this.variants.some(
                (variant) => variant.size === size && (!color || variant.color === color) && variant.stock > 0,
            );
        },

        increase() {
            if (this.quantity < this.stock) {
                this.quantity += 1;
            }
        },

        decrease() {
            if (this.quantity > 1) {
                this.quantity -= 1;
            }
        },
    };
};

