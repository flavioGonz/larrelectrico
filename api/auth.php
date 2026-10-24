<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
$in=json_decode(file_get_contents('php://input'),true)?:[];
$act=$_GET['action'] ?? ($in['action'] ?? '');
function out($d){ echo json_encode($d,JSON_UNESCAPED_UNICODE); exit; }
function ensure_cols($pdo){ try{ $have=[]; foreach($pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='users'") as $r){ $have[strtolower($r['column_name']??$r['COLUMN_NAME']??'')]=1; } if(!isset($have['avatar'])) $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(400) DEFAULT NULL"); }catch(Exception $e){} }
function sess_user($u){ return ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'phone'=>$u['phone'],'avatar'=>$u['avatar']??null]; }
try{
 $pdo=db(); ensure_cols($pdo);
 if($act==='me'){ $u=user(); if($u){ $s=$pdo->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$u['id']]); $db=$s->fetch(); if($db){ $_SESSION['user']=sess_user($db); $u=$_SESSION['user']; } } out(['user'=>$u]); }
 if($act==='logout'){ $_SESSION=[]; session_destroy(); out(['ok'=>true]); }
 if($act==='update'){
   $u=user(); if(!$u) out(['error'=>'No has iniciado sesión.']);
   $name=trim($in['name']??''); $email=strtolower(trim($in['email']??'')); $phone=trim($in['phone']??'');
   if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)) out(['error'=>'Revisá tu nombre y un email válido.']);
   $s=$pdo->prepare("SELECT id FROM users WHERE email=? AND id<>?"); $s->execute([$email,$u['id']]);
   if($s->fetch()) out(['error'=>'Ese email ya está en uso por otra cuenta.']);
   $pdo->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=?")->execute([$name,$email,$phone,$u['id']]);
   $s=$pdo->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$u['id']]); $_SESSION['user']=sess_user($s->fetch());
   out(['ok'=>true,'user'=>$_SESSION['user']]);
 }
 if($act==='password'){
   $u=user(); if(!$u) out(['error'=>'No has iniciado sesión.']);
   $cur=$in['current']??''; $new=$in['new']??'';
   if(strlen($new)<6) out(['error'=>'La nueva contraseña debe tener 6+ caracteres.']);
   $s=$pdo->prepare("SELECT pass_hash FROM users WHERE id=?"); $s->execute([$u['id']]); $row=$s->fetch();
   if(!$row||!password_verify($cur,$row['pass_hash'])) out(['error'=>'La contraseña actual no es correcta.']);
   $pdo->prepare("UPDATE users SET pass_hash=? WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT),$u['id']]);
   out(['ok'=>true]);
 }
 if($act==='orders'){
   $u=user(); if(!$u) out(['error'=>'No has iniciado sesión.']);
   $s=$pdo->prepare("SELECT id,total,status,items,created FROM orders WHERE user_id=? ORDER BY id DESC"); $s->execute([$u['id']]);
   $out=[]; foreach($s->fetchAll() as $o){ $m=json_decode($o['items'],true)?:[]; $lines=isset($m['lines'])?$m['lines']:$m;
     $out[]=['id'=>(int)$o['id'],'total'=>(float)$o['total'],'status'=>$o['status'],'created'=>$o['created'],
       'payment'=>$m['payment']??'','items'=>array_map(fn($l)=>['name'=>$l['name']??'','qty'=>(int)($l['qty']??1),'price'=>(float)($l['price']??0)],$lines)]; }
   out(['orders'=>$out]);
 }
 if($act==='register'){
   $name=trim($in['name']??''); $email=strtolower(trim($in['email']??'')); $phone=trim($in['phone']??''); $pass=$in['pass']??'';
   if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<6) out(['error'=>'Datos inválidos. Revisá nombre, email y una contraseña de 6+ caracteres.']);
   $s=$pdo->prepare("SELECT id FROM users WHERE email=?"); $s->execute([$email]);
   if($s->fetch()) out(['error'=>'Ya existe una cuenta con ese email.']);
   $h=password_hash($pass,PASSWORD_DEFAULT);
   $pdo->prepare("INSERT INTO users(name,email,phone,pass_hash,created) VALUES(?,?,?,?,NOW())")->execute([$name,$email,$phone,$h]);
   $id=$pdo->lastInsertId();
   $_SESSION['user']=['id'=>(int)$id,'name'=>$name,'email'=>$email,'phone'=>$phone];
   out(['ok'=>true,'user'=>$_SESSION['user']]);
 }
 if($act==='login'){
   $email=strtolower(trim($in['email']??'')); $pass=$in['pass']??'';
   $s=$pdo->prepare("SELECT * FROM users WHERE email=?"); $s->execute([$email]); $u=$s->fetch();
   if(!$u||!password_verify($pass,$u['pass_hash'])) out(['error'=>'Email o contraseña incorrectos.']);
   $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'phone'=>$u['phone']];
   out(['ok'=>true,'user'=>$_SESSION['user']]);
 }
 out(['error'=>'acción desconocida']);
}catch(Exception $e){ http_response_code(500); out(['error'=>$e->getMessage()]); }
