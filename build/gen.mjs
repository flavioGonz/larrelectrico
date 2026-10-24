import fs from 'fs';
import path from 'path';
import {fileURLToPath} from 'url';
const __dir=path.dirname(fileURLToPath(import.meta.url));
const ROOT=path.resolve(__dir,'..');
const data=JSON.parse(fs.readFileSync(path.join(ROOT,'catalog.json'),'utf8'));
const V=Date.now();
const IMGB='https://www.larrelectrico.uy/';
const imgu=u=>!u?null:(u.startsWith('http')?u:IMGB+u.replace(/^\/+/,''));
const money=n=>n==null?null:'$'+new Intl.NumberFormat('es-UY',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n);
const esc=s=>String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const IC={
 bolt:'<path d="M13 2 3 14h7l-1 8 10-12h-7z"/>',search:'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
 cart:'<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2l2.5 13h12l2-9H6"/>',
 user:'<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',menu:'<path d="M3 6h18M3 12h18M3 18h18"/>',
 chev:'<path d="m6 9 6 6 6-6"/>',arrow:'<path d="M5 12h14M13 6l6 6-6 6"/>',plus:'<path d="M12 5v14M5 12h14"/>',
 star:'<path d="m12 3 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8L3.6 9.1l5.8-.8z"/>',
 truck:'<path d="M3 6h11v9H3zM14 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
 shield:'<path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
 clock:'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
 headset:'<path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13a2 2 0 0 1 2 2v2a2 2 0 0 1-4 0v-2a2 2 0 0 1 2-2zM20 13a2 2 0 0 0-2 2v2a2 2 0 0 0 4 0v-2a2 2 0 0 0-2-2z"/>',
 cable:'<path d="M4 4v6a4 4 0 0 0 4 4h8a4 4 0 0 1 4 4v2"/><circle cx="4" cy="3" r="1.6"/><circle cx="20" cy="21" r="1.6"/>',
 grid:'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 bulb:'<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.3 1 2.5h6c0-1.2.3-1.8 1-2.5A6 6 0 0 0 12 3z"/>',
 pipe:'<path d="M4 8h10a4 4 0 0 1 4 4v8"/><path d="M4 5v6M14 17h8"/>',plug:'<path d="M9 2v6M15 2v6M7 8h10v3a5 5 0 0 1-10 0z"/><path d="M12 16v6"/>',
 chip:'<rect x="7" y="7" width="10" height="10" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>',
 tool:'<path d="M14.5 5.5a3.5 3.5 0 0 0-4.8 4.5l-6 6 2.3 2.3 6-6a3.5 3.5 0 0 0 4.5-4.8l-2 2-2-2z"/>',
 pkg:'<path d="m12 2 8 4.5v9L12 20l-8-4.5v-9z"/><path d="m4 7 8 4.5L20 7M12 20v-8.5"/>',
 wa:'<path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.5A10 10 0 1 0 12 2z"/>',
 pin:'<path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
 mail:'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
 heart:'<path d="M12 21C5 15.5 3 11.5 3 8.5A4.5 4.5 0 0 1 12 6a4.5 4.5 0 0 1 9 2.5c0 3-2 7-9 12.5z"/>',
 fire:'<path d="M12 3c1 3 4 4 4 8a4 4 0 0 1-8 0c0-1 .5-2 1-3 .5 2 2 2 2 0 0-2-1-3 1-5z"/>',
 home:'<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
 eye:'<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
 check:'<path d="M20 6 9 17l-5-5"/>',lock:'<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>'
};
const svg=(n,w=20)=>`<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="${w}" height="${w}">${IC[n]||IC.pkg}</svg>`;
const catIcon=name=>{const n=name.toUpperCase();
 if(/CABLE|GOMA|SUPER|CONDUCT|FLEX|UNIPOLAR/.test(n))return'cable';
 if(/DIFERENC/.test(n))return'shield';if(/TERMIC|T.RMIC|LLAVE|BREAKER|MAGNET/.test(n))return'bolt';
 if(/TABLERO|GABINET|CAJA|MODUL/.test(n))return'grid';if(/LED|ILUMIN|LAMPAR|REFLECT|PANEL|TUBO|FOCO|LUZ/.test(n))return'bulb';
 if(/DUCTO|CANAL|CA.O|CORRUG/.test(n))return'pipe';if(/TOMA|ENCHUFE|PRESTA|COMBINAC|PUNTO/.test(n))return'plug';
 if(/CONTACT|RELE|REL.|AUTOMAT|CONTROL|TEMPOR/.test(n))return'chip';if(/FERRET|TORNILL|GRAMPA|PRECINT|ACCESOR|HERRAM/.test(n))return'tool';
 return'pkg';};

