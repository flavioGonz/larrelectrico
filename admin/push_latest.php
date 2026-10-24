<?php require(__DIR__.'/inc.php');
header('Content-Type: application/json'); header('Cache-Control: no-store');
$t=$_GET['t']??'';
if(!$t){ http_response_code(400); echo json_encode(['error'=>'notoken']); exit; }
try{ $st=db()->prepare("SELECT id FROM push_subs WHERE token=?"); $st->execute([$t]); if(!$st->fetch()){ http_response_code(403); echo json_encode(['error'=>'badtoken']); exit; } }
catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>'db']); exit; }
try{ $o=db()->query("SELECT id,customer,total,created FROM orders ORDER BY id DESC LIMIT 1")->fetch(); }catch(Exception $e){ $o=null; }
if(!$o){ echo json_encode(['title'=>'Larr Eléctrico','body'=>'Novedad en la tienda','url'=>'orders.php']); exit; }
$total=money($o['total']);
echo json_encode(['title'=>'🛒 Nueva compra','body'=>(($o['customer']?:'Cliente').' · '.$total),'url'=>'orders.php','tag'=>'order-'.$o['id']]);
