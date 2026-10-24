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
  <span class="state off" id="waState"><span class="d"></span>Comprobando…</span>
  <p class="desc" style="margin-top:14px">Ingreso biométrico con Face ID / huella usando una passkey del dispositivo. Registralo una vez y entrás sin contraseña.</p>
  <div id="waList" style="margin:0 0 12px"></div>
  <button class="btn btn-pri" id="waAdd"><?=ico('key',16)?> Registrar Face ID en este dispositivo</button>
  <div id="waHint" class="desc" style="margin-top:12px;display:none"></div>
 </div>

 <div class="sec-card">
  <div class="sh"><span class="sec-ic"><?=ico('bell',21)?></span><h2>Notificaciones de compras</h2></div>
  <span class="state off" id="pushState"><span class="d"></span>Comprobando…</span>
  <p class="desc" style="margin-top:14px">Recibí un aviso en tu teléfono cada vez que entra una compra, incluso con la app cerrada.</p>
  <button class="btn btn-pri" id="pushBtn"><?=ico('bell',16)?> Activar notificaciones</button>
  <div id="pushHint" class="desc" style="margin-top:12px;display:none"></div>
 </div>
</div>
<script>
(function(){
 var btn=document.getElementById('pushBtn'), st=document.getElementById('pushState'), hint=document.getElementById('pushHint');
 function setState(t,on){ if(st){ st.className='state '+(on?'on':'off'); st.innerHTML='<span class="d"></span>'+t; } }
 if(!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)){
   setState('No disponible aquí',false); if(btn){btn.disabled=true;btn.style.opacity='.6';} if(hint){hint.style.display='block';hint.textContent='Instalá y abrí el panel como app (PWA) para activar las notificaciones.';} return;
 }
 function u8(b64){ var pad='='.repeat((4-b64.length%4)%4); var s=(b64+pad).replace(/-/g,'+').replace(/_/g,'/'); var raw=atob(s); var a=new Uint8Array(raw.length); for(var i=0;i<raw.length;i++)a[i]=raw.charCodeAt(i); return a; }
 function idbSet(k,v){ return new Promise(function(res){ try{ var o=indexedDB.open('larradmin',1); o.onupgradeneeded=function(){ try{o.result.createObjectStore('kv');}catch(e){} }; o.onsuccess=function(){ try{ var tx=o.result.transaction('kv','readwrite'); tx.objectStore('kv').put(v,k); tx.oncomplete=function(){res(true);}; tx.onerror=function(){res(false);}; }catch(e){res(false);} }; o.onerror=function(){res(false);}; }catch(e){res(false);} }); }
 function refresh(){ navigator.serviceWorker.ready.then(function(reg){ reg.pushManager.getSubscription().then(function(s){ if(s){ setState('Activadas',true); if(btn){btn.innerHTML='Desactivar notificaciones'; btn.setAttribute('data-on','1'); btn.className='btn btn-g';} } else { setState('Desactivadas',false); } }).catch(function(){ setState('Desactivadas',false); }); }); }
 function enable(){
   Notification.requestPermission().then(function(perm){
     if(perm!=='granted'){ setState('Permiso denegado',false); if(hint){hint.style.display='block';hint.textContent='Activá los permisos de notificación para este sitio.';} return; }
     navigator.serviceWorker.ready.then(function(reg){
       fetch('push_sub.php').then(function(r){return r.json();}).then(function(pk){
         if(!pk.publicKey){ setState('Falta configurar el servidor',false); return; }
         reg.pushManager.subscribe({userVisibleOnly:true, applicationServerKey:u8(pk.publicKey)}).then(function(sub){
           fetch('push_sub.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(sub.toJSON())})
            .then(function(r){return r.json();}).then(function(d){ if(d.token){ idbSet('pushToken',d.token); } setState('Activadas',true); if(btn){btn.innerHTML='Desactivar notificaciones'; btn.setAttribute('data-on','1'); btn.className='btn btn-g';} });
         }).catch(function(){ setState('No se pudo suscribir',false); });
       });
     });
   });
 }
 function disable(){ navigator.serviceWorker.ready.then(function(reg){ reg.pushManager.getSubscription().then(function(s){ if(s)s.unsubscribe(); setState('Desactivadas',false); if(btn){btn.innerHTML='Activar notificaciones'; btn.removeAttribute('data-on'); btn.className='btn btn-pri';} }); }); }
 if(btn){ btn.addEventListener('click',function(){ btn.getAttribute('data-on')?disable():enable(); }); }
 refresh();
})();
</script>
<script>
(function(){
 var addBtn=document.getElementById('waAdd'), st=document.getElementById('waState'), list=document.getElementById('waList'), hint=document.getElementById('waHint');
 var CSRF='<?=csrf()?>';
 function setState(t,on){ if(st){ st.className='state '+(on?'on':'off'); st.innerHTML='<span class="d"></span>'+t; } }
 if(!window.PublicKeyCredential){ setState('No disponible aquí',false); if(addBtn){addBtn.disabled=true;addBtn.style.opacity='.6';} if(hint){hint.style.display='block';hint.textContent='Abrí el panel como app (PWA) en un dispositivo con Face ID / huella.';} return; }
 function b2b(buf){ var a=new Uint8Array(buf),s=''; for(var i=0;i<a.length;i++)s+=String.fromCharCode(a[i]); return btoa(s).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,''); }
 function b2u(s){ s=s.replace(/-/g,'+').replace(/_/g,'/'); var p=s.length%4; if(p)s+='='.repeat(4-p); var raw=atob(s),a=new Uint8Array(raw.length); for(var i=0;i<raw.length;i++)a[i]=raw.charCodeAt(i); return a.buffer; }
 function esc(s){ return (s||'').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
 function renderList(creds){
   if(!list)return;
   if(!creds||!creds.length){ list.innerHTML=''; return; }
   var h=''; creds.forEach(function(c){ h+='<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border:1px solid var(--line);border-radius:11px;margin-bottom:8px"><span style="font-size:13.5px;font-weight:500">🔑 '+esc(c.label)+'</span><button class="btn btn-d btn-sm" data-del="'+c.id+'">Quitar</button></div>'; });
   list.innerHTML=h;
   list.querySelectorAll('[data-del]').forEach(function(btn){ btn.addEventListener('click',function(){ var fd=new URLSearchParams({csrf:CSRF,act:'delete',id:btn.getAttribute('data-del')}); fetch('wa_reg.php?act=delete',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd}).then(function(){ load(); }); }); });
 }
 function load(){ fetch('wa_reg.php?act=list').then(function(r){return r.json();}).then(function(d){ var n=(d.creds||[]).length; renderList(d.creds); setState(n?('Activo ('+n+')'):'Sin passkeys',n>0); if(addBtn)addBtn.innerHTML=(n?'Registrar otro dispositivo':'<?=addslashes(ico('key',16))?> Registrar Face ID en este dispositivo'); }).catch(function(){ setState('—',false); }); }
 function register(){
   addBtn.disabled=true; if(hint)hint.style.display='none';
   fetch('wa_reg.php?act=options').then(function(r){return r.json();}).then(function(opt){
     opt.challenge=b2u(opt.challenge); opt.user.id=b2u(opt.user.id);
     opt.excludeCredentials=(opt.excludeCredentials||[]).map(function(c){return {type:c.type,id:b2u(c.id)};});
     return navigator.credentials.create({publicKey:opt});
   }).then(function(cred){
     var body={id:b2b(cred.rawId),label:(navigator.platform||'Este dispositivo'),response:{attestationObject:b2b(cred.response.attestationObject),clientDataJSON:b2b(cred.response.clientDataJSON)}};
     return fetch('wa_reg.php?act=verify',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
   }).then(function(r){return r.json();}).then(function(d){ addBtn.disabled=false; if(d&&d.ok){ load(); } else if(hint){ hint.style.display='block'; hint.textContent='No se pudo registrar la passkey. Probá de nuevo.'; } })
   .catch(function(e){ addBtn.disabled=false; });
 }
 if(addBtn)addBtn.addEventListener('click',register);
 load();
})();
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
 var box=document.getElementById('qrbox'); if(!box||!box.dataset.uri||!window.QRCode) return;
 try{ new QRCode(box,{text:box.dataset.uri,width:168,height:168,correctLevel:QRCode.CorrectLevel.M}); }catch(e){}
})();
</script>
<?php af();
