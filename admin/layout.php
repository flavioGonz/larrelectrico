<?php
require_once(__DIR__.'/pwagate.php'); require_once(__DIR__.'/swreg.php');
function ah($title){ $u=admin(); $cur=basename($_SERVER['PHP_SELF']);
 $nav=[['index.php','dash','Dashboard'],['products.php','box','Productos'],['bulk.php','edit','Edición masiva'],['categories.php','grid','Categorías'],['vendors.php','tag','Vendors'],['orders.php','cart','Pedidos'],['carts.php','bell','Carritos abandonados'],['reviews.php','star','Reseñas'],['customers.php','users','Clientes'],['settings.php','cog','Configuración'],['security.php','key','Seguridad']];
 ob_start(); ?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title><?=e($title)?> · Admin Larr Eléctrico</title>
<link rel="manifest" href="manifest.webmanifest">
<meta name="theme-color" content="#0b1b3a">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Larr Admin">
<link rel="apple-touch-icon" href="icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192.png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--blue:#0071e3;--navy:#0b1b3a;--ink:#1d1d1f;--mut:#6e6e73;--mut2:#86868b;--line:#e8e8ed;--bg:#f5f6f8;--ok:#1a9e4b;--amber:#ff8a00;--red:#e0245e}
*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,sans-serif;background:var(--bg);color:var(--ink);-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}button,input,select,textarea{font-family:inherit}
.app{display:grid;grid-template-columns:238px 1fr;min-height:100vh}
.side{background:var(--navy);color:#cfd8ea;padding:20px 14px;position:sticky;top:0;height:100vh}
.side .lg{display:flex;align-items:center;gap:9px;font-weight:800;font-size:18px;color:#fff;padding:6px 10px 20px}
.side .lg .b{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#0a84ff,#0071e3);display:grid;place-items:center}
.side a{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:10px;font-size:14px;color:#aeb9cf;margin-bottom:2px;transition:.15s}
.side a:hover{background:rgba(255,255,255,.07);color:#fff}
.side a.on{background:var(--blue);color:#fff}
.side a svg{width:18px;height:18px}
.side .sep{border-top:1px solid rgba(255,255,255,.1);margin:14px 6px}
.main{display:flex;flex-direction:column;min-width:0}
.top{background:#fff;border-bottom:1px solid var(--line);padding:0 26px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:20}
.top h1{font-size:19px;font-weight:700}
.top .who{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--mut)}
.top .who a{color:var(--mut);display:flex;align-items:center;gap:6px}.top .who a:hover{color:var(--ink)}
.wrapc{padding:28px 32px;max-width:1340px;width:100%;margin:0 auto}
.main{align-items:stretch}
.tip{display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;background:var(--line);color:var(--mut2);font-size:11px;font-weight:700;cursor:help;position:relative;margin-left:6px;vertical-align:middle}
.tip:hover{background:var(--blue);color:#fff}
.tip .tipbox{position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%) translateY(4px);background:var(--navy);color:#fff;font-size:12px;font-weight:400;line-height:1.4;padding:9px 12px;border-radius:9px;width:210px;text-align:left;opacity:0;visibility:hidden;transition:.18s;z-index:50;box-shadow:0 10px 30px -8px rgba(0,0,0,.4);pointer-events:none}
.tip .tipbox::after{content:"";position:absolute;top:100%;left:50%;transform:translateX(-50%);border:6px solid transparent;border-top-color:var(--navy)}
.tip:hover .tipbox{opacity:1;visibility:visible;transform:translateX(-50%)}
label{display:flex;align-items:center}
label .lbl-ic{width:15px;height:15px;color:var(--mut2);margin-right:6px}
.flash{background:#eafaf0;color:var(--ok);border:1px solid #bfe9cf;padding:12px 16px;border-radius:12px;margin-bottom:18px;font-size:14px}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.kpi{background:#fff;border:1px solid var(--line);border-radius:16px;padding:18px 20px}
.kpi .l{font-size:12.5px;color:var(--mut2);font-weight:500;display:flex;align-items:center;gap:7px}.kpi .l svg{width:15px;height:15px;color:var(--blue)}
.kpi .v{font-size:28px;font-weight:800;letter-spacing:-.02em;margin-top:8px}
.panel{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;margin-bottom:22px}
.panel .ph{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
.panel .ph h2{font-size:15px;font-weight:600}
table{width:100%;border-collapse:collapse;font-size:13.5px}
th{text-align:left;padding:11px 16px;color:var(--mut2);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--line);background:#fafafc}
td{padding:11px 16px;border-bottom:1px solid var(--line);vertical-align:middle}
tr:last-child td{border-bottom:none}tr:hover td{background:#fafbfd}
.thumb{width:40px;height:40px;border-radius:8px;object-fit:contain;background:#f2f2f5;border:1px solid var(--line);padding:3px}
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 15px;border-radius:10px;font-size:13.5px;font-weight:500;cursor:pointer;border:1px solid transparent;transition:.18s}
.btn svg{width:16px;height:16px}
.btn-pri{background:var(--blue);color:#fff}.btn-pri:hover{background:#0077ed}
.btn-g{background:#fff;border-color:var(--line);color:var(--ink)}.btn-g:hover{border-color:var(--mut2)}
.btn-d{background:#fff;border-color:var(--line);color:var(--red)}.btn-d:hover{background:#fff0f1}
.btn-sm{padding:6px 11px;font-size:12.5px}
.badge{font-size:11.5px;font-weight:600;padding:3px 9px;border-radius:999px}
.badge.ok{background:#eafaf0;color:var(--ok)}.badge.no{background:#f2f2f5;color:var(--mut2)}.badge.new{background:#e9f2ff;color:var(--blue)}.badge.am{background:#fff5e9;color:var(--amber)}
.inp{width:100%;padding:10px 13px;border:1px solid var(--line);border-radius:10px;font-size:14px;outline:none}.inp:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(0,113,227,.1)}
label{font-size:13px;font-weight:500;color:var(--mut);display:block;margin-bottom:6px}
.field{margin-bottom:15px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.toolbar{display:flex;gap:10px;margin-bottom:16px;align-items:center}
.srch{flex:1;display:flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--line);border-radius:10px;padding:9px 13px}
.srch input{border:none;outline:none;width:100%;font-size:14px}.srch svg{width:17px;height:17px;color:var(--mut2)}
.pag{display:flex;gap:6px;justify-content:center;margin-top:18px}
.pag a{padding:7px 12px;border:1px solid var(--line);border-radius:9px;font-size:13px;background:#fff}.pag a.on{background:var(--ink);color:#fff;border-color:var(--ink)}
/* ===== transiciones de sección (igual que la portada) ===== */
.wrapc{animation:admin-in .2s cubic-bezier(.22,1,.36,1) both}
@keyframes admin-in{from{opacity:0;transform:translateY(7px)}to{opacity:1;transform:none}}
body.leaving .wrapc{opacity:0;transform:translateY(-4px);transition:opacity .08s ease,transform .08s ease}
.side a,.panel,.kpi{transition:.15s}
.panel{animation:admin-in .22s cubic-bezier(.22,1,.36,1) both}
.cards .kpi{animation:admin-in .24s cubic-bezier(.22,1,.36,1) both}
.cards .kpi:nth-child(2){animation-delay:.03s}.cards .kpi:nth-child(3){animation-delay:.06s}.cards .kpi:nth-child(4){animation-delay:.09s}
/* ===== combobox con búsqueda (reemplaza selects) ===== */
.ms{position:relative}
.ms-btn{width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 13px;border:1px solid var(--line);border-radius:10px;background:#fff;font-size:14px;color:var(--ink);cursor:pointer;text-align:left;transition:.15s}
.ms-btn:hover{border-color:var(--mut2)}
.ms.on .ms-btn{border-color:var(--blue);box-shadow:0 0 0 3px rgba(0,113,227,.1)}
.ms-btn>span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1}
.ms-btn svg{width:16px;height:16px;color:var(--mut2);flex-shrink:0;transition:.2s}
.ms.on .ms-btn svg{transform:rotate(180deg);color:var(--blue)}
.ms-pan{position:absolute;top:calc(100% + 6px);left:0;right:0;min-width:220px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 16px 44px -14px rgba(0,0,0,.28);z-index:40;opacity:0;visibility:hidden;transform:translateY(-6px);transition:.16s;overflow:hidden}
.ms.on .ms-pan{opacity:1;visibility:visible;transform:none}
.ms-srch{padding:8px;border-bottom:1px solid var(--line);position:relative}
.ms-srch svg{position:absolute;left:17px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--mut2)}
.ms-srch input{width:100%;padding:8px 11px 8px 34px;border:1px solid var(--line);border-radius:8px;font-size:13.5px;outline:none}
.ms-srch input:focus{border-color:var(--blue)}
.ms-list{max-height:262px;overflow:auto;padding:6px}
.ms-op{padding:9px 11px;border-radius:8px;font-size:13.5px;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:8px}
.ms-op:hover{background:var(--bg)}
.ms-op.on{background:#eef4ff;color:var(--blue);font-weight:600}
.ms-op .ck{margin-left:auto;opacity:0}.ms-op.on .ck{opacity:1}
.ms-empty{padding:16px;text-align:center;color:var(--mut2);font-size:13px}
/* ===== PWA móvil: botón menú, overlay y tab bar (ocultos en escritorio) ===== */
.navtoggle{display:none;width:40px;height:40px;border-radius:11px;border:1px solid var(--line);background:#fff;align-items:center;justify-content:center;color:var(--ink);cursor:pointer;flex-shrink:0}
.navtoggle svg{width:20px;height:20px}
.side-ov{display:none}
.tabbar{display:none}
.top .left{display:flex;align-items:center;gap:12px;min-width:0}
.pwa-install{display:none}
@media(max-width:820px){
 .app{grid-template-columns:1fr}
 .side{position:fixed;left:0;right:0;bottom:0;top:auto;width:auto;max-height:84vh;z-index:140;transform:translateY(102%);transition:transform .36s cubic-bezier(.32,.72,0,1);height:auto;overflow:auto;-webkit-overflow-scrolling:touch;display:block;border-radius:24px 24px 0 0;padding:8px 12px calc(14px + env(safe-area-inset-bottom));box-shadow:0 -22px 60px rgba(0,0,0,.45)}
 body.nav-open .side{transform:none}
 .side::before{content:"";display:block;width:42px;height:5px;border-radius:3px;background:rgba(255,255,255,.28);margin:6px auto 10px;flex-shrink:0}
 .side .lg{display:none}
 .side a{padding:13px 14px;font-size:15px}
 .side a svg{width:20px;height:20px}
 .side .sep{margin:10px 8px}
 .side-ov{display:block;position:fixed;inset:0;background:rgba(5,10,25,.5);backdrop-filter:blur(2px);z-index:130;opacity:0;visibility:hidden;transition:.25s}
 body.nav-open .side-ov{opacity:1;visibility:visible}
 .navtoggle{display:inline-flex}
 .cards{grid-template-columns:1fr 1fr}
 .top{padding:0 15px;height:auto;min-height:56px;padding-top:max(8px,env(safe-area-inset-top))}
 .top h1{font-size:17px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
 .top .who span{display:none}
 .wrapc{padding:20px 15px calc(82px + env(safe-area-inset-bottom))}
 .tabbar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:100;background:rgba(255,255,255,.88);backdrop-filter:saturate(180%) blur(20px);-webkit-backdrop-filter:saturate(180%) blur(20px);border-top:1px solid rgba(0,0,0,.08);padding:7px 6px calc(7px + env(safe-area-inset-bottom));justify-content:space-around}
 .tabbar a{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;font-size:10px;font-weight:500;color:var(--mut2);padding:3px 0;border-radius:10px;transition:.15s;letter-spacing:.01em}
 .tabbar a svg{width:23px;height:23px;stroke-width:1.9}
 .tabbar a.on{color:var(--blue)}
 .tabbar a:active{transform:scale(.9)}
 .pwa-install{display:inline-flex}
 /* adaptar cada sección a móvil */
 .grid2{grid-template-columns:1fr}
 .wrapc{overflow-x:hidden}
 .panel{overflow:visible}
 .panel table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;white-space:nowrap}
 .toolbar{flex-wrap:wrap}
 .toolbar .btn{flex:1;justify-content:center}
 .toolbar .srch{flex-basis:100%;order:-1}
}
</style></head><body><div class="app">
<aside class="side"><div class="lg"><span class="b"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>Larr<span style="color:#7db5ff">Admin</span></div>
<?php foreach($nav as $x):?><a href="<?=$x[0]?>" class="<?=$cur===$x[0]?'on':''?>"><?=ico($x[1])?><?=$x[2]?></a><?php endforeach;?>
<div class="sep"></div><a href="../index.html" target="_blank"><?=ico('eye')?>Ver tienda</a><a href="logout.php"><?=ico('out')?>Salir</a>
</aside><div class="side-ov" onclick="document.body.classList.remove('nav-open')"></div><div class="main"><div class="top"><div class="left"><button type="button" class="navtoggle" aria-label="Menú" onclick="document.body.classList.toggle('nav-open')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button><h1><?=e($title)?></h1></div><div class="who"><span>Hola, <?=e($u['name'])?></span><button type="button" class="btn btn-g btn-sm pwa-install" id="pwaInstall" aria-label="Instalar"><?=ico('box',15)?>Instalar</button><a href="logout.php"><?=ico('out',16)?></a></div></div>
<?php $tabs=[['index.php','dash','Inicio'],['products.php','box','Productos'],['orders.php','cart','Pedidos'],['carts.php','bell','Carritos'],['settings.php','cog','Ajustes']];?>
<nav class="tabbar"><?php foreach($tabs as $t):?><a href="<?=$t[0]?>" class="<?=$cur===$t[0]?'on':''?>"><?=ico($t[1])?><span><?=$t[2]?></span></a><?php endforeach;?></nav>
<div class="wrapc">
<?php $f=flash(); if($f):?><div class="flash"><?=e($f)?></div><?php endif; echo ob_get_clean(); }
function af(){ echo '</div></div></div>
<script>
(function(){
 document.addEventListener("click",function(e){
  var a=e.target.closest("a"); if(!a)return;
  var href=a.getAttribute("href"); if(!href)return;
  if(a.target==="_blank"||a.hasAttribute("download")||href.charAt(0)==="#"||/^(https?:|mailto:|tel:|javascript:)/.test(href))return;
  if(e.metaKey||e.ctrlKey||e.shiftKey||e.button!==0)return;
  e.preventDefault(); document.body.classList.add("leaving");
  setTimeout(function(){ location.href=href; },70);
 });
 window.addEventListener("pageshow",function(ev){ if(ev.persisted)document.body.classList.remove("leaving"); });
})();
/* ===== combobox con búsqueda: mejora todos los <select class=sel2> ===== */
(function(){
 var CHEV="<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m6 9 6 6 6-6\"/></svg>";
 var CK="<svg class=\"ck\" viewBox=\"0 0 24 24\" width=\"15\" height=\"15\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2.2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M20 6 9 17l-5-5\"/></svg>";
 var SR="<svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><circle cx=\"11\" cy=\"11\" r=\"7\"/><path d=\"m21 21-4.3-4.3\"/></svg>";
 function enhance(sel){
  if(sel.dataset.enh)return; sel.dataset.enh="1"; sel.style.display="none";
  var wrap=document.createElement("div"); wrap.className="ms"; sel.parentNode.insertBefore(wrap,sel.nextSibling);
  var btn=document.createElement("button"); btn.type="button"; btn.className="ms-btn";
  var pan=document.createElement("div"); pan.className="ms-pan";
  pan.innerHTML="<div class=\"ms-srch\">"+SR+"<input type=\"text\" placeholder=\"Buscar…\"></div><div class=\"ms-list\"></div>";
  wrap.appendChild(btn); wrap.appendChild(pan);
  var list=pan.querySelector(".ms-list"), inp=pan.querySelector("input");
  function opts(){ return [].slice.call(sel.options); }
  function label(){ var o=sel.options[sel.selectedIndex]; btn.innerHTML="<span>"+(o?o.textContent:"")+"</span>"+CHEV; }
  function render(q){ q=(q||"").toLowerCase(); list.innerHTML="";
   opts().forEach(function(o){ if(q&&o.textContent.toLowerCase().indexOf(q)<0)return;
    var it=document.createElement("div"); it.className="ms-op"+(o.selected?" on":""); it.innerHTML="<span>"+o.textContent+"</span>"+CK;
    it.onclick=function(){ sel.value=o.value; try{sel.dispatchEvent(new Event("change",{bubbles:true}));}catch(e){} label(); close(); };
    list.appendChild(it); });
   if(!list.children.length)list.innerHTML="<div class=\"ms-empty\">Sin resultados</div>";
  }
  function open(){ wrap.classList.add("on"); inp.value=""; render(""); setTimeout(function(){inp.focus();},10); }
  function close(){ wrap.classList.remove("on"); }
  btn.onclick=function(e){ e.preventDefault(); wrap.classList.contains("on")?close():open(); };
  inp.oninput=function(){ render(inp.value); };
  inp.onclick=function(e){ e.stopPropagation(); };
  document.addEventListener("click",function(e){ if(!wrap.contains(e.target))close(); });
  label();
 }
 function scan(){ [].slice.call(document.querySelectorAll("select.sel2")).forEach(enhance); }
 scan();
})();
/* ===== cerrar drawer al navegar / tecla Esc ===== */
(function(){
 document.addEventListener("keydown",function(e){ if(e.key==="Escape")document.body.classList.remove("nav-open"); });
})();
/* ===== PWA: boton instalar (el registro del SW va en sw_reg) ===== */
(function(){
 var deferred=null, btn=document.getElementById("pwaInstall");
 if(btn)btn.style.display="none";
 window.addEventListener("beforeinstallprompt",function(e){ e.preventDefault(); deferred=e; if(btn){btn.style.display="inline-flex";} });
 if(btn)btn.addEventListener("click",function(){ if(!deferred)return; deferred.prompt(); deferred.userChoice.finally(function(){ deferred=null; btn.style.display="none"; }); });
 window.addEventListener("appinstalled",function(){ if(btn)btn.style.display="none"; });
})();
</script>';
pwa_gate(); sw_reg();
echo '</body></html>'; }
