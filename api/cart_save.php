<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
function ensure_carts($pdo){ $pdo->exec("CREATE TABLE IF NOT EXISTS carts(id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, email VARCHAR(190), name VARCHAR(190), items MEDIUMTEXT, total DECIMAL(12,2) DEFAULT 0, token VARCHAR(40), updated DATETIME, reminded TINYINT DEFAULT 0, UNIQUE KEY uq_user(user_id)) DEFAULT CHARSET=utf8mb4"); }
try{
  $pdo=db(); ensure_carts($pdo);
  $u=user();
  if(!$u){ echo json_encode(['ok'=>false,'guest'=>true]); exit; } // solo clientes logueados
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $items=$in['items']??[];
  // normaliza y calcula total con los precios recibidos del carrito
  $clean=[]; $total=0;
  foreach($items as $it){ $q=max(1,(int)($it['qty']??1)); $pr=(float)($it['price']??0); $total+=$pr*$q;
    $clean[]=['id'=>(int)($it['id']??0),'name'=>(string)($it['name']??''),'qty'=>$q,'price'=>$pr,'img'=>(string)($it['img']??'')]; }
  if(!$clean){ $pdo->prepare("DELETE FROM carts WHERE user_id=?")->execute([$u['id']]); echo json_encode(['ok'=>true,'empty'=>true]); exit; }
  // conserva el token si ya existía
  $ex=$pdo->prepare("SELECT token FROM carts WHERE user_id=?"); $ex->execute([$u['id']]); $row=$ex->fetch();
  $token=$row['token']??substr(bin2hex(random_bytes(16)),0,32);
  $js=json_encode($clean,JSON_UNESCAPED_UNICODE);
  $pdo->prepare("INSERT INTO carts(user_id,email,name,items,total,token,updated,reminded) VALUES(?,?,?,?,?,?,NOW(),0)
    ON DUPLICATE KEY UPDATE email=VALUES(email),name=VALUES(name),items=VALUES(items),total=VALUES(total),updated=NOW(),reminded=0")
    ->execute([$u['id'],$u['email'],$u['name'],$js,$total,$token]);
  echo json_encode(['ok'=>true]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
