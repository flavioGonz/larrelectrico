<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db(); global $CFG;
$pdo->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(60) PRIMARY KEY, v TEXT) DEFAULT CHARSET=utf8mb4");
function setng($pdo,$k,$v){ $pdo->prepare("INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$k,(string)$v]); }
require_once(__DIR__.'/../inc/fx.php');
if(isset($_GET['fx'])&&$_GET['fx']==='now'){ $f=fx_fetch_brou(); if($f){ fx_set($pdo,$f['rate'],$f['src']); flash('Cotización actualizada: '.$f['rate'].' ('.$f['src'].').'); } else { flash('No se pudo obtener la cotización ahora. Probá de nuevo o cargala manual.'); } header('Location: settings.php'); exit; }
if(isset($_GET['fxajax'])){ header('Content-Type: application/json; charset=utf-8'); $f=fx_fetch_brou(); if($f){ fx_set($pdo,$f['rate'],$f['src']); } echo json_encode(array_merge(fx_info($pdo),['ok'=>$f?true:false])); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  if(isset($_POST['fx_save'])){
    if((float)($_POST['usd_rate']??0)>0){ setng($pdo,'usd_rate',round((float)$_POST['usd_rate'],4)); setng($pdo,'usd_rate_date',date('Y-m-d')); setng($pdo,'usd_rate_src','manual'); }
    setng($pdo,'fx_auto', isset($_POST['fx_auto'])?'1':'0');
    flash('Tipo de cambio guardado.'); header('Location: settings.php'); exit;
  }
  // texto
  foreach(['site_name','wa_number','mail_from','mail_from_name','mail_reply','mp_public_key'] as $k){ if(isset($_POST[$k])) setng($pdo,$k,trim($_POST[$k])); }
  // numéricos
  if(isset($_POST['transfer_discount'])) setng($pdo,'transfer_discount', max(0,min(90,(float)$_POST['transfer_discount']))/100);
  if(isset($_POST['cart_remind_after'])) setng($pdo,'cart_remind_after', max(1,(int)$_POST['cart_remind_after']));
  // sandbox
  setng($pdo,'mp_sandbox', isset($_POST['mp_sandbox'])?'1':'0');
  // secretos: solo si se escribió algo nuevo
  foreach(['mp_access_token','mp_test_access_token'] as $k){ $v=trim($_POST[$k]??''); if($v!=='') setng($pdo,$k,$v); }
  flash('Configuración guardada. Los cambios ya están activos en la tienda.');
  header('Location: settings.php'); exit;
}
$g=fn($k,$d='')=>$CFG[$k]??$d;
$hasAcc=trim($g('mp_access_token'))!==''; $hasTest=trim($g('mp_test_access_token'))!=='';
$sandbox=(string)$g('mp_sandbox','1')==='1' || $g('mp_sandbox')===true;
$mpLive=$sandbox?$hasTest:$hasAcc;
ah('Configuración');
?>
<form method="post">
<input type="hidden" name="csrf" value="<?=csrf()?>">
<div class="grid2" style="gap:20px;align-items:start">

 <div class="panel"><div class="ph"><h2><?=ico('cog',16)?> Tienda</h2></div><div style="padding:18px 20px">
  <div class="field"><label>Nombre de la tienda</label><input class="inp" name="site_name" value="<?=e($g('site_name','Larr Eléctrico'))?>"></div>
  <div class="field"><label>WhatsApp <?=tip('Número con código de país, sin + ni espacios. Ej: 59894726950')?></label><input class="inp" name="wa_number" value="<?=e($g('wa_number'))?>" placeholder="59894726950"></div>
  <div class="field"><label>IVA</label><input class="inp" value="22% (fijo)" disabled></div>
 </div></div>

 <div class="panel"><div class="ph"><h2><?=ico('cart',16)?> Pagos</h2></div><div style="padding:18px 20px">
  <div class="field"><label>Descuento por transferencia <?=tip('Porcentaje de descuento que se aplica al pagar por transferencia o giro bancario.')?></label>
   <div style="display:flex;align-items:center;gap:8px"><input class="inp" name="transfer_discount" type="number" step="0.5" min="0" max="90" value="<?=e(round((float)$g('transfer_discount',0.10)*100,1))?>" style="width:120px"><span style="color:var(--mut)">%</span></div></div>
  <p style="font-size:12.5px;color:var(--mut2);line-height:1.6;margin:0">Mercado Pago no lleva recargo (recargar por tarjeta es ilegal en Uruguay). Los medios de pago y cuentas bancarias se editan en <code>config.php</code>.</p>
 </div></div>

 <div class="panel" style="grid-column:1/-1"><div class="ph"><h2><?=ico('key',16)?> Mercado Pago</h2>
   <span class="badge <?=$mpLive?'ok':'am'?>"><?=$mpLive?'Activo':'Sin configurar'?></span></div>
  <div style="padding:18px 20px">
   <div class="grid2" style="gap:16px">
    <div class="field"><label>Public Key <?=tip('La encontrás en Mercado Pago → Tu negocio → Configuración → Credenciales. Empieza con APP_USR-')?></label><input class="inp" name="mp_public_key" value="<?=e($g('mp_public_key'))?>" placeholder="APP_USR-..."></div>
    <div class="field"><label>Modo</label>
     <label style="display:flex;align-items:center;gap:10px;height:42px;cursor:pointer"><input type="checkbox" name="mp_sandbox" <?=$sandbox?'checked':''?>> <span>Modo prueba (sandbox)</span> <?=tip('Activado: usa las credenciales TEST y no cobra de verdad. Desactivá para cobrar en producción.')?></label></div>
    <div class="field"><label>Access Token de producción <span class="badge <?=$hasAcc?'ok':'no'?>" style="font-size:10px"><?=$hasAcc?'Cargado':'Vacío'?></span> <?=tip('SECRETO. Solo se guarda en el servidor. Dejalo vacío para conservar el actual.')?></label><input class="inp" name="mp_access_token" type="password" placeholder="<?=$hasAcc?'•••••••• (dejar vacío para conservar)':'APP_USR-...'?>" autocomplete="off"></div>
    <div class="field"><label>Access Token de prueba <span class="badge <?=$hasTest?'ok':'no'?>" style="font-size:10px"><?=$hasTest?'Cargado':'Vacío'?></span></label><input class="inp" name="mp_test_access_token" type="password" placeholder="<?=$hasTest?'•••••••• (dejar vacío para conservar)':'TEST-...'?>" autocomplete="off"></div>
   </div>
   <p style="font-size:12.5px;color:var(--mut2);line-height:1.6;margin:8px 0 0"><?=ico('eye',13)?> Al cargar las credenciales, el botón "Pagar con Mercado Pago" se activa solo en el checkout. Recomendado: probar primero en modo prueba.</p>
  </div></div>

 <div class="panel"><div class="ph"><h2><?=ico('bell',16)?> Correo</h2></div><div style="padding:18px 20px">
  <div class="field"><label>Remitente (From) <?=tip('Usá una dirección @larrelectrico.uy para que los correos pasen SPF/DKIM y no caigan en spam.')?></label><input class="inp" name="mail_from" value="<?=e($g('mail_from'))?>" placeholder="ventas@larrelectrico.uy"></div>
  <div class="field"><label>Nombre del remitente</label><input class="inp" name="mail_from_name" value="<?=e($g('mail_from_name'))?>"></div>
  <div class="field"><label>Responder a (Reply-To) <?=tip('A dónde llegan las respuestas de los clientes. Puede ser tu Gmail.')?></label><input class="inp" name="mail_reply" value="<?=e($g('mail_reply'))?>" placeholder="tucorreo@gmail.com"></div>
 </div></div>

 <div class="panel"><div class="ph"><h2><?=ico('bell',16)?> Carrito abandonado</h2></div><div style="padding:18px 20px">
  <div class="field"><label>Enviar recordatorio después de <?=tip('Horas que espera el sistema antes de enviar el correo de recordatorio de carrito.')?></label>
   <div style="display:flex;align-items:center;gap:8px"><input class="inp" name="cart_remind_after" type="number" min="1" value="<?=e((int)$g('cart_remind_after',2))?>" style="width:120px"><span style="color:var(--mut)">horas</span></div></div>
  <p style="font-size:12.5px;color:var(--mut2);line-height:1.6;margin:0">El cron corre cada hora. Lo seguís desde <a href="carts.php" style="color:var(--blue)">Carritos abandonados</a>.</p>
 </div></div>

</div>
<div style="margin-top:22px;display:flex;justify-content:flex-end"><button class="btn btn-pri" style="padding:12px 26px"><?=ico('save',16)?>Guardar configuración</button></div>
</form>
<?php $fx=fx_info($pdo); ?>
<form method="post" style="margin-top:20px">
<input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="fx_save" value="1">
<div class="panel"><div class="ph"><h2><?=ico('card',16)?> Tipo de cambio (USD → UYU)</h2>
 <span class="badge <?=$fx['auto']?'ok':'am'?>"><?=$fx['auto']?'Automático':'Manual'?></span></div>
 <div style="padding:18px 20px">
  <div class="grid2" style="gap:16px">
   <div class="field"><label>Cotización del dólar (venta) <?=tip('Pesos por dólar. Se usa para mostrar y cobrar los productos cargados en USD.')?></label>
    <div style="display:flex;align-items:center;gap:8px"><span style="color:var(--mut)">$</span><input class="inp" name="usd_rate" type="number" step="0.0001" min="0" value="<?=e($fx['rate'])?>" style="width:150px"></div></div>
   <div class="field"><label>Actualización automática</label>
    <label style="display:flex;align-items:center;gap:10px;height:42px;cursor:pointer"><input type="checkbox" name="fx_auto" <?=$fx['auto']?'checked':''?>> <span>Diaria desde el Banco República</span></label></div>
  </div>
  <p style="font-size:12.5px;color:var(--mut2);line-height:1.6;margin:4px 0 14px">Fuente: <b><?=e($fx['src']?:'—')?></b> · Última actualización: <b><?=e($fx['date']?:'—')?></b>. Con "automático" se toma el <b>dólar billete venta del BROU</b> una vez por día. Si cargás un valor a mano, queda fijo hasta la próxima actualización.</p>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
   <button class="btn btn-pri"><?=ico('save',16)?>Guardar tipo de cambio</button>
   <button type="button" class="btn btn-g" id="fxGo"><?=ico('bolt',16)?>Actualizar ahora desde el BROU</button>
  </div>
 </div></div>
</form>

<div class="fxov" id="fxov" aria-hidden="true"><div class="fxcard" id="fxcard">
 <div class="fxscene">
  <div class="fxtrack"></div>
  <div class="fxnode a"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9M4 9h16v11H4zM9 20v-6h6v6"/></svg></div>
  <div class="fxnode b"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10 12 4l9 6M5 10v9M19 10v9M9 10v9M15 10v9M3 21h18"/></svg></div>
  <div class="fxcoin">$</div>
 </div>
 <div class="fxcheck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
 <div class="fxtitle" id="fxtitle">Buscando la cotización</div>
 <div class="fxrate" id="fxrate">$ 0,00</div>
 <div class="fxstep" id="fxstep">Conectando con el Banco República…</div>
 <div class="fxmeta" id="fxmeta"></div>
 <div class="fxbar"><i></i></div>
 <button type="button" class="fxbtn" id="fxClose">Listo</button>
</div></div>

<style>
.fxov{position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;padding:22px;background:rgba(9,16,32,.62);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);opacity:0;visibility:hidden;transition:opacity .22s}
.fxov.on{opacity:1;visibility:visible}
.fxcard{width:380px;max-width:100%;background:#fff;border-radius:22px;padding:30px 28px 24px;box-shadow:0 30px 80px -20px rgba(0,0,0,.5);text-align:center;overflow:hidden;transform:translateY(10px) scale(.98);transition:transform .26s cubic-bezier(.2,1,.3,1)}
.fxov.on .fxcard{transform:none}
.fxscene{position:relative;height:96px;margin:4px 0 20px}
.fxtrack{position:absolute;left:46px;right:46px;top:50%;height:2px;background:repeating-linear-gradient(90deg,var(--line) 0 7px,transparent 7px 14px);transform:translateY(-50%)}
.fxnode{position:absolute;top:50%;transform:translateY(-50%);width:62px;height:62px;border-radius:18px;display:grid;place-items:center;background:var(--bg);border:1px solid var(--line);color:var(--navy)}
.fxnode.a{left:0}.fxnode.b{right:0;background:#eef4ff;border-color:#cfe0fb;color:var(--blue)}
.fxnode svg{width:28px;height:28px}
.fxnode.b::after{content:"";position:absolute;inset:-6px;border-radius:22px;border:2px solid var(--blue);opacity:0;animation:fxpulse 1.6s ease-out infinite}
@keyframes fxpulse{0%{transform:scale(.8);opacity:.55}100%{transform:scale(1.25);opacity:0}}
.fxcoin{position:absolute;top:50%;width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#ffd45e,#f5a623);box-shadow:0 4px 12px rgba(245,166,35,.5);display:grid;place-items:center;color:#7a4b00;font-weight:800;font-size:13px;animation:fxtravel 1.5s cubic-bezier(.65,0,.35,1) infinite}
@keyframes fxtravel{0%{left:52px;transform:translateY(-50%) scale(.6);opacity:0}15%{opacity:1;transform:translateY(-50%) scale(1)}50%{left:calc(100% - 72px)}60%{opacity:1}74%{opacity:0;transform:translateY(-50%) scale(.6)}100%{opacity:0}}
.fxtitle{font-size:18px;font-weight:800;letter-spacing:-.3px;color:var(--ink);margin-bottom:5px}
.fxstep{font-size:13.5px;color:var(--mut);min-height:19px;transition:opacity .3s}
.fxbar{height:6px;border-radius:999px;background:var(--line);overflow:hidden;margin:18px 2px 0}
.fxbar i{display:block;height:100%;width:38%;border-radius:999px;background:linear-gradient(90deg,var(--blue),#4aa3ff);animation:fxload 1.5s cubic-bezier(.65,0,.35,1) infinite}
@keyframes fxload{0%{transform:translateX(-130%)}60%,100%{transform:translateX(360%)}}
.fxcheck{width:66px;height:66px;border-radius:50%;background:#eafaf0;color:var(--ok);display:none;place-items:center;margin:6px auto 14px;animation:fxpop .4s cubic-bezier(.2,1.3,.4,1) both}
.fxcheck svg{width:34px;height:34px}
.fxrate{display:none;font-size:40px;font-weight:800;letter-spacing:-.02em;color:var(--ink);margin:2px 0 2px;font-variant-numeric:tabular-nums}
.fxmeta{display:none;font-size:12.5px;color:var(--mut2);margin-bottom:18px}
.fxbtn{display:none;width:100%;justify-content:center;align-items:center;gap:7px;background:var(--blue);color:#fff;border:0;padding:13px;border-radius:12px;font-size:14.5px;font-weight:600;cursor:pointer}
.fxbtn:hover{background:#0077ed}
.fxcard.fxok .fxscene,.fxcard.fxok .fxbar{display:none}
.fxcard.fxok .fxcheck{display:grid}
.fxcard.fxok .fxrate,.fxcard.fxok .fxmeta,.fxcard.fxok .fxbtn{display:block}
.fxcard.fxok .fxbtn{display:flex}
.fxcard.fxok .fxstep{display:none}
.fxcard.fxerr .fxscene,.fxcard.fxerr .fxbar{display:none}
.fxcard.fxerr .fxcheck{display:grid;background:#fdeef3;color:var(--red)}
.fxcard.fxerr .fxbtn{display:flex;background:var(--mut)}
</style>
<script>
(function(){
 var go=document.getElementById('fxGo'); if(!go)return;
 var ov=document.getElementById('fxov'), card=document.getElementById('fxcard');
 var titleEl=document.getElementById('fxtitle'), stepEl=document.getElementById('fxstep'),
     rateEl=document.getElementById('fxrate'), metaEl=document.getElementById('fxmeta'),
     closeBtn=document.getElementById('fxClose');
 var steps=['Conectando con el Banco República…','Leyendo la cotización del día…','Confirmando el valor…'];
 var fmt=function(n){return new Intl.NumberFormat('es-UY',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n);};
 function close(){ ov.classList.remove('on'); }
 go.addEventListener('click',function(){
   card.classList.remove('fxok','fxerr'); titleEl.textContent='Buscando la cotización'; stepEl.textContent=steps[0];
   ov.classList.add('on');
   var i=0, cyc=setInterval(function(){ i++; if(i<steps.length)stepEl.textContent=steps[i]; },1100);
   var t0=Date.now();
   fetch('settings.php?fxajax=1',{headers:{'X-Requested-With':'fetch'}}).then(function(r){return r.json();}).then(function(d){
     var wait=Math.max(0,2300-(Date.now()-t0));
     setTimeout(function(){ clearInterval(cyc); (d&&d.ok)?done(d):fail(); },wait);
   }).catch(function(){ clearInterval(cyc); fail(); });
 });
 function done(d){
   titleEl.textContent='Cotización actualizada';
   metaEl.textContent=(d.src||'BROU')+' · '+(d.date||'hoy');
   card.classList.add('fxok');
   var inp=document.querySelector('[name=usd_rate]');
   var target=parseFloat(d.rate)||0, start=inp?(parseFloat(inp.value)||target):target, s0=Date.now(), dur=800;
   (function tick(){ var p=Math.min(1,(Date.now()-s0)/dur); var v=start+(target-start)*(1-Math.pow(1-p,3));
     rateEl.textContent='$ '+fmt(v); if(p<1)requestAnimationFrame(tick); else rateEl.textContent='$ '+fmt(target); })();
   if(inp)inp.value=target;
   var badge=document.querySelector('#fxBadge'); if(badge){badge.textContent='Automático';badge.className='badge ok';}
 }
 function fail(){ card.classList.add('fxerr'); titleEl.textContent='No se pudo obtener'; metaEl.textContent='Probá de nuevo o cargá el valor a mano.'; }
 closeBtn.addEventListener('click',close);
 ov.addEventListener('click',function(e){ if(e.target===ov && !card.classList.contains('')) close(); });
})();
</script>
<?php af();
