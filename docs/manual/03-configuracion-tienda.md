# 03 — Configuración de la tienda

Este capítulo explica los ajustes generales de la tienda: moneda, envío y datos del administrador. Todos se controlan desde el archivo `.env`; no hace falta tocar código.

## Moneda

La tienda trabaja en **dólares estadounidenses (USD)**.

```dotenv
STORE_CURRENCY=USD
STORE_CURRENCY_SYMBOL=$
```

| Variable | Valor actual | Significado |
|---|---|---|
| `STORE_CURRENCY` | `USD` | Código internacional de la moneda. |
| `STORE_CURRENCY_SYMBOL` | `$` | Símbolo que se muestra junto al precio. |

Los importes se guardan en **céntimos**, nunca en decimales. Esto evita errores de redondeo y garantiza que Stripe cobre exactamente lo que se muestra.

| Precio mostrado | Valor guardado |
|---|---|
| `$4,850.00` | `485000` |
| `$349.00` | `34900` |
| `$4.95` | `495` |

> **Importante:** si algún día se cambia la moneda, hay que revisar también los precios guardados. La moneda no convierte importes automáticamente.

## Envío

```dotenv
STORE_SHIPPING_CENTS=495
STORE_FREE_SHIPPING_CENTS=20000
STORE_COUNTRY=US
```

| Variable | Valor actual | Significado |
|---|---|---|
| `STORE_SHIPPING_CENTS` | `495` | Coste de envío estándar: `$4.95`. |
| `STORE_FREE_SHIPPING_CENTS` | `20000` | A partir de `$200.00` de subtotal, el envío es gratis. |
| `STORE_COUNTRY` | `US` | País que aparece por defecto en el checkout. |

Para cambiar el coste de envío, por ejemplo a `$9.95`:

```dotenv
STORE_SHIPPING_CENTS=995
```

Y para que el envío sea gratis a partir de `$500.00`:

```dotenv
STORE_FREE_SHIPPING_CENTS=50000
```

Después de cualquier cambio en `.env`:

```powershell
php artisan config:clear
```

## Administrador inicial

El seeder crea un usuario administrador con estos datos:

```dotenv
ADMIN_NAME="Administrador"
ADMIN_EMAIL=admin@luxurystore.test
ADMIN_PASSWORD=password
```

| Variable | Significado |
|---|---|
| `ADMIN_NAME` | Nombre que aparecerá en el panel. |
| `ADMIN_EMAIL` | Correo con el que se inicia sesión. |
| `ADMIN_PASSWORD` | Contraseña inicial. |

> ⚠️ **Muy importante:** antes de publicar la tienda en internet, cambia `ADMIN_PASSWORD` por una contraseña segura y vuelve a ejecutar el seeder, o cambia la contraseña desde el panel. Nunca dejes `password` en producción.

## Cupón de bienvenida

El seeder crea el cupón:

| Campo | Valor |
|---|---|
| Código | `BIENVENIDA10` |
| Tipo | Porcentaje |
| Descuento | `10 %` |
| Compra mínima | Ninguna |
| Usos máximos | Ilimitados |

Se puede desactivar o modificar desde el panel de administración (capítulo 07).

## Resumen de comandos

```powershell
php artisan config:clear          # recargar el .env
php artisan db:seed               # cargar todos los datos iniciales
php artisan migrate:fresh --seed  # recrear la base de datos y cargar datos
```

> ⚠️ `migrate:fresh` **borra todas las tablas y todos los datos**. Úsalo solo en desarrollo o antes de publicar la tienda.
