<?php require(__DIR__.'/inc.php'); require_once(__DIR__.'/../inc/webpush.php');
header('Content-Type: application/json');
if(!admin()){ http_response_code(401); echo json_encode(['error'=>'auth']); exit; }
wp_ensure_table();
if($_SERVER['REQUEST_METHOD']==='GET'){ echo json_encode(['publicKey'=>webpush_public_key()]); exit; }
$in=json_decode(file_get_contents('php://input'),true) ?: [];
$ep=$in['endpoint']??''; $keys=$in['keys']??[]; $p256=$keys['p256dh']??''; $auth=$keys['auth']??'';
if(!$ep || !$p256 || !$auth){ http_response_code(400); echo json_encode(['error'=>'bad']); exit; }
$tok=bin2hex(random_bytes(16)); $u=admin();
try{
  db()->prepare("INSERT INTO push_subs(user_id,endpoint,p256dh,auth,token,ua) VALUES(?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE p256dh=VALUES(p256dh),auth=VALUES(auth),token=VALUES(token),user_id=VALUES(user_id)")
    ->execute([$u['id'],$ep,$p256,$auth,$tok,substr($_SERVER['HTTP_USER_AGENT']??'',0,200)]);
  echo json_encode(['ok'=>true,'token'=>$tok]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>'db']); }