const head=(title,desc)=>`<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(title)}</title><meta name="description" content="${esc(desc)}">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;450;500;550;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/app.css?v=${V}">
</head><body>`;
const header=`
<div class="topbar"><div class="topbar-in">
<div class="tb-left">
<a href="https://wa.me/59894726950">${svg('wa',14)} +598 94 726 950</a>
<a href="mailto:ventas@larrelectrico.uy" class="tb-hide">${svg('mail',14)} ventas@larrelectrico.uy</a></div>
<div class="tb-right">
<span class="tb-hide">${svg('truck',14)} Envío a todo el país</span>
<span class="tb-hide">${svg('shield',14)} Compra protegida</span>
<a href="cuenta.html">${svg('user',14)} Mi cuenta</a></div>
</div></div>
<div class="nav"><div class="nav-in">
<button class="burger" id="burger" aria-label="Menú">${svg('menu',22)}</button>
<a class="brand" href="index.html"><span class="bk">${svg('bolt',20)}</span><span class="brand-tx">Larr<b>Eléctrico</b><small>Materiales eléctricos</small></span></a>
<form class="navsearch" action="catalogo.html" method="get" role="search">${svg('search',19)}<input name="q" placeholder="Buscar productos, marcas o códigos…" autocomplete="off"><button type="submit">Buscar</button></form>
<div class="nav-act">
<a class="actbtn" href="catalogo.html" id="mSearch" title="Buscar">${svg('search',22)}</a>
<a class="actbtn" href="favoritos.html" title="Favoritos">${svg('heart',22)}<span class="wlbadge" id="wlbadge">0</span></a>
<a class="actbtn" id="navUser" href="cuenta.html" title="Mi cuenta">${svg('user',22)}</a>
<button class="actbtn cartbtn" id="openCart" title="Carrito">${svg('cart',22)}<span class="cbadge" id="cbadge">0</span></button>
</div></div></div>
<div class="catbar"><div class="catbar-in">
<div class="megabtn" id="megabtn">${svg('grid',18)}<span>Todas las categorías</span>${svg('chev',15)}<div class="megamenu" id="megamenu"></div></div>
<nav class="catlinks">
<a href="catalogo.html?q=cable">${svg('cable',16)}Cables</a>
<a href="catalogo.html?q=diferencial">${svg('shield',16)}Protección</a>
<a href="catalogo.html?q=termica">${svg('bolt',16)}Térmicas</a>
<a href="catalogo.html?q=iluminacion">${svg('bulb',16)}Iluminación</a>
<a href="index.html#best">${svg('fire',16)}Más vendidos</a>
<a href="index.html#contacto">${svg('headset',16)}Contacto</a></nav>
<a class="catbar-cta" href="index.html#contacto">${svg('wa',15)} Cotización por volumen</a>
</div></div>`;
const footer=`<footer><div class="wrap"><div class="fg">
<div><a class="brand" href="index.html" style="margin-bottom:14px"><span class="bk">${svg('bolt',18)}</span>Larr<span>Eléctrico</span></a>
<p style="max-width:280px">Tu casa de materiales eléctricos en Uruguay. Calidad, stock y asesoramiento técnico desde hace más de 15 años.</p></div>
<div><h4>Tienda</h4><a href="catalogo.html">Todo el catálogo</a><a href="catalogo.html?q=cable">Cables</a><a href="catalogo.html?q=diferencial">Diferenciales</a><a href="catalogo.html?q=termica">Térmicas</a></div>
<div><h4>Empresa</h4><a href="#">Nosotros</a><a href="#">Marcas</a><a href="cuenta.html">Mi cuenta</a></div>
<div><h4>Contacto</h4><p class="cta">${svg('wa',16)} +598 94 726 950</p><p class="cta">${svg('mail',16)} ventas@larrelectrico.uy</p><p class="cta">${svg('pin',16)} Montevideo, Uruguay</p></div>
</div><div class="fbot"><span>© 2026 Larr Eléctrico — Todos los derechos reservados.</span><span>Sitio nuevo · versión preliminar</span></div></div></footer>`;
const drawer=`<div class="ov" id="ov"></div>
<aside class="drawer" id="drawer"><div class="drawer-h"><h3>Tu carrito</h3><button class="icbtn" id="closeCart">${svg('x',20)}</button></div>
<div class="drawer-b" id="drawerB"></div>
<div class="drawer-f" id="drawerF" style="display:none">
<div class="ctot-row"><span>Subtotal</span><span id="cartSub">$0</span></div>
<div class="ctot-row"><span>IVA (22%)</span><span id="cartIva">$0</span></div>
<div class="tot"><span>Total</span><span id="cartTot">$0</span></div>
<button class="btn btn-pri btn-block" id="checkout">${svg('cart',18)} Finalizar compra</button>
<p class="note">Precios en pesos, IVA 22% incluido en el total. Elegís el medio de pago en el siguiente paso: transferencia, giros, contra entrega, retira en local o Mercado Pago.</p></div></aside>`;
const foot=`${drawer}<script src="assets/app.js?v=${V}"></script></body></html>`;

