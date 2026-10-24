<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
// normaliza el payload de items (soporta formato viejo [array] y nuevo {lines,subtotal,iva,total,note})
function order_lines($raw){ $d=json_decode($raw,true)?:[]; if(isset($d['lines'])) return $d['lines']; return $d; }
function order_meta($raw){ $d=json_decode($raw,true)?:[]; return isset($d['lines'])?$d:null; }

if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $oid=(int)$_POST['id']; $new=trim($_POST['status']);
  $cur=$pdo->query("SELECT status,items FROM orders WHERE id=$oid")->fetch();
  if($cur){
    // al marcar ENTREGADO por primera vez: descuenta stock y suma ventas
    if($new==='entregado' && $cur['status']!=='entregado'){
      foreach(order_lines($cur['items']) as $ln){ $pid=(int)($ln['id']??0); $q=max(1,(int)($ln['qty']??1)); if($pid){
        $pdo->prepare("UPDATE products SET stock=GREATEST(0,stock-?), sales=sales+? WHERE id=?")->execute([$q,$q,$pid]);
      }}
      flash('Pedido entregado: stock y ventas actualizados.');
    }
    // al cancelar un pedido ya entregado: devuelve el stock y descuenta ventas
    elseif($new==='cancelado' && $cur['status']==='entregado'){
      foreach(order_lines($cur['items']) as $ln){ $pid=(int)($ln['id']??0); $q=max(1,(int)($ln['qty']??1)); if($pid){
        $pdo->prepare("UPDATE products SET stock=stock+?, sales=GREATEST(0,sales-?) WHERE id=?")->execute([$q,$q,$pid]);
      }}
      flash('Pedido cancelado: stock reintegrado.');
    } else flash('Estado actualizado.');
    $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$new,$oid]);
  }
  header('Location: orders.php'.(isset($_POST['view'])?'?id='.$oid:'')); exit;
}
$ESTADOS=['nuevo','pagado','en proceso','enviado','entregado','cancelado'];
$view=(int)($_GET['id']??0);
if($view){ $o=$pdo->query("SELECT * FROM orders WHERE id=$view")->fetch();
  if(!$o){ flash('Pedido no encontrado.'); header('Location: orders.php'); exit; }
  $items=order_lines($o['items']); $meta=order_meta($o['items']);
  $sub=$meta['subtotal']??array_sum(array_map(fn($it)=>($it['price']??0)*($it['qty']??1),$items));
  $iva=$meta['iva']??round($sub*0.22,2);
  $grand=$meta['total']??$o['total'];
  ah('Pedido #'.$view);
  ?>
  <a href="orders.php" class="btn btn-g btn-sm" style="margin-bottom:16px"><?=ico('chev',15)?>Volver a pedidos</a>
  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
  <div class="panel"><div class="ph"><h2>Ítems del pedido</h2></div><table><thead><tr><th>Producto</th><th>Código</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>
  <?php foreach($items as $it){ $s=($it['price']??0)*($it['qty']??1); ?>
  <tr><td style="font-weight:500"><?=e($it['name']??'')?></td><td><?=e($it['sku']??'')?></td><td><?=(int)($it['qty']??1)?></td><td><?=money($it['price']??0)?></td><td><?=money($s)?></td></tr>
  <?php } ?>
  <tr><td colspan="4" style="text-align:right;color:var(--mut)">Subtotal (sin IVA)</td><td><?=money($sub)?></td></tr>
  <tr><td colspan="4" style="text-align:right;color:var(--mut)">IVA (22%)</td><td><?=money($iva)?></td></tr>
  <tr><td colspan="4" style="text-align:right;font-weight:700">Total con IVA</td><td style="font-weight:800"><?=money($grand)?></td></tr>
  </tbody></table></div>
  <div><div class="panel"><div class="ph"><h2>Cliente</h2></div><div style="padding:16px 20px;font-size:14px;line-height:1.9">
   <div><b><?=e($o['customer'])?:'—'?></b></div><div style="color:var(--mut)"><?=e($o['email'])?:'—'?></div>
   <div style="color:var(--mut)"><?=e($o['phone'])?:'—'?></div><div style="color:var(--mut2);margin-top:8px"><?=e($o['created'])?></div>
   <?php $PM=['transfer'=>'Transferencia / Giro bancario','giros'=>'Giros Redpagos / Abitab','cod'=>'Pago contra entrega','pickup'=>'Retira en local','mp'=>'Mercado Pago (+9%)']; $pmk=$meta['payment']??''; if($pmk):?><div style="margin-top:10px;font-size:13px"><span style="color:var(--mut2)">Medio de pago:</span> <b><?=e($PM[$pmk]??$pmk)?></b></div><?php endif;?>
   <?php if(!empty($meta['note'])):?><div style="margin-top:10px;padding:10px 12px;background:var(--bg);border-radius:9px;font-size:13px;color:var(--ink)"><b>Nota:</b> <?=e($meta['note'])?></div><?php endif;?>
  </div></div>
  <div class="panel"><div class="ph"><h2>Estado</h2></div><form method="post" style="padding:16px 20px"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$view?>"><input type="hidden" name="view" value="1">
   <select class="inp" name="status" style="margin-bottom:12px"><?php foreach($ESTADOS as $s):?><option <?=$o['status']===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select>
   <button class="btn btn-pri btn-block" style="width:100%;justify-content:center">Actualizar estado</button>
   <p style="font-size:12px;color:var(--mut2);margin-top:10px;line-height:1.5">Al marcar <b>entregado</b> se descuenta el stock y se suman las ventas. Cancelar un pedido entregado reintegra el stock.</p></form></div></div>
  </div>
  <?php af(); exit;
}
$rows=$pdo->query("SELECT id,customer,email,phone,total,status,created FROM orders ORDER BY id DESC")->fetchAll();
ah('Pedidos');
$bcl=['nuevo'=>'new','pagado'=>'ok','en proceso'=>'am','enviado'=>'am','entregado'=>'ok','cancelado'=>'no'];
?>
<div class="panel">
<?php if(!$rows):?><div style="padding:40px;text-align:center;color:var(--mut2)">Todavía no hay pedidos. Cuando un cliente finalice una compra por WhatsApp, aparecerá acá.</div>
<?php else:?><table><thead><tr><th>#</th><th>Cliente</th><th>Contacto</th><th>Total c/IVA</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
<?php foreach($rows as $o):?><tr><td>#<?=$o['id']?></td><td style="font-weight:500"><?=e($o['customer'])?:'—'?></td><td style="color:var(--mut)"><?=e($o['phone']?:$o['email'])?:'—'?></td>
<td style="font-weight:600"><?=money($o['total'])?></td><td><span class="badge <?=$bcl[$o['status']]??'new'?>"><?=e($o['status'])?></span></td>
<td style="color:var(--mut2)"><?=e(substr($o['created'],0,16))?></td><td><a class="btn btn-g btn-sm" href="orders.php?id=<?=$o['id']?>"><?=ico('eye',15)?>Ver</a></td></tr><?php endforeach;?>
</tbody></table><?php endif;?>
</div>
<?php af();
