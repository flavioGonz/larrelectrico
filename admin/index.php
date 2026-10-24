<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$nprod=$pdo->query("SELECT COUNT(*) c FROM products")->fetch()['c'];
$ncat=$pdo->query("SELECT COUNT(*) c FROM categories")->fetch()['c'];
$nord=$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$ncli=$pdo->query("SELECT COUNT(*) c FROM users WHERE role<>'admin'")->fetch()['c'];
$rev=$pdo->query("SELECT COALESCE(SUM(total),0) s FROM orders")->fetch()['s'];
$top=$pdo->query("SELECT id,name,brand,price,sales,img FROM products ORDER BY sales DESC LIMIT 8")->fetchAll();
$orders=$pdo->query("SELECT id,customer,total,status,created FROM orders ORDER BY id DESC LIMIT 6")->fetchAll();
ah('Dashboard');
?>
<div class="cards">
 <div class="kpi"><div class="l"><?=ico('box',15)?>Productos</div><div class="v"><?=number_format($nprod,0,',','.')?></div></div>
 <div class="kpi"><div class="l"><?=ico('cart',15)?>Pedidos</div><div class="v"><?=$nord?></div></div>
 <div class="kpi"><div class="l"><?=ico('users',15)?>Clientes</div><div class="v"><?=$ncli?></div></div>
 <div class="kpi"><div class="l"><?=ico('star',15)?>Facturado</div><div class="v"><?=money($rev)?></div></div>
</div>
<div class="panel"><div class="ph"><h2>Más vendidos</h2><a class="btn btn-g btn-sm" href="products.php?sort=sales"><?=ico('box',16)?>Ver productos</a></div>
<table><thead><tr><th></th><th>Producto</th><th>Marca</th><th>Precio</th><th>Ventas</th><th></th></tr></thead><tbody>
<?php foreach($top as $p): $im=imgu($p['img']); ?>
<tr><td><?php if($im):?><img class="thumb" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="thumb"></div><?php endif;?></td>
<td style="font-weight:500"><?=e($p['name'])?></td><td><?=e($p['brand'])?: '—'?></td><td><?=money($p['price'])?></td>
<td><span class="badge new"><?=(int)$p['sales']?></span></td>
<td><a class="btn btn-g btn-sm" href="product.php?id=<?=$p['id']?>"><?=ico('edit',15)?>Editar</a></td></tr>
<?php endforeach;?>
</tbody></table></div>
<div class="panel"><div class="ph"><h2>Pedidos recientes</h2><a class="btn btn-g btn-sm" href="orders.php">Ver todos</a></div>
<?php if(!$orders):?><div style="padding:26px;color:var(--mut2);font-size:14px">Todavía no hay pedidos.</div>
<?php else:?><table><thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
<?php foreach($orders as $o):?><tr><td>#<?=$o['id']?></td><td><?=e($o['customer'])?:'—'?></td><td><?=money($o['total'])?></td>
<td><span class="badge <?=$o['status']==='nuevo'?'new':'ok'?>"><?=e($o['status'])?></span></td><td style="color:var(--mut2)"><?=e(substr($o['created'],0,16))?></td></tr><?php endforeach;?>
</tbody></table><?php endif;?></div>
<?php af();
