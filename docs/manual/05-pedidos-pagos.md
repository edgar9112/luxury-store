# 05 — Pedidos y pagos

Este capítulo explica cómo se finaliza una compra, cómo se calcula el importe y cómo se confirma el pago con Stripe.

> Los importes se guardan en **céntimos** y se muestran en **USD** (`$1,234.56`). Stripe recibe el importe exacto, sin conversiones.

## 1. Qué incluye esta versión

| Funcionalidad | Estado |
|---|---|
| Formulario de checkout con datos de envío | ✅ Disponible |
| Cupones de descuento | ✅ Disponible |
| Pago alojado en Stripe Checkout | ✅ Disponible |
| Confirmación del pedido y estados | ✅ Disponible |
| Cancelación con devolución de stock | ✅ Disponible |
| Webhook firmado de Stripe | ✅ Disponible |
| Correos de confirmación | ⏳ Pendiente |
| Reembolsos y área de pedidos | ⏳ Pendiente |

## 2. Cómo finalizar una compra

1. Añade piezas a la bolsa desde el catálogo o la ficha del producto.
2. Pulsa **Finalizar compra** en `/bolsa` o abre `/checkout`.
3. Rellena tres bloques:
   - **Contacto:** nombre, correo y teléfono opcional.
   - **Dirección de envío:** dirección, ciudad, estado o provincia, código postal y país (código ISO de 2 letras, por defecto `US`).
   - **Opcional:** código de descuento y notas para el pedido.
4. Pulsa **Pagar <importe>**.

Si la bolsa está vacía, la tienda te devuelve a la bolsa con un aviso.

## 3. Importes del pedido

| Concepto | Regla |
|---|---|
| **Subtotal** | Suma de los precios de las variantes por cantidad. |
| **Descuento** | Se calcula sobre el subtotal si el cupón es válido. |
| **Envío** | Gratis desde `$200.00` de subtotal; si no, la tarifa configurada. |
| **Total** | Subtotal − descuento + envío. |
| **Impuestos** | Stripe los calcula en la pantalla de pago. |

Ejemplo: subtotal `$2,000.00`, cupón `BIENVENIDA10` (−10 % = `$200.00`), envío gratis → total `$1,800.00`.

### Cupones

- Escribe el código en el checkout y el descuento se aplica al crear el pedido.
- El cupón se comprueba por **fechas**, **usos máximos**, **importe mínimo** y **estado activo**.
- El código no distingue mayúsculas (`bienvenida10` = `BIENVENIDA10`).
- Si no es válido, aparece un error y no se crea ningún pedido.
- El contador de usos del cupón se incrementa al crear el pedido y se devuelve si se cancela.

El cupón inicial del prototipo es **`BIENVENIDA10`** (10 % de descuento).

## 4. Flujo del pago

```text
Bolsa → Checkout → Pedido pendiente → Stripe Checkout → Webhook → Pedido pagado
```

1. La tienda crea el **pedido** con estado `Pendiente` y guarda una copia de cada línea (nombre, SKU, precio y cantidad).
2. Se **descuenta el stock** de las variantes para reservar las piezas.
3. Si Stripe está configurado, se crea una **sesión de pago** y el navegador se redirige a Stripe.
4. Tras el pago, Stripe redirige a `/checkout/confirmado/{pedido}` y envía un **webhook** a `/stripe/webhook`.
5. El webhook (o la página de confirmación como respaldo) marca el pedido como **Pagado**, guarda la referencia de Stripe y **vacía la bolsa**.

> El webhook es la fuente de verdad. La página de confirmación solo consulta Stripe si el webhook todavía no ha llegado.

### Si se cancela el pago

Al volver desde Stripe a `/checkout/cancelado/{pedido}` (o si la sesión expira):

- El pedido pasa a **Cancelado**.
- Se **devuelve el stock** reservado.
- Se devuelve el uso del cupón.
- La **bolsa se conserva** para poder intentarlo de nuevo.

## 5. Estados del pedido

| Estado | Significado |
|---|---|
| Pendiente | Pedido creado, esperando confirmación de pago. |
| Pagado | Stripe confirmó el cobro. |
| Enviado | Preparado y entregado al transportista (se marca desde el panel). |
| Entregado | El cliente recibió el pedido. |
| Cancelado | Pago cancelado o sesión expirada. |

Estados de pago: **Pendiente**, **Pagado**, **Fallido** y **Reembolsado**.

## 6. Página del pedido

La confirmación está en `/pedido/{numero}` y muestra:

- Número de pedido (por ejemplo `LS-2026-A1B2C3`), estado y estado de pago.
- Piezas, cantidades e importes.
- Subtotal, descuento, envío y total.
- Dirección de envío, teléfono y notas.
- Fecha y hora del pago cuando ya está confirmado.

> El pedido solo es visible para el usuario que lo hizo o, si era invitado, para la sesión que lo creó.

## 7. Modo demostración (local)

Si no hay claves de Stripe configuradas:

- En **local** y en **tests**, el pedido se marca como pagado con el método `demo` y aparece el aviso **“Modo demostración · sin cobro real”**. No se contacta con Stripe.
- En **producción**, el checkout se detiene con un error claro indicando que falta `STRIPE_SECRET`.

Así se puede probar todo el flujo sin una cuenta de Stripe.

## 8. Configuración de Stripe

Añade estas variables al `.env` del servidor:

```dotenv
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

En Stripe, registra el webhook:

```text
https://theluxurystore.com/stripe/webhook
```

Eventos necesarios:

| Evento | Acción |
|---|---|
| `checkout.session.completed` | Marca el pedido como pagado. |
| `checkout.session.async_payment_succeeded` | Confirma pagos que tardan (por ejemplo, transferencias). |
| `checkout.session.expired` | Cancela el pedido y devuelve el stock. |
| `checkout.session.async_payment_failed` | Cancela el pedido y devuelve el stock. |

El webhook lleva la cabecera `Stripe-Signature`. La tienda verifica la firma HMAC-SHA256 contra `STRIPE_WEBHOOK_SECRET` con una tolerancia de 5 minutos; una firma inválida devuelve HTTP 400 y no cambia el pedido.

La URL de retorno incluye el marcador `{CHECKOUT_SESSION_ID}` de Stripe para poder comprobar el estado de la sesión.

## 9. Seguridad

- El precio y el descuento se **recalculan en el servidor**: el navegador no puede cambiar el importe.
- El stock se bloquea y se vuelve a comprobar al crear el pedido.
- El número de pedido es único (`LS-AÑO-XXXXXX`).
- Los datos de pago nunca pasan por la tienda: Stripe los recoge en su propia página.
- La ruta del webhook está exenta de CSRF porque la autenticación es la firma.
- `.env` y las claves de Stripe nunca se suben al repositorio.

## 10. Comprobaciones rápidas

| Prueba | Resultado esperado |
|---|---|
| Checkout con bolsa vacía | Redirige a la bolsa con un aviso. |
| Checkout en local sin claves | Pedido pagado en modo demostración, bolsa vacía. |
| Cupón `BIENVENIDA10` | Descuento del 10 % y total recalculado. |
| Cupón inventado | Error en el campo y ningún pedido creado. |
| Pago con Stripe | Redirección a Stripe y pedido pendiente. |
| Webhook firmado como pagado | Pedido pagado y bolsa vaciada. |
| Webhook con firma inválida | HTTP 400, pedido sin cambios. |
| Cancelar el pago | Pedido cancelado, stock devuelto y bolsa intacta. |
| Abrir el pedido con otro usuario | Error 404. |