const stkH=p=>p.stock>5?`<span class="stk"><span class="d"></span>En stock</span>`:(p.stock>0?`<span class="stk low"><span class="d"></span>Últimas (${p.stock})</span>`:`<span class="stk no"><span class="d"></span>Bajo pedido</span>`);
const soldH=p=>p.sales>20?`<span class="sold">${svg('fire',12)}${p.sales} vendidos</span>`:'';
const pcardH=p=>{const im=p.images&&p.images[0]?p.images[0]:p.img;
 const imgt=im?`<img loading="lazy" src="${imgu(im)}">`:`<div class="noimg">${svg('pkg',46)}</div>`;
 const price=p.price?`<div class="price">${money(p.price)}<span class="iva">+ IVA</span></div>`:'<div class="noprice">Consultar</div>';
 const tag=p.sales>20?`<span class="tag sale">${svg('fire',12)}Top</span>`:(p.featured?`<span class="tag">${svg('star',12)}Destacado</span>`:'');
 return `<article class="pc2"><a class="pim" href="producto.html?id=${p.id}">${tag}${imgt}<button class="wl" data-wl="${p.id}" title="Agregar a favoritos">${svg('heart',17)}</button></a>
 <div class="pb"><div class="br">${esc(p.brand||'')}</div><h3><a href="producto.html?id=${p.id}">${esc(p.name)}</a></h3>
 <div class="metaline">${stkH(p)}${soldH(p)}</div>
 <div class="prow">${price}<button class="add" data-add="${p.id}" title="Agregar al carrito">${svg('plus',19)}</button></div></div></article>`;};

const withImg=data.products.filter(p=>(p.images&&p.images[0])||p.img);
const feat=(data.products.filter(p=>p.featured&&((p.images&&p.images[0])||p.img)&&p.price)).slice(0,8);
const featFull=feat.length>=4?feat:withImg.filter(p=>p.price).slice(0,8);
const heroImgs=featFull.slice(0,3).map(p=>imgu(p.images&&p.images[0]||p.img));
const topCats=data.categories.filter(c=>c.n>0).sort((a,b)=>b.n-a.n).slice(0,8);
const brands=['CONATEL','FUNSA','KALOP','LEMU','MOLVENO','RICHI','SELTIR','WELT'];
const bestSellers=[...data.products].filter(p=>(p.images&&p.images[0])||p.img).sort((a,b)=>(b.sales||0)-(a.sales||0)).slice(0,8);
const catImg={};
data.products.forEach(p=>{const im=(p.images&&p.images[0])||p.img;if(im)(p.catids||[]).forEach(cid=>{if(!catImg[cid])catImg[cid]=imgu(im);});});

