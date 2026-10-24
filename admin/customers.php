<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $id=(int)$_POST['id'];
  if(($_POST['act']??'')==='save'){
    $pdo->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=? AND role<>'admin'")->execute([trim($_POST['name']),strtolower(trim($_POST['email'])),trim($_POST['phone']),$id]);
    if(!empty($_POST['pass'])){ $pdo->prepare("UPDATE users SET pass_hash=? WHERE id=?")->execute([password_hash($_POST['pass'],PASSWORD_DEFAULT),$id]); }
    flash('Cliente actualizado.');
  }
  if(($_POST['act']??'')==='del'){ $pdo->prepare("DELETE FROM users WHERE id=? AND role<>'admin'")->execute([$id]); flash('Cliente eliminado.'); header('Location: customers.php'); exit; }
  header('Location: customers.php?id='.$id); exit;
}
$edit=(int)($_GET['id']??0);
if($edit){ $u=$pdo->query("SELECT * FROM users WHERE id=$edit AND role<>'admin'")->fetch();
  if(!$u){ flash('Cliente no encontrado.'); header('Location: customers.php'); exit; }
  $orders=$pdo->query("SELECT id,total,status,created FROM orders WHERE user_id=$edit ORDER BY id DESC")->fetchAll();
  ah('Editar cliente');
  ?>
  <a href="customers.php" class="btn btn-g btn-sm" style="margin-bottom:16px"><?=ico('chev',15)?>Volver a clientes</a>
  <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">
  <form method="post" class="panel"><div class="ph"><h2>Datos del cliente</h2></div><div style="padding:18px 20px">
   <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit?>"><input type="hidden" name="act" value="save">
   <div class="field"><label><?=ico('users',15)?>&nbsp;Nombre</label><input class="inp" name="name" value="<?=e($u['name'])?>" required></div>
   <div class="field"><label><?=ico('box',15)?>&nbsp;Email <?=tip('Debe ser único. Es con lo que el cliente inicia sesión.')?></label><input class="inp" name="email" type="email" value="<?=e($u['email'])?>" required></div>
   <div class="field"><label>Teléfono</label><input class="inp" name="phone" value="<?=e($u['phone'])?>"></div>
   <div class="field"><label>Nueva contraseña <?=tip('Dejá vacío para no cambiarla. Mínimo 6 caracteres.')?></label><input class="inp" name="pass" type="text" placeholder="(dejar vacío para no cambiar)"></div>
   <button class="btn btn-pri"><?=ico('save',16)?>Guardar cambios</button>
  </div></form>
  <div>
   <div class="panel"><div class="ph"><h2>Resumen</h2></div><div style="padding:16px 20px;font-size:14px;line-height:2">
    <div>Pedidos: <b><?=count($orders)?></b></div><div>Alta: <span class="muted" style="color:var(--mut)"><?=e(substr($u['created'],0,10))?></span></div></div></div>
   <form method="post" class="panel" onsubmit="return confirm('¿Eliminar este cliente? No se puede deshacer.')"><div class="ph"><h2>Zona peligrosa</h2></div><div style="padding:16px 20px">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit?>"><input type="hidden" name="act" value="del">
    <button class="btn btn-d btn-block" style="width:100%;justify-content:center"><?=ico('trash',15)?>Eliminar cliente</button></div></form>
  </div></div>
  <?php if($orders):?><div class="panel" style="margin-top:20px"><div class="ph"><h2>Pedidos del cliente</h2></div><table><thead><tr><th>#</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
  <?php foreach($orders as $o):?><tr><td>#<?=$o['id']?></td><td><?=money($o['total'])?></td><td><span class="badge new"><?=e($o['status'])?></span></td><td style="color:var(--mut2)"><?=e(substr($o['created'],0,16))?></td><td><a class="btn btn-g btn-sm" href="orders.php?id=<?=$o['id']?>">Ver</a></td></tr><?php endforeach;?>
  </tbody></table></div><?php endif;?>
  <?php af(); exit;
}
$q=trim($_GET['q']??'');
$where=$q?"AND (u.name LIKE ? OR u.email LIKE ?)":""; $args=$q?["%$q%","%$q%"]:[];
$st=$pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) norders, (SELECT COALESCE(SUM(total),0) FROM orders o WHERE o.user_id=u.id) spent FROM users u WHERE u.role<>'admin' $where ORDER BY u.id DESC LIMIT 500");
$st->execute($args); $rows=$st->fetchAll();
ah('Clientes');
?>
<div class="toolbar"><form class="srch" method="get" style="max-width:360px"><?=ico('search')?><input name="q" value="<?=e($q)?>" placeholder="Buscar cliente por nombre o email…"></form><span style="color:var(--mut2);font-size:13px;align-self:center"><?=count($rows)?> clientes</span></div>
<div class="panel">
<?php if(!$rows):?><div style="padding:40px;text-align:center;color:var(--mut2)">No hay clientes<?=$q?' para esa búsqueda':''?>.</div>
<?php else:?><table><thead><tr><th>Cliente</th><th>Email</th><th>Teléfono</th><th>Pedidos</th><th>Gastado</th><th>Alta</th><th></th></tr></thead><tbody>
<?php foreach($rows as $u):?><tr><td style="font-weight:500"><?=e($u['name'])?></td><td style="color:var(--mut)"><?=e($u['email'])?></td>
<td><?=e($u['phone'])?:'—'?></td><td><span class="badge new"><?=(int)$u['norders']?></span></td><td style="font-weight:600"><?=money($u['spent'])?></td>
<td style="color:var(--mut2)"><?=e(substr($u['created'],0,10))?></td><td><a class="btn btn-g btn-sm" href="customers.php?id=<?=$u['id']?>"><?=ico('edit',15)?>Editar</a></td></tr><?php endforeach;?>
</tbody></table><?php endif;?></div>
<?php af();
