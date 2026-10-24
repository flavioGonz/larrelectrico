<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
$in=json_decode(file_get_contents('php://input'),true)?:[];
$act=$_GET['action'] ?? ($in['action'] ?? '');
function out($d){ echo json_encode($d); exit; }
try{
 $pdo=db();
 if($act==='me'){ out(['user'=>user()]); }
 if($act==='logout'){ $_SESSION=[]; session_destroy(); out(['ok'=>true]); }
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
