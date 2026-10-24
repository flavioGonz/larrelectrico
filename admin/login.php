<?php require(__DIR__.'/inc.php'); require_once(__DIR__.'/pwagate.php'); require_once(__DIR__.'/swreg.php'); require_once(__DIR__.'/totp.php');
$err=''; $step='login';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['code']) && !empty($_SESSION['pending2fa'])){
    // segundo paso: verificar código 2FA
    $p=$_SESSION['pending2fa'];
    if(totp_verify($p['secret'], $_POST['code'])){
      $_SESSION['admin']=['id'=>$p['id'],'name'=>$p['name'],'email'=>$p['email']]; unset($_SESSION['pending2fa']);
      session_regenerate_id(true); header('Location: index.php'); exit;
    }
    $err='Código incorrecto. Revisá tu app autenticadora.'; $step='2fa';
  } else {
    $email=strtolower(trim($_POST['email']??'')); $pass=$_POST['pass']??'';
    $s=db()->prepare("SELECT * FROM users WHERE email=? AND role='admin'"); $s->execute([$email]); $u=$s->fetch();
    if($u && password_verify($pass,$u['pass_hash'])){
      if(!empty($u['totp_enabled']) && !empty($u['totp_secret'])){
        $_SESSION['pending2fa']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'secret'=>$u['totp_secret']]; $step='2fa';
      } else {
        $_SESSION['admin']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email']]; session_regenerate_id(true); header('Location: index.php'); exit;
      }
    } else { $err='Credenciales incorrectas o sin permisos de administrador.'; }
  }
}
if(isset($_GET['reset'])){ unset($_SESSION['pending2fa']); $step='login'; }
if(admin()){ header('Location: index.php'); exit; }
if($step!=='2fa' && !empty($_SESSION['pending2fa']) && $_SERVER['REQUEST_METHOD']!=='POST'){ $step='2fa'; }
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>Ingresar · Admin Larr Eléctrico</title>
<link rel="manifest" href="manifest.webmanifest">
<meta name="theme-color" content="#0a1730">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Larr Admin">
<link rel="apple-touch-icon" href="icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--blue:#0071e3;--blue2:#0a84ff;--navy:#0a1730;--ink:#13151a;--mut:#5f6470;--mut2:#8a909c;--line:#e7e8ee;--ok:#1a9e4b;--red:#e0245e}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif;color:var(--ink);-webkit-font-smoothing:antialiased;
 background:var(--navy);min-height:100vh;display:grid;place-items:center;padding:22px;position:relative;overflow:hidden}
