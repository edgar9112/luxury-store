# 04 — Catálogo, ficha de producto y bolsa

Este capítulo explica cómo recorrer la tienda: el catálogo con filtros, la ficha de cada pieza y la bolsa de compra.

> Todos los importes se muestran en **dólares estadounidenses (USD)** con el formato `$1,234.56`.

## 1. Qué incluye esta versión

| Zona | Estado |
|---|---|
| Catálogo con filtros y orden | ✅ Disponible |
| Ficha de producto con tallas y acabados | ✅ Disponible |
| Bolsa de compra (añadir, cambiar cantidad, quitar) | ✅ Disponible |
| Pago y checkout con Stripe | ⏳ Pendiente (capítulo 05) |
| Área de cliente y pedidos | ⏳ Pendiente (capítulo 06) |

## 2. El catálogo

Se accede desde el menú principal o desde `https://tu-dominio.com/catalogo`.

- Solo aparecen los productos **activos**.
- Se muestran **12 piezas por página**.
- Cada tarjeta enlaza a la ficha completa del producto.

### Filtros disponibles

| Filtro | Cómo funciona |
|---|---|
| **Categoría** | Relojes, ropa, calzado o accesorios. Muestra cuántas piezas hay en cada una. |
| **Talla / medida** | Solo aparecen tallas o medidas con stock disponible. |
| **Color** | Solo aparecen colores con stock disponible. |
| **Precio (USD)** | Rango mínimo y máximo en dólares. Si el rango se escribe al revés, la tienda lo corrige sola. |
| **Ordenar por** | Destacados, precio de menor a mayor, precio de mayor a menor y novedades. |

Los filtros se combinan entre sí y se aplican con el botón **Aplicar filtros**.

### Filtros activos

Cuando hay filtros aplicados, encima de los resultados aparecen **etiquetas** con lo que está activo:

- Cada etiqueta se puede quitar con un clic para eliminar solo ese filtro.
- **Limpiar todo** elimina todos los filtros de una vez.
- Si no hay resultados, se muestra un aviso con un botón para limpiar los filtros.

### Orden por defecto

Si no se elige un orden, se usa **Destacados** (el orden que se define en el panel de administración). Una dirección de orden desconocida nunca rompe la página: vuelve al orden por defecto.

## 3. Ficha de producto

Cada pieza tiene su propia página con una dirección permanente, por ejemplo `/producto/reloj-heritage`.

### Qué se muestra

- Galería del producto con vistas del acabado.
- Nombre, categoría y colección.
- Precio en USD y, si hay oferta, precio anterior tachado y porcentaje de descuento.
- Descripción breve, material, colección y referencia (SKU).
- Acabados y tallas o diámetros **disponibles**.
- Disponibilidad en tiempo real: `En stock · N unidades`, `Última unidad disponible` o `Agotado`.

### Elegir talla y acabado

1. Elige un **acabado** (color).
2. Elige una **talla** o **diámetro**.
3. La tienda selecciona automáticamente una combinación que tenga stock.
4. Ajusta la **cantidad** con los botones `+` y `−` (máximo 10 unidades o el stock real).

Las combinaciones agotadas aparecen tachadas y desactivadas; no se pueden añadir.

### Añadir a la bolsa

1. Pulsa **Añadir a la bolsa**.
2. Aparece un aviso de confirmación en la misma ficha.
3. El número de la cabecera se actualiza al instante.

> ⚠️ Si la pieza se agota mientras la tienes en la bolsa, la tienda lo avisa y la retira al actualizar la cantidad.

### Secciones desplegables

- **Detalles:** descripción, material, colección y referencia.
- **Envío y devoluciones:** envío asegurado y devolución gratuita durante 30 días.
- **Guía de medidas:** consejos según se trate de relojes o de prendas.

Al final de la ficha se muestran **recomendados**: primero las piezas de la misma categoría.

## 4. La bolsa de compra

Se accede con el icono de la bolsa en la cabecera o desde `/bolsa`.

### Dentro de la bolsa

| Acción | Cómo |
|---|---|
| Ver el detalle | Muestra cada pieza, su talla/acabado, referencia y subtotal. |
| Cambiar la cantidad | Escribe la cantidad y pulsa **Actualizar**. Se limita al stock real. |
| Quitar una pieza | Pulsa **Quitar**. |
| Seguir comprando | Enlace **Seguir comprando** hacia el catálogo. |

### Resumen del pedido

| Concepto | Regla |
|---|---|
| **Subtotal** | Suma de las piezas de la bolsa. |
| **Envío** | **Gratis** desde `$200.00`; por debajo se aplica la tarifa de envío configurada. |
| **Total** | Subtotal + envío. Los impuestos se calculan en el checkout. |

La bolsa avisa de cuánto falta para conseguir el **envío gratuito**.

> El botón **Finalizar compra** está desactivado en esta versión. El pago con Stripe se conecta en el capítulo 05.

### Bolsa vacía

Si no hay piezas, se muestra un mensaje con un botón para ir al catálogo.

## 5. Contador de la bolsa

El número sobre el icono de la bolsa en la cabecera refleja la **suma de cantidades** de la bolsa actual:

- **Visitantes:** se guarda en la sesión del navegador.
- **Usuarios con sesión iniciada:** se guarda en su cuenta.

## 6. Comprobaciones rápidas

| Comprobación | Resultado esperado |
|---|---|
| Abrir `/catalogo` | Se ven las piezas activas y el contador de piezas. |
| Filtrar por categoría, talla, color y precio | Solo aparecen piezas que cumplen todos los filtros. |
| Ordenar por precio | El orden cambia de menor a mayor y viceversa. |
| Abrir una ficha | Se ven precio en USD, variantes y stock. |
| Añadir una pieza | El contador de la cabecera sube y aparece el aviso. |
| Cambiar la cantidad en la bolsa | El subtotal y el total se recalculan. |
| Quitar la última pieza | La bolsa muestra su estado vacío. |

## 7. Notas técnicas

- El catálogo, la ficha y la bolsa usan el mismo diseño base (Blade + Tailwind 4).
- Las variantes de producto son la unidad que se compra: cada talla/acabado tiene su propio stock y precio.
- Las imágenes actuales son **ilustraciones vectoriales** por categoría; se sustituirán por fotografías cuando existan archivos subidos.
- Los filtros son enlaces `GET`: se pueden compartir y conservar en el historial del navegador.
