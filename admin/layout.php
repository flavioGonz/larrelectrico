<?php
function ah($title){ $u=admin(); $cur=basename($_SERVER['PHP_SELF']);
 $nav=[['index.php','dash','Dashboard'],['products.php','box','Productos'],['bulk.php','edit','Edición masiva'],['categories.php','grid','Categorías'],['vendors.php','tag','Vendors'],['orders.php','cart','Pedidos'],['carts.php','bell','Carritos abandonados'],['reviews.php','star','Reseñas'],['customers.php','users','Clientes']];
 ob_start(); ?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> · Admin Larr Eléctrico</title>
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
@media(max-width:820px){.app{grid-template-columns:1fr}.side{display:none}.cards{grid-template-columns:1fr 1fr}}
/* ===== transiciones de sección (igual que la portada) ===== */
.wrapc{animation:admin-in .36s cubic-bezier(.16,1,.3,1) both}
@keyframes admin-in{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
body.leaving .wrapc{opacity:0;transform:translateY(-7px);transition:opacity .14s ease,transform .14s ease}
.side a,.panel,.kpi{transition:.18s}
.panel{animation:admin-in .42s cubic-bezier(.16,1,.3,1) both}
.cards .kpi{animation:admin-in .44s cubic-bezier(.16,1,.3,1) both}
.cards .kpi:nth-child(2){animation-delay:.05s}.cards .kpi:nth-child(3){animation-delay:.1s}.cards .kpi:nth-child(4){animation-delay:.15s}
</style></head><body><div class="app">
<aside class="side"><div class="lg"><span class="b"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>Larr<span style="color:#7db5ff">Admin</span></div>
<?php foreach($nav as $x):?><a href="<?=$x[0]?>" class="<?=$cur===$x[0]?'on':''?>"><?=ico($x[1])?><?=$x[2]?></a><?php endforeach;?>
<div class="sep"></div><a href="../index.html" target="_blank"><?=ico('eye')?>Ver tienda</a><a href="logout.php"><?=ico('out')?>Salir</a>
</aside><div class="main"><div class="top"><h1><?=e($title)?></h1><div class="who"><span>Hola, <?=e($u['name'])?></span><a href="logout.php"><?=ico('out',16)?></a></div></div><div class="wrapc">
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
  setTimeout(function(){ location.href=href; },150);
 });
 window.addEventListener("pageshow",function(ev){ if(ev.persisted)document.body.classList.remove("leaving"); });
})();
</script>
</body></html>'; }
