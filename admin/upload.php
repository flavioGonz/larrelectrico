<?php require(__DIR__.'/inc.php'); require_admin();
header('Content-Type: application/json; charset=utf-8');
if(($_POST['csrf']??($_GET['csrf']??''))!==($_SESSION['csrf']??'x')){ http_response_code(400); echo json_encode(['error'=>'CSRF']); exit; }
if(empty($_FILES['file']['tmp_name'])){ echo json_encode(['error'=>'sin archivo']); exit; }
$f=$_FILES['file'];
if($f['size']>15*1024*1024){ echo json_encode(['error'=>'archivo muy grande (máx 15MB)']); exit; }
$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
$img=['jpg','jpeg','png','webp','gif','avif']; $doc=['pdf','doc','docx','xls','xlsx','txt'];
if(!in_array($ext,array_merge($img,$doc))){ echo json_encode(['error'=>'tipo no permitido: .'.$ext]); exit; }
$dir=__DIR__.'/../assets/uploads'; if(!is_dir($dir)) @mkdir($dir,0755,true);
$base=preg_replace('/[^a-zA-Z0-9._-]/','_',pathinfo($f['name'],PATHINFO_FILENAME));
$fn=substr($base,0,60).'_'.substr(md5(uniqid()),0,6).'.'.$ext;
if(!move_uploaded_file($f['tmp_name'],"$dir/$fn")){ echo json_encode(['error'=>'no se pudo guardar']); exit; }
echo json_encode(['ok'=>true,'url'=>'final/assets/uploads/'.$fn,'type'=>in_array($ext,$img)?'image':'doc','name'=>$f['name'],'ext'=>$ext]);
