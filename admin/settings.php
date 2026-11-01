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
    flash('Tipo de cambio guardado.'); header('Location: settings.php#cambio'); exit;
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
$fx=fx_info($pdo);
$waOK=trim($g('wa_number'))!==''; $mailOK=trim($g('mail_from'))!=='';
$cartH=(int)$g('cart_remind_after',2);
ah('Configuración');
?>
<style>
.cfg .status{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px}
.cfg .stchip{display:flex;align-items:center;gap:11px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:13px 16px}
.cfg .stchip .ic{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;flex-shrink:0}
.cfg .stchip .ic svg{width:18px;height:18px}
.cfg .stchip.ok .ic{background:#eafaf0;color:var(--ok)} .cfg .stchip.am .ic{background:#fff6e6;color:#b7791f}
.cfg .stchip .sl{font-size:11px;color:var(--mut2);font-weight:500} .cfg .stchip .sv{font-size:14.5px;font-weight:700;margin-top:1px;letter-spacing:-.2px}
.cfg .tabnav{display:flex;gap:2px;border-bottom:1px solid var(--line);margin-bottom:22px;overflow-x:auto}
.cfg .tabnav button{display:inline-flex;align-items:center;gap:8px;padding:11px 17px;font-size:14px;font-weight:500;color:var(--mut);background:none;border:0;border-bottom:2px solid transparent;cursor:pointer;white-space:nowrap;margin-bottom:-1px}
.cfg .tabnav button svg{width:16px;height:16px}
.cfg .tabnav button:hover{color:var(--ink)}
.cfg .tabnav button.on{color:var(--blue);border-bottom-color:var(--blue);font-weight:600}
.cfg .pane{display:none} .cfg .pane.on{display:block;animation:cfgfade .25s ease}
@keyframes cfgfade{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
.cfg .grid2{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.cfg .grid2 .panel{height:100%}
.cfg .presets{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:12px}
.cfg .chip{padding:8px 15px;border-radius:10px;border:1px solid var(--line);background:#fff;font-size:13px;font-weight:600;color:var(--mut);cursor:pointer;font-variant-numeric:tabular-nums;transition:.15s}
.cfg .chip:hover{border-color:var(--blue);color:var(--blue)}
.cfg .chip.on{background:var(--blue);border-color:var(--blue);color:#fff}
.cfg .cartnote{padding:12px 14px;background:#eef4ff;border:1px solid #d4e4fb;border-radius:11px;font-size:13px;color:#1b3a6b;line-height:1.5;display:flex;gap:9px;align-items:flex-start}
.cfg .cartnote svg{width:16px;height:16px;flex-shrink:0;margin-top:1px}
.cfg .savebar{margin-top:22px;display:flex;justify-content:flex-end}
@media(max-width:900px){.cfg .status{grid-template-columns:1fr 1fr}.cfg .grid2{grid-template-columns:1fr}}
</style>
<div class="cfg">

<div class="status">
 <div class="stchip <?=$waOK?'ok':'am'?>"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h5l2 5-3 2a14 14 0 0 0 6 6l2-3 5 2v5a2 2 0 0 1-2 2A18 18 0 0 1 2 6a2 2 0 0 1 2-2z"/></svg></div><div><div class="sl">WhatsApp</div><div class="sv"><?=$waOK?'Configurado':'Falta'?></div></div></div>
 <div class="stchip <?=$mpLive?'ok':'am'?>"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div><div><div class="sl">Mercado Pago</div><div class="sv"><?=$mpLive?($sandbox?'Prueba':'Activo'):'Sin configurar'?></div></div></div>
 <div class="stchip <?=$fx['auto']?'ok':'am'?>"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="sl">Dólar <?=$fx['auto']?'(auto)':'(manual)'?></div><div class="sv">$<?=e(number_format((float)$fx['rate'],2,',','.'))?></div></div></div>
 <div class="stchip <?=$mailOK?'ok':'am'?>"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></div><div><div class="sl">Correo</div><div class="sv"><?=$mailOK?'Configurado':'Falta'?></div></div></div>
</div>

<div class="tabnav">
 <button type="button" data-t="tienda" class="on"><?=ico('cog',16)?>Tienda</button>
 <button type="button" data-t="pagos"><?=ico('card',16)?>Pagos</button>
 <button type="button" data-t="correo"><?=ico('bell',16)?>Correo</button>
 <button type="button" data-t="cambio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Tipo de cambio</button>
</div>

<form method="post">
<input type="hidden" name="csrf" value="<?=csrf()?>">

 <div class="pane on" data-pane="tienda">
  <div class="grid2">
   <div class="panel"><div class="ph"><h2><?=ico('cog',16)?> Datos de la tienda</h2></div><div style="padding:18px 20px">
    <div class="field"><label>Nombre de la tienda</label><input class="inp" name="site_name" value="<?=e($g('site_name','Larr Eléctrico'))?>"></div>
    <div class="field"><label>WhatsApp <?=tip('Número con código de país, sin + ni espacios. Ej: 59894726950')?></label><input class="inp" name="wa_number" value="<?=e($g('wa_number'))?>" placeholder="59894726950"></div>
    <div class="field" style="margin-bottom:0"><label>IVA</label><input class="inp" value="22% (fijo)" disabled></div>
   </div></div>
   <div class="panel"><div class="ph"><h2><?=ico('bell',16)?> Carrito abandonado</h2></div><div style="padding:18px 20px">
    <div class="field"><label>Enviar el recordatorio después de <?=tip('Tiempo que espera el sistema antes de mandar el correo de recordatorio.')?></label>
     <div class="presets" id="cartPresets">
      <?php foreach([1,2,6,24,48] as $hh):?><button type="button" class="chip <?=$cartH==$hh?'on':''?>" data-h="<?=$hh?>"><?=$hh<24?$hh.' h':($hh/24).' día'.($hh>24?'s':'')?></button><?php endforeach;?>
     </div>
     <div style="display:flex;align-items:center;gap:8px"><input class="inp" name="cart_remind_after" id="cartInp" type="number" min="1" value="<?=$cartH?>" style="width:110px"><span style="color:var(--mut)">horas</span></div>
    </div>
    <div class="cartnote"><?=ico('bell',15)?><span id="cartHint"></span></div>
    <p style="font-size:12px;color:var(--mut2);line-height:1.6;margin:10px 0 0">El cron corre cada hora. Lo seguís desde <a href="carts.php" style="color:var(--blue)">Carritos abandonados</a>.</p>
   </div></div>
  </div>
  <div class="savebar"><button class="btn btn-pri" style="padding:12px 26px"><?=ico('save',16)?>Guardar configuración</button></div>
 </div>

 <div class="pane" data-pane="pagos">
  <div class="panel"><div class="ph"><h2><?=ico('card',16)?> Descuento por transferencia</h2></div><div style="padding:18px 20px">
   <div class="field" style="margin-bottom:10px"><label>Porcentaje de descuento <?=tip('Se aplica al pagar por transferencia o giro bancario.')?></label>
    <div style="display:flex;align-items:center;gap:8px"><input class="inp" name="transfer_discount" type="number" step="0.5" min="0" max="90" value="<?=e(round((float)$g('transfer_discount',0.10)*100,1))?>" style="width:120px"><span style="color:var(--mut)">%</span></div></div>
   <p style="font-size:12.5px;color:var(--mut2);line-height:1.6;margin:0">Mercado Pago no lleva recargo (recargar por tarjeta es ilegal en Uruguay). Los medios de pago y cuentas bancarias se editan en <code>config.php</code>.</p>
  </div></div>

  <div class="panel" style="margin-top:18px"><div class="ph"><h2><?=ico('key',16)?> Mercado Pago</h2>
    <span class="badge <?=$mpLive?'ok':'am'?>"><?=$mpLive?'Activo':'Sin configurar'?></span></div>
   <div style="padding:18px 20px">
    <div class="grid2" style="gap:16px">
     <div class="field"><label>Public Key <?=tip('Mercado Pago → Tu negocio → Configuración → Credenciales. Empieza con APP_USR-')?></label><input class="inp" name="mp_public_key" value="<?=e($g('mp_public_key'))?>" placeholder="APP_USR-..."></div>
     <div class="field"><label>Modo</label>
      <label style="display:flex;align-items:center;gap:10px;height:42px;cursor:pointer"><input type="checkbox" name="mp_sandbox" <?=$sandbox?'checked':''?>> <span>Modo prueba (sandbox)</span> <?=tip('Activado: usa las credenciales TEST y no cobra de verdad.')?></label></div>
     <div class="field"><label>Access Token de producción <span class="badge <?=$hasAcc?'ok':'no'?>" style="font-size:10px"><?=$hasAcc?'Cargado':'Vacío'?></span> <?=tip('SECRETO. Solo se guarda en el servidor. Dejalo vacío para conservar el actual.')?></label><input class="inp" name="mp_access_token" type="password" placeholder="<?=$hasAcc?'•••••••• (dejar vacío para conservar)':'APP_USR-...'?>" autocomplete="off"></div>
     <div class="field"><label>Access Token de prueba <span class="badge <?=$hasTest?'ok':'no'?>" style="font-size:10px"><?=$hasTest?'Cargado':'Vacío'?></span></label><input class="inp" name="mp_test_access_token" type="password" placeholder="<?=$hasTest?'•••••••• (dejar vacío para conservar)':'TEST-...'?>" autocomplete="off"></div>
    </div>
    <p style="font-size:12.5px;color:var(--mut2);line-height:1.6;margin:8px 0 0"><?=ico('eye',13)?> Al cargar las credenciales, el botón "Pagar con Mercado Pago" se activa solo en el checkout.</p>
   </div></div>
  <div class="savebar"><button class="btn btn-pri" style="padding:12px 26px"><?=ico('save',16)?>Guardar configuración</button></div>
 </div>

 <div class="pane" data-pane="correo">
  <div class="panel"><div class="ph"><h2><?=ico('bell',16)?> Correo</h2></div><div style="padding:18px 20px">
   <div class="grid2" style="gap:16px">
    <div class="field"><label>Remitente (From) <?=tip('Usá una dirección @larrelectrico.uy para pasar SPF/DKIM y no caer en spam.')?></label><input class="inp" name="mail_from" value="<?=e($g('mail_from'))?>" placeholder="ventas@larrelectrico.uy"></div>
    <div class="field"><label>Nombre del remitente</label><input class="inp" name="mail_from_name" value="<?=e($g('mail_from_name'))?>"></div>
    <div class="field" style="grid-column:1/-1"><label>Responder a (Reply-To) <?=tip('A dónde llegan las respuestas de los clientes. Puede ser tu Gmail.')?></label><input class="inp" name="mail_reply" value="<?=e($g('mail_reply'))?>" placeholder="tucorreo@gmail.com"></div>
   </div>
  </div></div>
  <div class="savebar"><button class="btn btn-pri" style="padding:12px 26px"><?=ico('save',16)?>Guardar configuración</button></div>
 </div>

</form>

<div class="pane" data-pane="cambio">
<form method="post">
<input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="fx_save" value="1">
<div class="panel"><div class="ph"><h2><?=ico('card',16)?> Tipo de cambio (USD → UYU)</h2>
 <span class="badge <?=$fx['auto']?'ok':'am'?>" id="fxBadge"><?=$fx['auto']?'Automático':'Manual'?></span></div>
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
</div>

</div>

<div class="fxov" id="fxov" aria-hidden="true"><div class="fxcard" id="fxcard">
 <div class="fxscene">
  <div class="fxtrack"></div>
  <div class="fxnode a"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9M4 9h16v11H4zM9 20v-6h6v6"/></svg></div>
  <div class="fxnode b"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10 12 4l9 6M5 10v9M19 10v9M9 10v9M15 10v9M3 21h18"/></svg></div>
  <div class="fxcoin">$</div>
  <div class="fxcoin c2">$</div>
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
.fxcoin{position:absolute;top:50%;left:56px;width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#ffd45e,#f5a623);box-shadow:0 4px 12px rgba(245,166,35,.5);display:grid;place-items:center;color:#7a4b00;font-weight:800;font-size:13px;animation:fxtravel 1.7s cubic-bezier(.4,0,.2,1) infinite}
.fxcoin.c2{animation-delay:.85s}
@keyframes fxtravel{0%{left:56px;opacity:0;transform:translateY(-50%) scale(.4)}14%{opacity:1;transform:translateY(-50%) scale(1)}66%{left:calc(100% - 80px);opacity:1;transform:translateY(-50%) scale(1)}80%{left:calc(100% - 80px);opacity:0;transform:translateY(-50%) scale(.5)}100%{opacity:0}}
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
 // ---- tabs ----
 var nav=document.querySelector('.cfg .tabnav');
 if(nav){
  var btns=nav.querySelectorAll('button'), panes=document.querySelectorAll('.cfg .pane');
  function show(t){ btns.forEach(function(b){b.classList.toggle('on',b.dataset.t===t);});
    panes.forEach(function(p){p.classList.toggle('on',p.dataset.pane===t);});
    try{history.replaceState(null,'','#'+t);}catch(e){} }
  btns.forEach(function(b){ b.addEventListener('click',function(){show(b.dataset.t);}); });
  var h=(location.hash||'').replace('#',''); if(h&&document.querySelector('.cfg .pane[data-pane='+h+']'))show(h);
 }
 // ---- carrito presets ----
 var inp=document.getElementById('cartInp'), hint=document.getElementById('cartHint'),
     chips=document.querySelectorAll('#cartPresets .chip');
 function human(h){ h=parseInt(h,10)||0; if(h<24)return h+(h==1?' hora':' horas'); var d=Math.round(h/24*10)/10; return d+(d==1?' día':' días'); }
 function refreshHint(){ if(hint)hint.textContent='Se enviará el recordatorio '+human(inp.value)+' después de que el cliente abandone el carrito.';
   chips.forEach(function(c){c.classList.toggle('on',parseInt(c.dataset.h,10)==parseInt(inp.value,10));}); }
 if(inp){ chips.forEach(function(c){ c.addEventListener('click',function(){ inp.value=c.dataset.h; refreshHint(); }); });
   inp.addEventListener('input',refreshHint); refreshHint(); }
 // ---- fx splash ----
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
   var fi=document.querySelector('[name=usd_rate]');
   var target=parseFloat(d.rate)||0, start=fi?(parseFloat(fi.value)||target):target, s0=Date.now(), dur=800;
   (function tick(){ var p=Math.min(1,(Date.now()-s0)/dur); var v=start+(target-start)*(1-Math.pow(1-p,3));
     rateEl.textContent='$ '+fmt(v); if(p<1)requestAnimationFrame(tick); else rateEl.textContent='$ '+fmt(target); })();
   if(fi)fi.value=target;
   var badge=document.getElementById('fxBadge'); if(badge){badge.textContent='Automático';badge.className='badge ok';}
 }
 function fail(){ card.classList.add('fxerr'); titleEl.textContent='No se pudo obtener'; metaEl.textContent='Probá de nuevo o cargá el valor a mano.'; }
 closeBtn.addEventListener('click',close);
 ov.addEventListener('click',function(e){ if(e.target===ov) close(); });
})();
</script>
<?php af();
