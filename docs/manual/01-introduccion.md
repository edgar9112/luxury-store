# 01 — Introducción

## ¿Qué es Luxury Store?

Luxury Store es una tienda online de artículos de lujo: relojes, ropa, calzado y accesorios. La tienda permite:

- Navegar por categorías y ver el catálogo con filtros.
- Consultar la ficha de cada producto, con sus tallas, colores y disponibilidad.
- Añadir productos al carrito y comprar como invitado o con una cuenta.
- Pagar de forma segura con tarjeta mediante **Stripe**.
- Recibir confirmación del pedido y consultar su estado.

## Moneda

Todos los importes de la tienda se gestionan en **dólares estadounidenses (USD)** y se muestran con el formato:

```text
$1,234.56
```

El símbolo `$` va delante del importe, se usa coma para los miles y punto para los decimales.

## Cómo se organiza la información

```text
Categoría
  └── Producto
        └── Variante (talla / color)  ← aquí se controla el stock
```

Ejemplo: la categoría **Relojes** contiene el producto **Cronógrafo Heritage**, disponible en dos variantes (correa de cuero o acero). Cada variante tiene su propio stock y precio.

## Roles de usuario

| Rol | Qué puede hacer |
|---|---|
| **Visitante** | Ver la tienda, el catálogo y las fichas de producto. Puede comprar como invitado. |
| **Cliente** | Todo lo anterior, además de guardar direcciones y consultar sus pedidos. |
| **Administrador** | Gestionar productos, pedidos, cupones y clientes desde el panel de administración. |

## Estados de un pedido

| Estado | Significado |
|---|---|
| **Pendiente** | El pedido se ha creado, pero el pago aún no se ha confirmado. |
| **Pagado** | Stripe ha confirmado el pago correctamente. |
| **Enviado** | El pedido ha salido del almacén. |
| **Entregado** | El cliente ha recibido el pedido. |
| **Cancelado** | El pedido se ha anulado. |

## Estados del pago

| Estado | Significado |
|---|---|
| **Pendiente** | A la espera de confirmación de Stripe. |
| **Pagado** | Cobro confirmado. |
| **Fallido** | El cobro no se pudo completar. |
| **Reembolsado** | Se ha devuelto el importe al cliente. |
