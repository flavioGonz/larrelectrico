<?php
// Crea una preferencia de Checkout Pro de Mercado Pago y devuelve el init_point.
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
global $CFG;
$tok = ($CFG['mp_sandbox']??true) ? ($CFG['mp_test_access_token']?:'') : ($CFG['mp_access_token']?:'');
if($tok===''){ http_response_code(400); echo json_encode(['error'=>'mp_no_configurado','msg'=>'Falta cargar el Access Token de Mercado Pago en config.php']); exit; }
try{
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $oid=(int)($in['order']??0);
  if(!$oid){ http_response_code(400); echo json_encode(['error'=>'orden_invalida']); exit; }
  $pdo=db();
  $o=$pdo->prepare("SELECT * FROM orders WHERE id=?"); $o->execute([$oid]); $o=$o->fetch();
  if(!$o){ http_response_code(404); echo json_encode(['error'=>'orden_no_encontrada']); exit; }
  $meta=json_decode($o['items'],true)?:[];
  $base=(float)($meta['total']??$o['total']);         // total con IVA
  $sur=(float)($CFG['mp_surcharge']??0.09);
  $amount=round($base*(1+$sur),2);                    // + recargo 9%
  $site=rtrim($CFG['img_base'],'/').'/final';
  $pref=[
    'items'=>[[
      'title'=>'Pedido #'.$oid.' — Larr Eléctrico',
      'quantity'=>1,
      'unit_price'=>$amount,
      'currency_id'=>$CFG['currency']??'UYU',
    ]],
    'external_reference'=>(string)$oid,
    'back_urls'=>[
      'success'=>$site.'/pago.html?st=ok&o='.$oid,
      'failure'=>$site.'/pago.html?st=err&o='.$oid,
      'pending'=>$site.'/pago.html?st=pend&o='.$oid,
    ],
    'auto_return'=>'approved',
    'notification_url'=>$site.'/api/mpwebhook.php',
    'statement_descriptor'=>'LARR ELECTRICO',
    'metadata'=>['order_id'=>$oid,'surcharge'=>$sur],
  ];
  $ch=curl_init('https://api.mercadopago.com/checkout/preferences');
  curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$tok],
    CURLOPT_POSTFIELDS=>json_encode($pref,JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT=>20,
  ]);
  $res=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
  if($res===false){ http_response_code(502); echo json_encode(['error'=>'mp_sin_conexion','msg'=>$err]); exit; }
  $j=json_decode($res,true);
  if($code>=400 || empty($j['id'])){ http_response_code(502); echo json_encode(['error'=>'mp_error','code'=>$code,'detail'=>$j]); exit; }
  // guarda la preferencia y el monto cobrado en el pedido
  $meta['payment']='mp'; $meta['mp_pref']=$j['id']; $meta['surcharge']=$sur; $meta['total_pagar']=$amount;
  $pdo->prepare("UPDATE orders SET items=?, total=? WHERE id=?")->execute([json_encode($meta,JSON_UNESCAPED_UNICODE),$amount,$oid]);
  $url=($CFG['mp_sandbox']??true) ? ($j['sandbox_init_point']??$j['init_point']) : ($j['init_point']??$j['sandbox_init_point']);
  echo json_encode(['ok'=>true,'init_point'=>$url,'pref'=>$j['id'],'amount'=>$amount]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
