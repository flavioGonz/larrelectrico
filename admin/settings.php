<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db(); global $CFG;
$pdo->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(60) PRIMARY KEY, v TEXT) DEFAULT CHARSET=utf8mb4");
function setng($pdo,$k,$v){ $pdo->prepare("INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$k,(string)$v]); }
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
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
<?php af();