const home=head('Larr Eléctrico — Materiales eléctricos en Uruguay','Catálogo online de materiales eléctricos: cables, diferenciales, llaves térmicas, ductos, tableros e iluminación. Stock real y precios al día.')+header+`
<header class="hero"><div class="hero-in">
<div><span class="eyebrow">Materiales eléctricos · Uruguay</span>
<h1>Tu obra, <span class="g">bien conectada</span>.</h1>
<p class="lead">Más de ${data.count} productos de las mejores marcas: cables, diferenciales, térmicas, ductos e iluminación. Stock real y despacho a todo el país.</p>
<div class="hero-cta"><a href="catalogo.html" class="btn btn-pri btn-lg">${svg('cart',18)} Ver catálogo</a>
<a href="index.html#contacto" class="btn btn-ghost btn-lg">Pedir cotización</a></div>
<div class="hero-stat"><div><b>${data.count}</b><span>Productos</span></div><div><b>${data.categories.filter(c=>c.n>0).length}</b><span>Categorías</span></div><div><b>+15</b><span>Años</span></div></div></div>
<div class="hero-vis"><div class="halo"></div><div class="hero-show" id="heroShow"></div></div><div style="display:none">
<div class="hpill" style="top:18px;left:0"><span class="dot"></span>Stock disponible</div>
<div class="hpill" style="bottom:26px;right:4px">${svg('truck',15)} Envío 24-48h</div>
<div class="hcard hc1"><div class="im">${heroImgs[0]?`<img src="${heroImgs[0]}">`:''}</div></div>
<div class="hcard hc2"><div class="im">${heroImgs[1]?`<img src="${heroImgs[1]}">`:''}</div></div>
<div class="hcard hc3"><div class="im">${heroImgs[2]?`<img src="${heroImgs[2]}">`:''}</div></div>
</div></div></header>
<div class="benefits"><div class="wrap"><div class="ben-grid">
<a class="ben" href="#envio"><div class="bi">${svg('truck',22)}</div><div><h4>Envío a todo el país</h4><p>Despacho rápido y seguro</p></div></a>
<a class="ben" href="#stock"><div class="bi">${svg('bolt',22)}</div><div><h4>Stock real</h4><p>Inventario actualizado</p></div></a>
<a class="ben" href="#marcas"><div class="bi">${svg('shield',22)}</div><div><h4>Marcas garantizadas</h4><p>Calidad certificada</p></div></a>
<a class="ben" href="#asesoria"><div class="bi">${svg('headset',22)}</div><div><h4>Asesoría técnica</h4><p>Te ayudamos a elegir</p></div></a>
</div></div></div>`;

