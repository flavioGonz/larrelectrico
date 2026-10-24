<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$rows=$pdo->query("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) norders, (SELECT COALESCE(SUM(total),0) FROM orders o WHERE o.user_id=u.id) spent FROM users u WHERE u.role<>'admin' ORDER BY u.id DESC")->fetchAll();
ah('Clientes');
?>
<div class="panel">
<?php if(!$rows):?><div style="padding:40px;text-align:center;color:var(--mut2)">Todavía no hay clientes registrados.</div>
<?php else:?><table><thead><tr><th>Cliente</th><th>Email</th><th>Teléfono</th><th>Pedidos</th><th>Gastado</th><th>Alta</th></tr></thead><tbody>
<?php foreach($rows as $u):?><tr><td style="font-weight:500"><?=e($u['name'])?></td><td style="color:var(--mut)"><?=e($u['email'])?></td>
<td><?=e($u['phone'])?:'—'?></td><td><span class="badge new"><?=(int)$u['norders']?></span></td><td style="font-weight:600"><?=money($u['spent'])?></td>
<td style="color:var(--mut2)"><?=e(substr($u['created'],0,10))?></td></tr><?php endforeach;?>
</tbody></table><?php endif;?></div>
<?php af();
