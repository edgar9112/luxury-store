# 02 — Instalación local

Esta guía permite poner la tienda en marcha en un ordenador Windows con **Laragon**. Sirve para desarrollo y pruebas; la publicación en internet se explica en el capítulo 08.

## Requisitos

| Programa | Versión mínima | Para qué |
|---|---|---|
| PHP | 8.2 | Ejecutar Laravel |
| Composer | 2.x | Instalar dependencias de PHP |
| Node.js | 20.x | Compilar los estilos y scripts |
| MySQL | 8.0 | Guardar los datos de la tienda |
| Laragon | 6.x | Servidor local y dominio `*.test` |

## Puesta en marcha

### 1. Clonar el proyecto

Abre una terminal en `E:\laragon\www` y ejecuta:

```powershell
git clone https://github.com/edgar9112/luxury-store.git
cd luxury-store
```

### 2. Instalación automática

El proyecto incluye un script que instala dependencias, prepara el archivo `.env`, crea la base de datos y compila los estilos:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-windows.ps1
```

### 3. Instalación manual (alternativa)

```powershell
composer install
copy .env.example .env
php artisan key:generate
```

Crea la base de datos `luxury_store` en MySQL y después ejecuta:

```powershell
php artisan migrate --seed
npm install
npm run build
```

### 4. Arrancar MySQL y el servidor

1. Abre **Laragon** y pulsa **Start All**.
2. Comprueba que MySQL está en marcha.
3. Visita `http://luxury-store.test` en el navegador.

Si el dominio no responde, en Laragon usa **Reload** para regenerar los hosts virtuales.

## Configuración de la moneda

La moneda se define en el archivo `.env`:

```dotenv
STORE_CURRENCY=USD
STORE_CURRENCY_SYMBOL=$
STORE_SHIPPING_CENTS=495
STORE_FREE_SHIPPING_CENTS=20000
STORE_COUNTRY=US
```

| Variable | Significado |
|---|---|
| `STORE_CURRENCY` | Código de la moneda (`USD`). |
| `STORE_CURRENCY_SYMBOL` | Símbolo que se muestra (`$`). |
| `STORE_SHIPPING_CENTS` | Coste de envío en céntimos (`495` = `$4.95`). |
| `STORE_FREE_SHIPPING_CENTS` | Subtotal a partir del cual el envío es gratis (`20000` = `$200.00`). |
| `STORE_COUNTRY` | País por defecto del checkout (`US`). |

> **Importante:** los importes se guardan en **céntimos**. `$4,850.00` se almacena como `485000`. Así se evitan errores de redondeo y Stripe recibe siempre el importe exacto.

Después de cambiar el `.env`, limpia la configuración:

```powershell
php artisan config:clear
```

## Comprobar que todo funciona

```powershell
php artisan about
php artisan migrate:status
```

- `php artisan about` muestra la versión de Laravel, PHP y la base de datos.
- `php artisan migrate:status` debe mostrar todas las migraciones como `Ran`.

## Problemas frecuentes

| Problema | Solución |
|---|---|
| `SQLSTATE[HY000] [2002]` | MySQL no está arrancado. Pulsa **Start All** en Laragon. |
| La página sale sin estilos | Ejecuta `npm run build` o `npm run dev`. |
| `No application encryption key` | Ejecuta `php artisan key:generate`. |
| Cambios en `.env` sin efecto | Ejecuta `php artisan config:clear`. |
| El dominio `.test` no carga | Pulsa **Reload** en Laragon y revisa el archivo `hosts`. |
