<?php require(__DIR__.'/inc.php'); require_once(__DIR__.'/totp.php'); require_admin();
$u=admin();
$row=db()->prepare("SELECT totp_enabled,totp_secret FROM users WHERE id=?"); $row->execute([$u['id']]); $me=$row->fetch();
$enabled=!empty($me['totp_enabled']);
$issuer='Larr Eléctrico Admin';
$msg=''; $msgtype='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  $act=$_POST['act']??'';
  if($act==='start' && !$enabled){
    $_SESSION['tfa_setup']=totp_secret(); // nuevo secreto pendiente
  }
  elseif($act==='confirm' && !$enabled){
    $sec=$_SESSION['tfa_setup']??''; $code=$_POST['code']??'';
    if($sec && totp_verify($sec,$code)){
      db()->prepare("UPDATE users SET totp_secret=?, totp_enabled=1 WHERE id=?")->execute([$sec,$u['id']]);
      unset($_SESSION['tfa_setup']); flash('Verificación en dos pasos activada correctamente.'); header('Location: security.php'); exit;
    } else { $msg='El código no coincide. Revisá la hora de tu teléfono y probá de nuevo.'; $msgtype='err'; }
  }
  elseif($act==='disable' && $enabled){
    $code=$_POST['code']??'';
    if(totp_verify($me['totp_secret'],$code)){
      db()->prepare("UPDATE users SET totp_secret=NULL, totp_enabled=0 WHERE id=?")->execute([$u['id']]);
      flash('Verificación en dos pasos desactivada.'); header('Location: security.php'); exit;
    } else { $msg='Código incorrecto. No se desactivó la verificación.'; $msgtype='err'; }
  }
}
$setup=$_SESSION['tfa_setup']??'';
$uri = $setup ? totp_uri($setup, $u['email'], $issuer) : '';
ah('Seguridad');
?>
<style>
.sec-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
@media(max-width:820px){.sec-grid{grid-template-columns:1fr}}
.sec-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;position:relative}
.sec-card .sh{display:flex;align-items:center;gap:12px;margin-bottom:6px}
.sec-ic{width:42px;height:42px;border-radius:12px;background:#eef4ff;color:var(--blue);display:grid;place-items:center;flex-shrink:0}
.sec-ic svg{width:21px;height:21px}
.sec-card h2{font-size:16px;font-weight:700}
.sec-card .desc{font-size:13.5px;color:var(--mut);line-height:1.55;margin:4px 0 16px}
.state{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;padding:4px 11px;border-radius:999px}
.state.on{background:#eafaf0;color:var(--ok)}.state.off{background:#f2f2f5;color:var(--mut2)}
.state .d{width:7px;height:7px;border-radius:50%;background:currentColor}
.qrwrap{display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start;margin:16px 0}
#qrbox{width:188px;height:188px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:10px;display:grid;place-items:center}
#qrbox img,#qrbox canvas{width:100%;height:100%}
.qrsteps{flex:1;min-width:220px}
.qrsteps ol{margin:0;padding-left:18px;font-size:13.5px;color:var(--mut);line-height:1.7}
.manualkey{margin-top:12px;font-size:12px;color:var(--mut2)}
.manualkey code{display:inline-block;margin-top:4px;background:var(--bg,#f5f6f8);border:1px solid var(--line);border-radius:8px;padding:7px 11px;font-size:13px;letter-spacing:.12em;font-family:ui-monospace,Menlo,monospace;color:var(--ink);word-break:break-all}
.codein{letter-spacing:.42em;text-align:center;font-weight:700;font-size:18px;max-width:200px}
.secmsg{font-size:13px;padding:10px 13px;border-radius:10px;margin-bottom:14px}
.secmsg.err{background:#fff0f1;color:#c81e3c}
</style>
<div class="sec-grid">
 <div class="sec-card">
  <div class="sh"><span class="sec-ic"><?=ico('key',21)?></span><h2>Verificación en dos pasos</h2></div>
  <?php if($msg):?><div class="secmsg <?=$msgtype?>"><?=e($msg)?></div><?php endif;?>
  <?php if($enabled): ?>
   <span class="state on"><span class="d"></span>Activada</span>
   <p class="desc" style="margin-top:14px">Tu cuenta pide un código de 6 dígitos al ingresar. Para desactivarla, ingresá un código actual de tu app.</p>
   <form method="post" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="disable">
    <input class="inp codein" name="code" inputmode="numeric" maxlength="6" placeholder="000000" required>
    <button class="btn btn-d">Desactivar 2FA</button>
   </form>
  <?php elseif($setup): ?>
   <span class="state off"><span class="d"></span>Configurando…</span>
   <p class="desc" style="margin-top:14px">Escaneá el código QR con <b>Google Authenticator</b>, <b>Authy</b> o <b>Microsoft Authenticator</b> y después ingresá el código que aparece para confirmar.</p>
   <div class="qrwrap">
    <div id="qrbox" data-uri="<?=e($uri)?>"></div>
    <div class="qrsteps">
     <ol>
      <li>Abrí tu app autenticadora.</li>
      <li>Tocá “Agregar” y escaneá el QR.</li>
      <li>Ingresá el código de 6 dígitos para confirmar.</li>
     </ol>
     <div class="manualkey">¿No podés escanear? Cargá esta clave manualmente:<br><code><?=e($setup)?></code></div>
    </div>
   </div>
   <form method="post" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="confirm">
    <input class="inp codein" name="code" inputmode="numeric" maxlength="6" placeholder="000000" autofocus required>
    <button class="btn btn-pri">Confirmar y activar</button>
    <a href="security.php" class="btn btn-g">Cancelar</a>
   </form>
  <?php else: ?>
   <span class="state off"><span class="d"></span>Desactivada</span>
   <p class="desc" style="margin-top:14px">Agregá una capa extra de seguridad: además de tu contraseña, pediremos un código temporal de tu teléfono al ingresar.</p>
   <form method="post">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="start">
    <button class="btn btn-pri"><?=ico('key',16)?> Activar verificación en dos pasos</button>
   </form>
  <?php endif; ?>
 </div>

 <div class="sec-card">
  <div class="sh"><span class="sec-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a5 5 0 0 1 5 5v3M7 10V7a5 5 0 0 1 2-4"/><rect x="5" y="10" width="14" height="11" rx="2.5"/><circle cx="12" cy="15.5" r="1.4"/></svg></span><h2>Face ID / Passkey</h2></div>
  <span class="state off"><span class="d"></span>Próximamente</span>
  <p class="desc" style="margin-top:14px">Ingreso biométrico con Face ID / huella usando una passkey del dispositivo. Lo activás una vez y entrás sin contraseña.</p>
  <button class="btn btn-g" disabled style="opacity:.6;cursor:default">En preparación</button>
 </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
 var box=document.getElementById('qrbox'); if(!box||!box.dataset.uri||!window.QRCode) return;
 try{ new QRCode(box,{text:box.dataset.uri,width:168,height:168,correctLevel:QRCode.CorrectLevel.M}); }catch(e){}
})();
</script>
<?php af();
