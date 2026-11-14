# 08 — Publicación en hosting

Este capítulo explica cómo publicar **Luxury Store** en internet con un dominio propio, HTTPS y pagos en **dólares estadounidenses (USD)**.

> El entorno de Laragon (`luxury-store.test`) sirve solo para desarrollo. En producción se usa un servidor con PHP, MySQL y HTTPS.

## Resumen de la configuración

| Elemento | Valor recomendado |
|---|---|
| Dominio | `theluxurystore.com` |
| DNS y SSL | Cloudflare + Let's Encrypt |
| Hosting | Laravel Cloud (US East) o VPS administrado |
| Servidor | 2 vCPU · 4 GB RAM · 80 GB NVMe |
| Sistema | Ubuntu 24.04 LTS |
| Stack | Nginx + PHP-FPM 8.3 + MySQL 8 + Redis |
| Moneda | USD |
| Pagos | Stripe |
| Correo | Resend o Postmark |

## 1. Requisitos del servidor

| Componente | Requerimiento |
|---|---|
| PHP | 8.2 o superior |
| Extensiones PHP | `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `gd` o `imagick`, `intl` |
| Composer | 2.x |
| MySQL | 8.0 o superior |
| Node.js | 20+ (solo para compilar assets) |
| Servidor web | Nginx + PHP-FPM |
| Procesos | Cron y Supervisor |
| HTTPS | Obligatorio para Stripe y cookies seguras |

No hace falta Node.js en el servidor si los assets se compilan antes de subir (`public/build`).

## 2. Opciones de hosting

### Opción A — Laravel Cloud (recomendada)

- Plataforma gestionada para Laravel.
- Incluye PHP, MySQL, colas, SSL y despliegues.
- Región **US East** para clientes de Estados Unidos.
- Coste orientativo: **USD 15–30/mes**.

### Opción B — VPS administrado

| Recurso | Mínimo | Recomendado |
|---|---|---|
| CPU | 2 vCPU | 2–4 vCPU |
| RAM | 2 GB | 4 GB |
| Disco | 40 GB NVMe | 80 GB NVMe |
| Sistema | Ubuntu 24.04 | Ubuntu 24.04 |

Proveedores: DigitalOcean (NYC3), Hetzner (Ashburn), Hostinger VPS. Se recomienda añadir **Laravel Forge**, **Ploi** o **RunCloud** para gestionar despliegues, SSL y colas.

> ⚠️ No se recomienda un hosting compartido barato: Filament, las colas y los webhooks de Stripe necesitan SSH, cron y procesos en segundo plano.

## 3. Dominio y DNS

Comprar el dominio en Cloudflare Registrar, Porkbun o Namecheap.

| Tipo | Nombre | Valor |
|---|---|---|
| A | `@` | IP del servidor |
| CNAME | `www` | `theluxurystore.com` |

Después:

1. Activar SSL (Let's Encrypt).
2. Forzar HTTPS.
3. Redirigir `www` al dominio principal.
4. Configurar MX, SPF, DKIM y DMARC si el correo saldrá del mismo dominio.

Alternativas si el dominio principal no está disponible: `shopluxurystore.com`, `getluxurystore.com`, `luxurystore.boutique`.

## 4. Variables de producción

Editar `.env` en el servidor:

```dotenv
APP_NAME="Luxury Store"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://theluxurystore.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=luxury_store
DB_USERNAME=luxury_user
DB_PASSWORD=cambiar_por_una_clave_segura

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

STORE_CURRENCY=USD
STORE_CURRENCY_SYMBOL=$
STORE_SHIPPING_CENTS=495
STORE_FREE_SHIPPING_CENTS=20000
STORE_COUNTRY=US
```

> ⚠️ Nunca subir el `.env` al repositorio. Debe estar en `.gitignore`.

## 5. Despliegue

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

En cada nueva versión, repetir la instalación de dependencias, migraciones y cachés.

## 6. Colas y tareas programadas

### Supervisor

Las colas procesan correos, confirmaciones y webhooks:

```ini
[program:luxury-store-worker]
command=php /var/www/luxury-store/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/luxury-store
autostart=true
autorestart=true
user=www-data
numprocs=1
```

### Cron

```cron
* * * * * cd /var/www/luxury-store && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Stripe en USD

1. Crear la cuenta de Stripe y activar los pagos.
2. Copiar las claves a `.env`:

```dotenv
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
```

3. Registrar el webhook:

```text
https://theluxurystore.com/stripe/webhook
```

4. Verificar la firma del webhook con el secreto correspondiente.

> ⚠️ Si la empresa está registrada en México, Stripe puede cobrar en USD pero normalmente liquida en MXN con tipo de cambio. Para liquidar en USD se necesita una entidad estadounidense.

Los importes ya se guardan en céntimos (`$4,850.00 = 485000`), así que Stripe recibe el importe exacto.

## 8. Correo transaccional

Configurar un proveedor como Resend, Postmark o Amazon SES y actualizar:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.proveedor.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=hello@theluxurystore.com
MAIL_FROM_NAME="${APP_NAME}"
```

Sin esto, las confirmaciones de pedido no llegarán al cliente.

## 9. Seguridad antes de publicar

- Cambiar `ADMIN_PASSWORD`; nunca dejar `password`.
- `APP_DEBUG=false` y `APP_ENV=production`.
- HTTPS forzado en todo el sitio.
- Permisos de escritura solo en `storage/` y `bootstrap/cache/`.
- Backups diarios de MySQL y de `storage/app/public`.
- Limitar el acceso a `/admin` a usuarios administradores.
- Revisar que `.env`, logs y backups no sean accesibles desde el navegador.

## 10. Comprobaciones finales

```bash
php artisan about
php artisan migrate:status
php artisan route:list --except-vendor
```

| Comprobación | Resultado esperado |
|---|---|
| `https://theluxurystore.com` | Carga la home |
| `https://theluxurystore.com/catalogo` | Carga el catálogo |
| `https://theluxurystore.com/admin` | Muestra el acceso al panel |
| Certificado | Válido y sin avisos |
| Correo de prueba | Llega a la bandeja de entrada |
| Pago de prueba | Stripe confirma el pedido |

## 11. Coste estimado

| Concepto | Coste |
|---|---|
| Hosting | USD 15–30/mes |
| Dominio | USD 10–30/año |
| Correo | USD 0–15/mes |
| Backups | USD 1–5/mes |
| **Total** | **USD 17–52/mes** |

## Problemas frecuentes

| Problema | Solución |
|---|---|
| Error 500 tras desplegar | Revisar `storage/logs/laravel.log` y permisos |
| Página sin estilos | Ejecutar `npm run build` o subir `public/build` |
| Cambios en `.env` sin efecto | `php artisan config:clear` y volver a cachear |
| Las colas no procesan | Comprobar Supervisor y `QUEUE_CONNECTION` |
| Stripe no confirma el pago | Revisar el webhook y la firma |
| No llegan los correos | Revisar SPF, DKIM y credenciales SMTP |
