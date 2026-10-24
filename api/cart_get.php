<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
try{
  $pdo=db();
  $id=(int)($_GET['id']??0); $tok=trim($_GET['t']??'');
  if(!$id||!$tok){ echo json_encode(['error'=>'params']); exit; }
  $s=$pdo->prepare("SELECT items,token FROM carts WHERE id=?"); $s->execute([$id]); $row=$s->fetch();
  if(!$row || !hash_equals((string)$row['token'],$tok)){ echo json_encode(['error'=>'not_found']); exit; }
  echo json_encode(['ok'=>true,'items'=>json_decode($row['items'],true)?:[]]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
