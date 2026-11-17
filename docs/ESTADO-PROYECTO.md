# Estado del proyecto — Luxury Store

**Fecha:** 17 de noviembre de 2026  
**Rama de trabajo:** `feat/checkout-stripe`  
**Repositorio:** https://github.com/edgar9112/luxury-store

Este documento resume **qué está terminado**, **qué se ha verificado** y **qué falta** para tener la tienda lista para producción. Para el detalle de uso, consulta el [manual](manual/README.md).

---

## 1. Resumen ejecutivo

Luxury Store es una tienda de artículos de lujo construida con **Laravel 12**, **Filament 5** (panel admin), **Livewire 4**, **Tailwind 4** vía Vite y **MySQL**. Trabaja en **dólares estadounidenses (USD)**, con importes almacenados en céntimos.

Al cierre de hoy, el **cliente de la tienda ya puede comprar de principio a fin**: home → catálogo filtrado → ficha de producto → bolsa → checkout → pago con Stripe → confirmación del pedido.

| Área | Estado |
|---|---|
| Identidad visual y home | ✅ Terminado |
| Catálogo, ficha de producto y bolsa | ✅ Terminado |
| Pedidos, checkout y pagos | ✅ Terminado |
| Dominio de datos y seeders | ✅ Terminado |
| Manual de usuario (capítulos 01–05 y 08) | ✅ Terminado |
| Panel de administración funcional | ⏳ Pendiente |
| Área de cliente | ⏳ Pendiente |
| Correos transaccionales | ⏳ Pendiente |
| Contenido visual definitivo (fotos) | ⏳ Pendiente |
| Despliegue a producción | ⏳ Pendiente |

---

## 2. Historial de entregas

| Rama | Commits | Contenido |
|---|---|---|
| `main` | `d3b59bf` → `9723dac` | Base Laravel, esquema de tienda, modelos, seeders, moneda USD, manual inicial e identidad visual. |
| `feat/catalogo-filtros` | `8dd9c5a`, `71f4fbf` | Catálogo con filtros, ficha de producto, bolsa y manuales 04 y 08. |
| `feat/checkout-stripe` | `cd7f38c`, `cbaefc7` | Checkout, Stripe, webhook, pedidos y manual 05. |

Ramas publicadas en `origin`. Falta abrir/fusionar los PR para que `main` contenga todo:

- https://github.com/edgar9112/luxury-store/pull/new/feat/catalogo-filtros
- https://github.com/edgar9112/luxury-store/pull/new/feat/checkout-stripe

---

## 3. Lo que llevamos

### 3.1 Dominio y datos

| Elemento | Detalle |
|---|---|
| Modelos | `Category`, `Product`, `ProductImage`, `ProductVariant`, `Cart`, `CartItem`, `Coupon`, `Order`, `OrderItem`, `Address`, `NewsletterSubscriber`, `User`. |
| Enums | `OrderStatus` (pendiente/pagado/enviado/entregado/cancelado), `PaymentStatus`, `CouponType`. |
| Migraciones | Esquema completo de tienda (categorías → pedidos) + `add_cart_id_to_orders_table`. |
| Seeders | Categorías, catálogo prototipo en USD, administrador y cupón `BIENVENIDA10`. |
| Soporte | `App\Support\Money` para formatear USD y convertir importes a Stripe. |
| Configuración | `config/store.php` (moneda, envío, país) y `config/stripe.php` (claves). |

### 3.2 Catálogo y producto

- Rutas `GET /catalogo` y `GET /producto/{slug}`.
- `CatalogController`: filtros combinables por categoría, talla, color y precio; rangos invertidos corregidos; órdenes permitidos; paginación de 12; chips de filtros activos; estado vacío.
- `ProductController`: ficha con galería, variantes activas, stock, descuentos, acordeones y recomendados.
- `Product::artName()` y `Product::toneClass()` centralizan las ilustraciones vectoriales por categoría.
- `resources/js/app.js`: `productOptions()` gestiona color, talla, cantidad y disponibilidad en el navegador.
- Tarjetas de producto enlazadas a la ficha real.

### 3.3 Bolsa

- Rutas `GET/POST /bolsa`, `PATCH /bolsa/{item}` y `DELETE /bolsa/{item}`.
- `CartService` resuelve la bolsa del invitado (sesión) o del usuario, y calcula subtotal, envío, descuento y total.
- Envío gratis desde `$200.00` de subtotal; tarifa configurable por debajo.
- `AppServiceProvider` mantiene el contador de la cabecera actualizado en cada vista.
- Vista `bolsa.blade.php` con cantidades, borrado, resumen y acceso al checkout.

### 3.4 Pedidos y pagos

- `GET /checkout` y `POST /checkout`: formulario de contacto/envío, cupón y notas; importes recalculados en servidor.
- `StripeCheckout`: crea una sesión de pago alojada, añade el envío como línea y el descuento como cupón de Stripe; consulta sesiones.
- `OrderPayment`: marca pedidos pagados, vacía la bolsa, cancela pedidos y devuelve stock/usos de cupón.
- `StripeWebhookController`: verifica la firma HMAC-SHA256 (`Stripe-Signature`, tolerancia de 5 min) y atiende `completed`, `async_payment_succeeded`, `expired` y `async_payment_failed`.
- `GET /checkout/confirmado/{pedido}`, `GET /checkout/cancelado/{pedido}`, `GET /pedido/{numero}`.
- **Modo demostración:** sin `STRIPE_SECRET`, en local/tests el pedido se marca pagado sin cobro; en producción el checkout se detiene con un error explícito.
- Reserva de stock con `lockForUpdate()` y comprobación dentro de la transacción.
- Pedidos privados: solo el dueño autenticado o la sesión que los creó (invitados).

### 3.5 Manual de usuario