/* ===== aurora de fondo ===== */
.aura{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none}
.aura b{position:absolute;border-radius:50%;filter:blur(70px);opacity:.55;animation:drift 18s ease-in-out infinite}
.aura .a1{width:520px;height:520px;background:#1657c8;top:-160px;left:-120px}
.aura .a2{width:460px;height:460px;background:#0a84ff;bottom:-180px;right:-120px;animation-delay:-6s}
.aura .a3{width:360px;height:360px;background:#0f7a44;top:40%;right:12%;opacity:.34;animation-delay:-11s}
.aura .grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:46px 46px;mask-image:radial-gradient(circle at 50% 40%,#000,transparent 72%)}
@keyframes drift{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(28px,-22px) scale(1.08)}}
/* ===== tarjeta dividida ===== */
.shell{position:relative;z-index:1;width:900px;max-width:100%;background:rgba(255,255,255,.98);border-radius:26px;
 box-shadow:0 40px 110px -30px rgba(0,0,0,.65),0 0 0 1px rgba(255,255,255,.06);overflow:hidden;
 display:grid;grid-template-columns:1.02fr .98fr;animation:rise .6s cubic-bezier(.16,1,.3,1) both}
@keyframes rise{from{opacity:0;transform:translateY(22px) scale(.985)}to{opacity:1;transform:none}}
/* panel marca */
.brand{position:relative;padding:48px 44px;color:#eaf1ff;overflow:hidden;
 background:linear-gradient(155deg,#0b1f49 0%,#0a3a86 55%,#0b5bd0 100%)}
.brand::after{content:"";position:absolute;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(26,158,75,.5),transparent 68%);bottom:-120px;left:-80px;filter:blur(20px)}
.brand>*{position:relative;z-index:2}
.logo{display:flex;align-items:center;gap:11px;font-weight:800;font-size:21px;letter-spacing:-.02em}
.logo .b{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#27a0ff,#0071e3);display:grid;place-items:center;box-shadow:0 10px 26px -6px rgba(10,132,255,.7)}
.logo .b svg{width:22px;height:22px}
.logo i{font-style:normal;color:#8fc0ff}
.brand .tag{margin-top:30px;font-size:26px;font-weight:800;line-height:1.18;letter-spacing:-.03em}
.brand .sub{margin-top:12px;font-size:14px;color:#aec6ef;line-height:1.55;max-width:300px}
.feat{margin-top:30px;display:flex;flex-direction:column;gap:14px}
.feat .f{display:flex;align-items:center;gap:12px;font-size:13.5px;color:#d6e4fb}
.feat .f .fi{width:32px;height:32px;border-radius:9px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);display:grid;place-items:center;color:#9fd0ff;flex-shrink:0}
.feat .f .fi svg{width:16px;height:16px}
.brand .bfoot{margin-top:38px;font-size:12px;color:#7f9ccb}
/* panel formulario */
.form{padding:48px 44px;display:flex;flex-direction:column;justify-content:center}
.form h1{font-size:25px;font-weight:800;letter-spacing:-.03em}
.form .hi{font-size:14px;color:var(--mut);margin-top:6px;margin-bottom:26px}
.err{display:flex;align-items:center;gap:9px;background:#fff1f3;color:#c81e3c;border:1px solid #f6c9d2;font-size:13px;padding:11px 13px;border-radius:12px;margin-bottom:18px}
.err svg{width:17px;height:17px;flex-shrink:0}
.fld{position:relative;margin-bottom:16px}
.fld .fi{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:19px;height:19px;color:var(--mut2);transition:.18s;pointer-events:none}
.fld input{width:100%;height:56px;padding:20px 14px 6px 42px;border:1.5px solid var(--line);border-radius:14px;font-size:15px;font-family:inherit;outline:none;background:#fbfbfd;transition:.18s}
.fld.haspw input{padding-right:46px}
.fld label{position:absolute;left:42px;top:50%;transform:translateY(-50%);font-size:15px;color:var(--mut2);pointer-events:none;transition:.16s cubic-bezier(.16,1,.3,1);transform-origin:left}
.fld input:focus{border-color:var(--blue);background:#fff;box-shadow:0 0 0 4px rgba(0,113,227,.1)}
.fld input:focus~.fi{color:var(--blue)}
.fld input:focus+label,.fld input:not(:placeholder-shown)+label{top:15px;transform:translateY(0) scale(.78);color:var(--blue)}
.fld input:not(:placeholder-shown):not(:focus)+label{color:var(--mut2)}
.pwtog{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:34px;height:34px;border-radius:9px;display:grid;place-items:center;color:var(--mut2);background:none;border:none;cursor:pointer;transition:.15s}
.pwtog:hover{color:var(--ink);background:var(--line)}
.pwtog svg{width:19px;height:19px}
.go{position:relative;width:100%;height:54px;margin-top:8px;border:none;border-radius:14px;cursor:pointer;
 background:linear-gradient(135deg,#0a84ff,#0071e3);color:#fff;font-size:15.5px;font-weight:700;font-family:inherit;
 display:flex;align-items:center;justify-content:center;gap:9px;overflow:hidden;transition:.2s;box-shadow:0 14px 30px -10px rgba(0,113,227,.6)}
.go:hover{transform:translateY(-1px);box-shadow:0 18px 38px -10px rgba(0,113,227,.7)}
.go:active{transform:translateY(0)}
.go svg{width:18px;height:18px}
.go .shine{position:absolute;top:0;left:-130%;width:70%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.35),transparent);transform:skewX(-18deg);animation:shine 3.6s ease-in-out infinite}
@keyframes shine{0%,62%{left:-130%}80%,100%{left:130%}}
.go.busy{pointer-events:none;opacity:.86}
.go .spin{width:18px;height:18px;border:2.4px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:sp .7s linear infinite;display:none}
@keyframes sp{to{transform:rotate(360deg)}}
.go.busy .spin{display:block}.go.busy .lbl,.go.busy svg.ar{display:none}
.hint{display:flex;align-items:center;justify-content:center;gap:7px;font-size:12.5px;color:var(--mut2);margin-top:20px}
.hint svg{width:14px;height:14px}
.backlink{display:block;text-align:center;margin-top:18px;font-size:13px;color:var(--mut2);text-decoration:none}
.backlink:hover{color:var(--mut);text-decoration:underline}
/* responsive */
@media(max-width:760px){
 body{padding:0;place-items:stretch}
 .shell{grid-template-columns:1fr;width:100%;min-height:100vh;border-radius:0;box-shadow:none;background:#fff}
 .brand{padding:calc(26px + env(safe-area-inset-top)) 26px 30px;border-radius:0}
 .brand .tag{font-size:21px;margin-top:22px}
 .brand .sub{font-size:13.5px}
 .feat{display:none}
 .brand .bfoot{display:none}
 .form{padding:30px 24px calc(30px + env(safe-area-inset-bottom));justify-content:flex-start;flex:1}
 .form h1{font-size:23px}
}
@media(max-width:400px){.brand .sub{display:none}}
</style></head><body>
<div class="aura"><b class="a1"></b><b class="a2"></b><b class="a3"></b><div class="grid"></div></div>
<form class="shell" method="post" id="loginForm">
 <div class="brand">
  <div class="logo"><span class="b"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>Larr <i>Eléctrico</i></div>
  <div class="tag">Panel de<br>administración</div>
  <div class="sub">Gestioná tu catálogo, pedidos y clientes desde un solo lugar.</div>
  <div class="feat">
   <div class="f"><span class="fi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15M21 8l-9 5-9-5 9-5 9 5zM3 8v8l9 5 9-5V8M12 13v8"/></svg></span>Productos, stock e imágenes</div>
   <div class="f"><span class="fi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg></span>Pedidos y pagos en tiempo real</div>
   <div class="f"><span class="fi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>Acceso seguro y protegido</div>
  </div>
  <div class="bfoot">© <?=date('Y')?> Larr Eléctrico · Uruguay</div>
 </div>
 <div class="form">
<?php if($step==='2fa'): ?>
  <h1>Verificación 🔐</h1>
  <div class="hi">Ingresá el código de 6 dígitos de tu app autenticadora.</div>
  <?php if($err):?><div class="err"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><?=e($err)?></div><?php endif;?>
  <div class="fld">
   <svg class="fi" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
   <input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" placeholder=" " required autofocus style="letter-spacing:.42em;font-weight:700;text-align:center;font-size:19px">
   <label for="code">Código</label>
  </div>
  <button class="go" id="goBtn" type="submit">
   <span class="shine"></span><span class="spin"></span>
   <span class="lbl">Verificar</span>
   <svg class="ar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
  </button>
  <a href="login.php?reset=1" class="backlink">← Usar otra cuenta</a>
<?php else: ?>
  <h1>Bienvenido 👋</h1>
  <div class="hi">Ingresá con tus credenciales de administrador.</div>
  <?php if($err):?><div class="err"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><?=e($err)?></div><?php endif;?>
  <div class="fld">
   <svg class="fi" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m2 7 10 6 10-6"/></svg>
   <input type="email" name="email" id="email" placeholder=" " required autofocus autocomplete="username">
   <label for="email">Email</label>
  </div>
  <div class="fld haspw">
   <svg class="fi" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
   <input type="password" name="pass" id="pass" placeholder=" " required autocomplete="current-password">
   <label for="pass">Contraseña</label>
   <button type="button" class="pwtog" id="pwtog" aria-label="Mostrar contraseña">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
   </button>
  </div>
  <button class="go" id="goBtn" type="submit">
   <span class="shine"></span><span class="spin"></span>
   <span class="lbl">Ingresar</span>
   <svg class="ar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
  </button>
  <div class="hint"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>Acceso exclusivo para administradores</div>
<?php endif; ?>
 </div>
</form>
<script>
(function(){
 var tog=document.getElementById('pwtog'), pw=document.getElementById('pass');
 var EYE='<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>';
 var OFF='<path d="M9.9 4.24A9.1 9.1 0 0 1 12 4c6.5 0 10 7 10 7a13.2 13.2 0 0 1-2.16 2.92M6.1 6.1A13.3 13.3 0 0 0 2 11s3.5 7 10 7a9 9 0 0 0 4-.94M1 1l22 22M9.9 9.9a3 3 0 0 0 4.2 4.2"/>';
 if(tog&&pw){ tog.addEventListener('click',function(){ var s=pw.type==='password'; pw.type=s?'text':'password'; tog.querySelector('svg').innerHTML=s?OFF:EYE; pw.focus(); }); }
 var f=document.getElementById('loginForm'), b=document.getElementById('goBtn');
 if(f&&b){ f.addEventListener('submit',function(){ b.classList.add('busy'); }); }
})();
</script>
<?php pwa_gate(); sw_reg(); ?>
</body></html>
