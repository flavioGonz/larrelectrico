# Larr Eléctrico — Tienda nueva

Tienda e-commerce de materiales eléctricos. Front estático generado con Node + API en PHP sobre MySQL.

## Estructura
- `index.html`, `catalogo.html`, `producto.html`, `cuenta.html`, `favoritos.html`, `comparador.html` — páginas (generadas por `build/gen.mjs`).
- `assets/app.css`, `assets/app.js` — estilos y lógica del front.
- `api/` — endpoints PHP (catálogo, auth, pedidos) sobre la base `larrelec_final2`.
- `admin/` — panel de administración (login, productos, edición masiva, pedidos, vendors, categorías, reseñas, clientes).
- `build/gen.mjs` — generador estático (lee `catalog.json` / la API y arma el HTML).
- `config.php` — credenciales (NO versionado; ver `config.sample.php`).

## Build
```
node build/gen.mjs
```

## Deploy
Subir los archivos a `public_html/final/` del cPanel.

## Datos
Migrados desde el Joomla/VirtueMart anterior (productos, categorías, precios, ventas, usuarios, logos de fabricantes).