| Capítulo | Estado |
|---|---|
| [01 — Introducción](manual/01-introduccion.md) | ✅ |
| [02 — Instalación local](manual/02-instalacion.md) | ✅ |
| [03 — Configuración de la tienda](manual/03-configuracion-tienda.md) | ✅ |
| [04 — Catálogo, ficha y bolsa](manual/04-catalogo.md) | ✅ |
| [05 — Pedidos y pagos](manual/05-pedidos-pagos.md) | ✅ |
| 06 — Clientes | ⏳ |
| 07 — Panel de administración | ⏳ |
| [08 — Publicación en hosting](manual/08-publicacion.md) | ✅ |

### 3.6 Verificación

| Comprobación | Resultado |
|---|---|
| `php artisan test` | **41 tests, 145 aserciones — OK** |
| `npm run build` | OK (CSS 73 kB, JS 60 kB) |
| Pint en archivos propios | OK |
| `php artisan route:list --except-vendor` | 14 rutas públicas |
| `php artisan migrate --force` (BD local) | Migración `cart_id` aplicada |

Reparto de tests: `Unit` (Money), `ExampleTest`, `NewsletterTest`, `CatalogPageTest` (12), `ProductPageTest` (5), `CartPageTest` (6), `CheckoutTest` (11).

---

## 4. Lo que falta

### Prioridad alta — para poder operar la tienda

| Tarea | Por qué | Capítulo |
|---|---|---|
| **Panel de administración con Filament** | Hoy `/admin` está montado pero no hay recursos: no se pueden gestionar productos, categorías, pedidos ni cupones desde la UI. | 07 |
| **Gestión de estados de pedido** | Marcar *Enviado/Entregado* y ver pedidos pagados. | 07 |
| **Correos transaccionales** | Confirmación de pedido, envío y recuperación de contraseña. Configurar Resend/Postmark y plantillas Blade. | 05/07 |
| **Área de cliente** | Registro, login, historial de pedidos y direcciones guardadas. | 06 |
| **Fusionar las ramas en `main`** | `main` todavía está en `9723dac`; todo lo nuevo vive en dos ramas. | — |
| **Claves y webhook de Stripe en producción** | Sin `STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET` no se puede cobrar fuera de local. | 05/08 |

### Prioridad media — experiencia de compra

| Tarea | Detalle |
|---|---|
| Fotografías reales | Sustituir los SVG por categoría por imágenes subidas a `storage/app/public` y ejecutar `php artisan storage:link`. |
| Búsqueda de productos | El icono de búsqueda de la cabecera aún lleva al catálogo. |
| Favoritos / wishlist | El botón de corazón de la ficha es decorativo. |
| Reembolsos desde Stripe | Existe el estado `Refunded`, pero no flujo para usarlo. |
| Reseñas y valoraciones | Las estrellas de la ficha son estáticas. |
| Alertas de stock bajo | Aviso al administrador cuando una variante se agota. |
| Cupones avanzados | Aplicación automática, cupones por cliente y límites por usuario. |

### Prioridad baja — calidad y operaciones

| Tarea | Detalle |
|---|---|
| CI y checks automáticos | No hay GitHub Actions; ejecutar tests y Pint manualmente. |
| Base de datos de tests dedicada | `phpunit.xml` usa SQLite `:memory:`; `AGENTS.md` todavía describe la configuración antigua y conviene actualizarlo. |
| Colas para correos | `QUEUE_CONNECTION=database` en local; hace falta worker en producción (Supervisor). |
| Backups y monitorización | Copias diarias de MySQL y `storage/`, revisión de logs. |
| Acceso a `/admin` en producción | `User` debe implementar `FilamentUser` o el panel devuelve 403 fuera de `local`. |
| Optimización de cachés | `config:cache`, `route:cache` y `view:cache` en despliegue. |
| Documentación viva | Actualizar este documento al cerrar cada nueva funcionalidad. |

---

## 5. Riesgos y decisiones a recordar

1. **Modo demostración:** en local los pedidos se marcan como pagados sin cobrar. Es intencionado y está avisado en la UI y en el manual; no debe confundirse con un pago real.
2. **Webhook obligatorio en producción:** la página de confirmación es solo un respaldo. Sin `STRIPE_WEBHOOK_SECRET` configurado, los pagos podrían quedar pendientes.
3. **Stock reservado:** se descuenta al crear el pedido y se devuelve al cancelar. Si un pedido queda pendiente para siempre, el stock sigue reservado; conviene añadir un job de expiración.
4. **Pedidos de invitado:** el acceso depende de la sesión. Si se añade área de cliente, conviene asociar el pedido al usuario tras el registro.
5. **Sin correos:** el cliente no recibe confirmación todavía, aunque el manual 08 ya prepara el proveedor SMTP.
6. **Entorno de Laragon:** `APP_URL=http://luxury-store.test`; `php artisan serve` usa `127.0.0.1:8000` y puede afectar a las URL de retorno de Stripe.

---

## 6. Cómo retomar el trabajo

```bash
# Arrancar todo el entorno de desarrollo
composer dev

# Verificar
php artisan test
php vendor/bin/pint
npm run build

# Base de datos y enlace de almacenamiento
php artisan migrate
php artisan storage:link
```

**Siguiente paso recomendado:** crear los recursos de Filament (`app/Filament/Resources`) para `Product`, `Category`, `Order` y `Coupon`, con acciones para cambiar el estado del pedido. Después, el área de cliente y los correos.

---

## 7. Para el cierre de hoy

- Código y documentación publicados en `origin/feat/checkout-stripe`.
- Árbol de trabajo limpio; tests y build en verde.
- Sin despliegue a producción ni claves reales configuradas.
- Queda pendiente abrir los PR y decidir el orden de fusión en `main`.