const homeFull=home+`
<section class="sec"><div class="wrap">
<div class="sec-head ctr reveal"><span class="eyebrow">Explorá por rubro</span><h2>Categorías principales</h2><p>Encontrá lo que necesitás, organizado como en el mostrador.</p></div>
<div class="cat-grid">${topCats.map(c=>`<a class="catc reveal" href="catalogo.html?cat=${encodeURIComponent(c.name)}">${catImg[c.id]?`<div class="catbg" style="background-image:url('${catImg[c.id]}')"></div>`:''}<div class="go">${svg('arrow',18)}</div><div class="cat-tx"><h3>${esc(c.name)}</h3><div class="n">${c.n} producto${c.n!==1?'s':''}</div></div></a>`).join('')}</div>
<div style="text-align:center;margin-top:30px"><a class="btn btn-sec" href="catalogo.html">Ver todas las categorías</a></div>
</div></section>
<div class="marq-wrap"><div class="marq">${brands.concat(brands).map(b=>`<span>${b}</span>`).join('')}</div></div>
<section class="sec"><div class="wrap">
<div class="rowbetween reveal" style="margin-bottom:28px"><div><span class="eyebrow">Selección</span><h2 style="font-size:clamp(25px,3.4vw,38px);font-weight:700;letter-spacing:-.03em;margin-top:6px">Productos destacados</h2></div><a class="link-arrow" href="catalogo.html">Ver todo ${svg('arrow',17)}</a></div>
<div class="pgrid" id="homeGrid">${featFull.map(pcardH).join('')}</div>
</div></section>
<section class="sec" style="padding-top:0" id="best"><div class="wrap">
<div class="rowbetween reveal" style="margin-bottom:28px"><div><span class="eyebrow" style="color:#ff8a00">Los que más salen</span><h2 style="font-size:clamp(25px,3.4vw,38px);font-weight:700;letter-spacing:-.03em;margin-top:6px">Más vendidos</h2></div><a class="link-arrow" href="catalogo.html">Ver todo ${svg('arrow',17)}</a></div>
<div class="pgrid" id="bestGrid">${bestSellers.map(pcardH).join('')}</div>
</div></section>
<section class="sec" id="servicios"><div class="wrap">
<div class="sec-head ctr reveal"><span class="eyebrow">Cómo trabajamos</span><h2>Más que una tienda de materiales</h2><p>Te acompañamos en cada compra: del asesoramiento a la entrega.</p></div>
<div class="svc-grid">
<div class="svc reveal" id="envio"><div class="svc-ic">${svg('truck',26)}</div><h3>Envío a todo el país</h3><p>Despachamos a todo Uruguay por agencia o encomienda. Coordinás el envío por WhatsApp y te pasamos el costo y el tiempo estimado según tu localidad. En Montevideo y Ciudad de la Costa coordinamos entrega o retiro, muchas veces en el día.</p><a class="svc-wa" href="https://wa.me/59894726950?text=${encodeURIComponent('Hola! Quiero consultar por un envío.')}">${svg('wa',16)} Consultar envío</a></div>
<div class="svc reveal" id="marcas"><div class="svc-ic">${svg('shield',26)}</div><h3>Marcas garantizadas</h3><p>Trabajamos solo con marcas líderes del rubro eléctrico, con garantía oficial del fabricante. Cada producto es original y cumple las normas de seguridad vigentes: nada de réplicas ni materiales sin respaldo.</p></div>
<div class="svc reveal" id="asesoria"><div class="svc-ic">${svg('headset',26)}</div><h3>Asesoría técnica</h3><p>Nuestro equipo tiene más de 15 años en el rubro. Te ayudamos a elegir el material correcto para tu obra o instalación, calcular cantidades y resolver dudas técnicas. Escribinos por WhatsApp y te asesoramos sin compromiso.</p><a class="svc-wa" href="https://wa.me/59894726950?text=${encodeURIComponent('Hola! Necesito asesoramiento técnico.')}">${svg('wa',16)} Pedir asesoramiento</a></div>
<div class="svc reveal" id="stock"><div class="svc-ic">${svg('bolt',26)}</div><h3>Stock real y actualizado</h3><p>Lo que ves en la tienda es lo que tenemos. Mantenemos el inventario al día para que compres con confianza, y si algo queda bajo pedido te lo avisamos antes de confirmar.</p></div>
</div></div></section>
<section class="sec" id="contacto"><div class="wrap"><div class="band reveal">
<span class="eyebrow" style="color:#8fc0ff">Clientes y empresas</span>
<h2>¿Obra grande? Cotizamos en el día.</h2>
<p>Enviános tu lista de materiales y te armamos el presupuesto con los mejores precios por volumen.</p>
<a class="btn btn-wa btn-lg" href="https://wa.me/59894726950?text=${encodeURIComponent('Hola Larr Eléctrico! Quiero pedir una cotización.')}">${svg('wa',18)} Escribir por WhatsApp</a>
</div></div></section>`+footer+foot;

