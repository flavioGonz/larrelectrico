<?php
// Recordatorio de carrito abandonado. Ejecutado por cron cada hora.
// Uso: /final/api/cart_cron.php?key=CRON_KEY   (&test=email@dominio para probar a una casilla)
require(__DIR__.'/../inc/init.php');
require_once(__DIR__.'/../inc/mailer.php');
header('Content-Type: application/json; charset=utf-8');
global $CFG;
if(($_GET['key']??'')!==($CFG['cron_key']??'x')){ http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
$site=rtrim($CFG['site_url']??'','/');
$from=$CFG['mail_from']??'no-reply@larrelectrico.uy';
$fromName=$CFG['mail_from_name']??'Larr Eléctrico';
$hrs=(int)($CFG['cart_remind_after']??2);
$money=fn($n)=>'$'.number_format((float)$n,2,',','.');

function send_mail($to,$subject,$html,$from,$fromName){
  $h="MIME-Version: 1.0\r\n"."Content-Type: text/html; charset=UTF-8\r\n"."From: ".$fromName." <".$from.">\r\n"."Reply-To: ".$from."\r\n";
  return @mail($to,'=?UTF-8?B?'.base64_encode($subject).'?=',$html,$h);
}
function cart_email($name,$items,$total,$link,$site,$money){
  $rows='';
  foreach($items as $it){ $img=$it['img']??''; $src=$img?((strpos($img,'http')===0)?$img:'https://www.larrelectrico.uy/'.ltrim($img,'/')):'';
    $rows.='<tr><td style="padding:10px 0;border-bottom:1px solid #eee" width="56">'.($src?'<img src="'.htmlspecialchars($src).'" width="48" height="48" style="border-radius:8px;object-fit:contain;background:#f4f4f6;border:1px solid #eee">':'').'</td>'
      .'<td style="padding:10px 10px;border-bottom:1px solid #eee;font-size:14px;color:#1d1d1f">'.htmlspecialchars($it['name']).'<div style="color:#86868b;font-size:12px">Cantidad: '.(int)$it['qty'].'</div></td>'
      .'<td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;font-weight:700;color:#1d1d1f;text-align:right" align="right">'.$money($it['price']*$it['qty']).'</td></tr>';
  }
  return '<div style="background:#f5f6f8;padding:28px 0;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif">
   <div style="max-width:480px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e8e8ed">
    <div style="background:#0b1b3a;padding:22px 28px;color:#fff;font-size:18px;font-weight:800">⚡ Larr Eléctrico</div>
    <div style="padding:28px">
     <h1 style="font-size:21px;margin:0 0 6px;color:#1d1d1f">¿Te quedó algo en el carrito?</h1>
     <p style="color:#6e6e73;font-size:14px;line-height:1.6;margin:0 0 18px">Hola '.htmlspecialchars($name?:'').', guardamos tu carrito para que puedas terminar la compra cuando quieras.</p>
     <table style="width:100%;border-collapse:collapse">'.$rows.'
      <tr><td colspan="2" style="padding:14px 0 0;font-size:15px;font-weight:700;color:#1d1d1f">Total (IVA incl.)</td><td align="right" style="padding:14px 0 0;font-size:17px;font-weight:800;color:#1d1d1f">'.$money($total).'</td></tr>
     </table>
     <a href="'.$link.'" style="display:block;text-align:center;background:#0071e3;color:#fff;text-decoration:none;font-weight:600;font-size:15px;padding:14px;border-radius:12px;margin:22px 0 8px">Seguí tu compra →</a>
     <p style="color:#a0a0a8;font-size:12px;text-align:center;margin:10px 0 0">Transferí y pagás 10% menos. Si ya compraste, ignorá este mensaje.</p>
    </div>
    <div style="padding:16px 28px;border-top:1px solid #eee;color:#a0a0a8;font-size:12px">Larr Eléctrico · Materiales eléctricos · Montevideo, Uruguay</div>
   </div></div>';
}

try{
  $pdo=db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS carts(id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, email VARCHAR(190), name VARCHAR(190), items MEDIUMTEXT, total DECIMAL(12,2) DEFAULT 0, token VARCHAR(40), updated DATETIME, reminded TINYINT DEFAULT 0, UNIQUE KEY uq_user(user_id)) DEFAULT CHARSET=utf8mb4");
  $testTo=trim($_GET['test']??'');
  $ageCond=$testTo ? "1=1" : "updated < (NOW() - INTERVAL $hrs HOUR)";   // en prueba, sin esperar
  $sql="SELECT * FROM carts WHERE reminded=0 AND email<>'' AND $ageCond ORDER BY updated ASC LIMIT 50";
  $rows=$pdo->query($sql)->fetchAll();
  $sent=0; $skipped=0;
  foreach($rows as $c){
    $items=json_decode($c['items'],true)?:[]; if(!$items){ $pdo->prepare("UPDATE carts SET reminded=1 WHERE id=?")->execute([$c['id']]); continue; }
    // ¿ya convirtió? (pedido posterior a la última actualización del carrito)
    $conv=$pdo->prepare("SELECT 1 FROM orders WHERE user_id=? AND created>=? LIMIT 1"); $conv->execute([$c['user_id'],$c['updated']]);
    if($conv->fetch()){ $pdo->prepare("UPDATE carts SET reminded=1 WHERE id=?")->execute([$c['id']]); $skipped++; continue; }
    $to=$testTo?:$c['email'];
    $link=$site.'/index.html?recover='.$c['id'].'&t='.$c['token'];
    if(mail_send($to,'Te quedó el carrito en Larr Eléctrico',mail_cart($c['name'],$items,$c['total'],$link))){ $sent++; if(!$testTo)$pdo->prepare("UPDATE carts SET reminded=1 WHERE id=?")->execute([$c['id']]); }
    if($testTo) break; // en modo prueba, un solo envío
  }
  echo json_encode(['ok'=>true,'candidates'=>count($rows),'sent'=>$sent,'skipped_converted'=>$skipped,'test'=>$testTo?:null]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
