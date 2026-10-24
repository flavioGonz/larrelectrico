<?php require(__DIR__.'/inc.php');
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=strtolower(trim($_POST['email']??'')); $pass=$_POST['pass']??'';
  $s=db()->prepare("SELECT * FROM users WHERE email=? AND role='admin'"); $s->execute([$email]); $u=$s->fetch();
  if($u && password_verify($pass,$u['pass_hash'])){ $_SESSION['admin']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email']]; header('Location: index.php'); exit; }
  $err='Credenciales incorrectas o sin permisos de administrador.';
}
if(admin()){ header('Location: index.php'); exit; }
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin · Larr Eléctrico</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,sans-serif;background:#0b1b3a;min-height:100vh;display:grid;place-items:center;color:#1d1d1f}
.card{background:#fff;border-radius:20px;padding:38px;width:380px;max-width:92vw;box-shadow:0 30px 70px -20px rgba(0,0,0,.5)}
.lg{display:flex;align-items:center;gap:9px;font-weight:800;font-size:20px;justify-content:center;margin-bottom:6px}
.lg .b{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#0a84ff,#0071e3);color:#fff;display:grid;place-items:center}
.lg span{color:#0071e3}
h1{font-size:16px;font-weight:600;text-align:center;color:#6e6e73;margin-bottom:22px}
label{font-size:13px;font-weight:500;color:#6e6e73;display:block;margin-bottom:6px}
input{width:100%;padding:12px 14px;border:1px solid #d2d2d7;border-radius:11px;font-size:15px;margin-bottom:14px;outline:none;font-family:inherit}
input:focus{border-color:#0071e3;box-shadow:0 0 0 3px rgba(0,113,227,.12)}
button{width:100%;padding:13px;background:#0071e3;color:#fff;border:none;border-radius:11px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit}
button:hover{background:#0077ed}.err{background:#fff0f1;color:#c81e3c;font-size:13px;padding:10px 13px;border-radius:10px;margin-bottom:16px}
.hint{font-size:12px;color:#86868b;text-align:center;margin-top:16px}</style></head><body>
<form class="card" method="post">
<div class="lg"><span class="b"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>Larr<span>Eléctrico</span></div>
<h1>Panel de administración</h1>
<?php if($err):?><div class="err"><?=e($err)?></div><?php endif;?>
<label>Email</label><input type="email" name="email" required autofocus>
<label>Contraseña</label><input type="password" name="pass" required>
<button>Ingresar</button>
<div class="hint">Acceso exclusivo para administradores</div>
</form></body></html>