const catalogo=head('Catálogo — Larr Eléctrico','Explorá los '+data.count+' productos del catálogo de Larr Eléctrico: cables, diferenciales, térmicas, iluminación y más.')+header+`
<section class="sec" style="padding:34px 0 70px"><div class="wrap">
<div class="sec-head" style="margin-bottom:22px"><h2>Catálogo</h2><p>${data.count} productos disponibles. Filtrá por categoría, buscá o ordená por precio.</p></div>
<div class="toolbar">
<label class="search">${svg('search',18)}<input id="q" type="search" placeholder="Buscar producto, marca o código…" autocomplete="off"></label>
<div class="sel"><select id="sort"><option value="rel">Relevancia</option><option value="sold">Más vendidos</option><option value="pa">Precio: menor a mayor</option><option value="pd">Precio: mayor a menor</option><option value="az">Nombre A–Z</option></select>${svg('chev',16)}</div>
</div>
<div class="layout">
<aside class="side"><h4>Categorías</h4><div class="catlist" id="catlist"></div></aside>
<div><div class="count" id="count">Cargando…</div><div class="pgrid" id="grid"></div><div class="sentinel" id="sentinel"></div></div>
</div></div></section>`+footer+foot;

const producto=head('Producto — Larr Eléctrico','Ficha de producto — Larr Eléctrico.')+header+`<section class="sec" style="padding:20px 0 60px"><div class="wrap" id="ppRoot"><div class="pp"><div class="skel"></div><div><div class="skel" style="height:40px;aspect-ratio:auto;margin-bottom:14px"></div><div class="skel" style="height:120px;aspect-ratio:auto"></div></div></div></div></section>`+footer+foot;

const cuenta=head('Mi cuenta — Larr Eléctrico','Ingresá o creá tu cuenta en Larr Eléctrico.')+header+`<section class="sec"><div class="wrap"><div class="authwrap">
<div class="authside">
<div class="lg"><span class="bk">${svg('bolt',22)}</span>Larr Eléctrico</div>
<h2>Comprá más rápido y seguí tus pedidos</h2>
<p>Creá tu cuenta para guardar favoritos, ver tu historial de compras y cotizar en segundos.</p>
<div class="perks">
<div class="perk"><span class="pk">${svg('truck',18)}</span> Seguimiento de tus pedidos</div>
<div class="perk"><span class="pk">${svg('heart',18)}</span> Favoritos guardados en la nube</div>
<div class="perk"><span class="pk">${svg('shield',18)}</span> Datos protegidos</div>
<div class="perk"><span class="pk">${svg('bolt',18)}</span> Checkout más rápido</div>
</div></div>
<div class="authmain"><div id="authRoot"><div class="auth">
<div class="tabs"><button class="on" data-t="login">Ingresar</button><button data-t="reg">Crear cuenta</button></div>
<div class="msg" id="authMsg"></div>
<form id="formLogin">
<div class="field"><label>Email</label><input id="liEmail" type="email" placeholder="tu@email.com" autocomplete="email" required></div>
<div class="field"><label>Contraseña</label><div class="pwwrap"><input id="liPass" type="password" autocomplete="current-password" required><button type="button" class="pwtog" data-tog="liPass">${svg('eye',18)}</button></div></div>
<button class="btn btn-pri btn-block btn-lg">${svg('user',18)} Ingresar</button>
<div class="auth-alt"><a href="https://wa.me/59894726950?text=${encodeURIComponent('Hola! Necesito recuperar el acceso a mi cuenta de la tienda.')}">¿Olvidaste tu contraseña?</a></div></form>
<form id="formReg" style="display:none">
<div class="field"><label>Nombre y apellido</label><input id="rgName" placeholder="Ej. Juan Pérez" autocomplete="name" required></div>
<div class="field"><label>Email</label><input id="rgEmail" type="email" placeholder="tu@email.com" autocomplete="email" required><span class="field-hint" id="rgEmailHint"></span></div>
<div class="field"><label>Teléfono <span class="opt">(opcional)</span></label><input id="rgPhone" placeholder="099 123 456" autocomplete="tel"></div>
<div class="field"><label>Contraseña</label><div class="pwwrap"><input id="rgPass" type="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password" required><button type="button" class="pwtog" data-tog="rgPass">${svg('eye',18)}</button></div><div class="pwmeter" id="pwMeter"><span></span></div><div class="pwlabel" id="pwLabel"></div></div>
<label class="auth-terms"><input type="checkbox" id="rgTerms"><span>Acepto que Larr Eléctrico me contacte para coordinar mis pedidos y consultas.</span></label>
<button class="btn btn-pri btn-block btn-lg" id="rgBtn">${svg('check',18)} Crear mi cuenta</button>
<p class="auth-foot">${svg('lock',14)} Tus datos están protegidos y no se comparten con terceros.</p></form>
</div></div></div></div></div></section>`+footer+foot;

