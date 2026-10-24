<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
$u=user();
if(!$u){ http_response_code(401); echo json_encode(['error'=>'No has iniciado sesión.']); exit; }
if(empty($_FILES['file']['tmp_name'])){ echo json_encode(['error'=>'sin archivo']); exit; }
$f=$_FILES['file'];
if($f['size']>6*1024*1024){ echo json_encode(['error'=>'La imagen supera 6MB.']); exit; }
$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
if(!in_array($ext,['jpg','jpeg','png','webp','gif'])){ echo json_encode(['error'=>'Formato no permitido (usá JPG, PNG o WEBP).']); exit; }
$dir=__DIR__.'/../assets/uploads/avatars'; if(!is_dir($dir)) @mkdir($dir,0755,true);
$fn='u'.$u['id'].'_'.substr(md5(uniqid()),0,6).'.'.$ext;
if(!move_uploaded_file($f['tmp_name'],"$dir/$fn")){ echo json_encode(['error'=>'No se pudo guardar la imagen.']); exit; }
$url='final/assets/uploads/avatars/'.$fn;
try{ $pdo=db(); $pdo->prepare("UPDATE users SET avatar=? WHERE id=?")->execute([$url,$u['id']]); }catch(Exception $e){ echo json_encode(['error'=>$e->getMessage()]); exit; }
$_SESSION['user']['avatar']=$url;
echo json_encode(['ok'=>true,'avatar'=>$url]);
