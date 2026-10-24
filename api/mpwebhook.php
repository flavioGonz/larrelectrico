<?php
// Webhook de Mercado Pago: confirma el pago y actualiza el estado del pedido.
require(__DIR__.'/../inc/init.php');
global $CFG;
http_response_code(200); // siempre 200 para que MP no reintente de más
$tok = ($CFG['mp_sandbox']??true) ? ($CFG['mp_test_access_token']?:'') : ($CFG['mp_access_token']?:'');
if($tok==='') { echo 'no-config'; exit; }
$body=json_decode(file_get_contents('php://input'),true)?:[];
$pid = $_GET['data.id'] ?? $_GET['id'] ?? ($body['data']['id'] ?? null);
$type= $_GET['type'] ?? $_GET['topic'] ?? ($body['type'] ?? '');
if(!$pid || strpos($type,'payment')===false){ echo 'ignored'; exit; }
try{
  $ch=curl_init('https://api.mercadopago.com/v1/payments/'.$pid);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$tok],CURLOPT_TIMEOUT=>20]);
  $res=curl_exec($ch); curl_close($ch);
  $p=json_decode($res,true); if(empty($p['id'])){ echo 'notfound'; exit; }
  $oid=(int)($p['external_reference']??0); if(!$oid){ echo 'noref'; exit; }
  $st=$p['status']??''; // approved, pending, rejected, ...
  $map=['approved'=>'pagado','pending'=>'en proceso','in_process'=>'en proceso','rejected'=>'cancelado','cancelled'=>'cancelado','refunded'=>'cancelado'];
  $new=$map[$st]??'en proceso';
  $pdo=db();
  $cur=$pdo->prepare("SELECT items,status FROM orders WHERE id=?"); $cur->execute([$oid]); $cur=$cur->fetch();
  if($cur){
    $meta=json_decode($cur['items'],true)?:[]; $meta['mp_payment']=$p['id']; $meta['mp_status']=$st;
    $pdo->prepare("UPDATE orders SET status=?, items=? WHERE id=?")->execute([$new,json_encode($meta,JSON_UNESCAPED_UNICODE),$oid]);
  }
  echo 'ok';
}catch(Exception $e){ echo 'err'; }