const favoritos=head('Favoritos — Larr Eléctrico','Tus productos favoritos en Larr Eléctrico.')+header+`<section class="sec" style="padding:34px 0 70px"><div class="wrap">
<div class="sec-head" style="margin-bottom:22px"><h2>Tus favoritos</h2><p>Guardá los productos que te interesan y pedilos cuando quieras.</p></div>
<div class="pgrid" id="favGrid"></div><div id="favEmpty"></div></div></section>`+footer+foot;

const comparador=head('Comparador — Larr Eléctrico','Compará productos de Larr Eléctrico lado a lado.')+header+`<section class="sec" style="padding:34px 0 70px"><div class="wrap">
<div class="sec-head" style="margin-bottom:22px"><h2>Comparar productos</h2><p>Mirá las especificaciones de hasta 4 productos lado a lado.</p></div>
<div id="cmpRoot"></div></div></section>`+footer+foot;

const pago=head('Pago — Larr Eléctrico','Resultado del pago de tu pedido en Larr Eléctrico.')+header+`<section class="sec" style="padding:60px 0 80px"><div class="wrap" style="max-width:620px">
<div id="payResult" class="payres"><div class="skel" style="height:260px;aspect-ratio:auto"></div></div>
</div></section>
<script>
(function(){
 var p=new URLSearchParams(location.search), st=p.get('st')||'', o=p.get('o')||'';
 var box=document.getElementById('payResult'); if(!box)return;
 var M={ok:{i:'check',c:'ok',t:'¡Pago aprobado!',d:'Recibimos tu pago correctamente. Te vamos a contactar para coordinar la entrega.'},
  pend:{i:'clock',c:'pend',t:'Pago pendiente',d:'Tu pago está siendo procesado. Apenas se acredite te avisamos.'},
  err:{i:'x',c:'err',t:'El pago no se concretó',d:'No pudimos procesar el pago. Podés intentar de nuevo o escribirnos por WhatsApp.'}};
 var m=M[st]||M.err;
 if(st==='ok'){ try{localStorage.setItem('larr_cart','[]');}catch(e){} }
 box.className='payres '+m.c;
 box.innerHTML='<div class="pay-ic"></div><h1>'+m.t+'</h1>'+(o?'<p class="pay-o">Pedido #'+o+'</p>':'')+'<p class="pay-d">'+m.d+'</p>'+
  '<div class="pay-cta"><a class="btn btn-pri btn-lg" href="index.html">Volver al inicio</a><a class="btn btn-ghost btn-lg" href="catalogo.html">Seguir comprando</a></div>';
})();
</script>`+footer+foot;

const W=(f,c)=>{fs.writeFileSync(path.join(ROOT,f),c);console.log('wrote',f,c.length);};
W('index.html',homeFull);W('catalogo.html',catalogo);W('producto.html',producto);W('cuenta.html',cuenta);W('favoritos.html',favoritos);W('comparador.html',comparador);W('pago.html',pago);
console.log('OK build',data.count,'productos, destacados',featFull.length);
