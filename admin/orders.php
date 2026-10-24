<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([trim($_POST['status']),(int)$_POST['id']]);
  flash('Estado actualizado.'); header('Location: orders.php'.(isset($_POST['view'])?'?id='.(int)$_POST['id']:'')); exit;
}
$ESTADOS=['nuevo','en proceso','enviado','entregado','cancelado'];
$view=(int)($_GET['id']??0);
if($view){ $o=$pdo->query("SELECT * FROM orders WHERE id=$view")->fetch();
  if(!$o){ flash('Pedido no encontrado.'); header('Location: orders.php'); exit; }
  $items=json_decode($o['items'],true)?:[];
  ah('Pedido #'.$view);
  ?>
  <a href="orders.php" class="btn btn-g btn-sm" style="margin-bottom:16px"><?=ico('chev',15)?>Volver a pedidos</a>
  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
  <div class="panel"><div class="ph"><h2>Ítems del pedido</h2></div><table><thead><tr><th>Producto</th><th>Código</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>
  <?php $tot=0; foreach($items as $it){ $sub=($it['price']??0)*($it['qty']??1); $tot+=$sub; ?>
  <tr><td style="font-weight:500"><?=e($it['name']??'')?></td><td><?=e($it['sku']??'')?></td><td><?=(int)($it['qty']??1)?></td><td><?=money($it['price']??0)?></td><td><?=money($sub)?></td></tr>
  <?php } ?>
  <tr><td colspan="4" style="text-align:right;font-weight:700">Total</td><td style="font-weight:800"><?=money($o['total'])?></td></tr>
  </tbody></table></div>
  <div><div class="panel"><div class="ph"><h2>Cliente</h2></div><div style="padding:16px 20px;font-size:14px;line-height:1.9">
   <div><b><?=e($o['customer'])?:'—'?></b></div><div class="muted" style="color:var(--mut)"><?=e($o['email'])?:'—'?></div>
   <div class="muted" style="color:var(--mut)"><?=e($o['phone'])?:'—'?></div><div class="muted" style="color:var(--mut2);margin-top:8px"><?=e($o['created'])?></div>
  </div></div>
  <div class="panel"><div class="ph"><h2>Estado</h2></div><form method="post" style="padding:16px 20px"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$view?>"><input type="hidden" name="view" value="1">
   <select class="inp" name="status" style="margin-bottom:12px"><?php foreach($ESTADOS as $s):?><option <?=$o['status']===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select>
   <button class="btn btn-pri btn-block" style="width:100%;justify-content:center">Actualizar estado</button></form></div></div>
  </div>
  <?php af(); exit;
}
$rows=$pdo->query("SELECT id,customer,email,total,status,created FROM orders ORDER BY id DESC")->fetchAll();
ah('Pedidos');
$bcl=['nuevo'=>'new','en proceso'=>'am','enviado'=>'am','entregado'=>'ok','cancelado'=>'no'];
?>
<div class="panel">
<?php if(!$rows):?><div style="padding:40px;text-align:center;color:var(--mut2)">Todavía no hay pedidos. Cuando un cliente finalice una compra por WhatsApp, aparecerá acá.</div>
<?php else:?><table><thead><tr><th>#</th><th>Cliente</th><th>Email</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
<?php foreach($rows as $o):?><tr><td>#<?=$o['id']?></td><td style="font-weight:500"><?=e($o['customer'])?:'—'?></td><td style="color:var(--mut)"><?=e($o['email'])?:'—'?></td>
<td style="font-weight:600"><?=money($o['total'])?></td><td><span class="badge <?=$bcl[$o['status']]??'new'?>"><?=e($o['status'])?></span></td>
<td style="color:var(--mut2)"><?=e(substr($o['created'],0,16))?></td><td><a class="btn btn-g btn-sm" href="orders.php?id=<?=$o['id']?>"><?=ico('eye',15)?>Ver</a></td></tr><?php endforeach;?>
</tbody></table><?php endif;?>
</div>
<?php af();
