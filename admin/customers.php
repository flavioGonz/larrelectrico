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
  $norders=count($orders);
  $spent=array_sum(array_map(fn($o)=>(float)$o['total'],$orders));
  $avg=$norders?$spent/$norders:0;
  $last=$norders?$orders[0]['created']:null;
  $daysAgo=$last?floor((time()-strtotime($last))/86400):null;
  $rel=$daysAgo===null?'—':($daysAgo<1?'hoy':($daysAgo==1?'ayer':'hace '.(int)$daysAgo.' d'));
  $cm=date('Y-m'); $thisMonth=0; foreach($orders as $o){ if(substr($o['created'],0,7)===$cm)$thisMonth++; }
  $MES=['01'=>'ene','02'=>'feb','03'=>'mar','04'=>'abr','05'=>'may','06'=>'jun','07'=>'jul','08'=>'ago','09'=>'set','10'=>'oct','11'=>'nov','12'=>'dic'];
  $byMonth=[]; foreach($orders as $o){ $k=substr($o['created'],0,7); $byMonth[$k]=($byMonth[$k]??0)+(float)$o['total']; }
  $months=[]; $maxv=0; for($i=5;$i>=0;$i--){ $ts=mktime(0,0,0,(int)date('n')-$i,1,(int)date('Y')); $k=date('Y-m',$ts); $v=$byMonth[$k]??0; if($v>$maxv)$maxv=$v; $months[]=['lbl'=>$MES[date('m',$ts)],'v'=>$v]; }
  $kf=function($v){ return $v>=1000?'$'.number_format($v/1000,1,',','.').'k':'$'.number_format($v,0,',','.'); };
  $bcl=['nuevo'=>'new','pagado'=>'ok','en proceso'=>'am','enviado'=>'am','entregado'=>'ok','cancelado'=>'no'];
  $ini=mb_strtoupper(mb_substr(trim($u['name'])?:'C',0,1));
  $alta=$u['created']?date('d/m/Y',strtotime($u['created'])):'—';
  ah('Cliente · '.$u['name']);
?>
<style>
.cf .chead{background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px 24px;display:flex;align-items:center;gap:18px;margin-bottom:16px}
.cf .chead .av{width:70px;height:70px;border-radius:20px;background:linear-gradient(135deg,#1b3a6b,#0b1b3a);display:grid;place-items:center;color:#cfe0ff;font-size:28px;font-weight:800;flex-shrink:0;box-shadow:0 10px 24px -8px rgba(11,27,58,.5)}
.cf .chead .idc{flex:1;min-width:0}
.cf .chead .idc h1{font-size:22px;font-weight:800;letter-spacing:-.3px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.cf .chead .meta{display:flex;flex-wrap:wrap;gap:6px 16px;margin-top:7px;color:var(--mut);font-size:13.5px}
.cf .chead .meta span{display:inline-flex;align-items:center;gap:6px}.cf .chead .meta svg{width:14px;height:14px;color:var(--mut2)}
.cf .chead .acts{display:flex;gap:8px;flex-shrink:0}
.cf .kgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
.cf .kc{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px 18px}
.cf .kc .kl{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--mut2);font-weight:500}.cf .kc .kl svg{width:15px;height:15px;color:var(--blue)}
.cf .kc .kv{font-size:24px;font-weight:800;letter-spacing:-.02em;margin-top:9px;font-variant-numeric:tabular-nums}
.cf .kc .ks{font-size:12px;color:var(--mut2);margin-top:2px}
.cf .cgrid{display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start}
.cf .chart{display:flex;align-items:flex-end;gap:12px;height:170px;padding:22px 22px 8px}
.cf .col{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end}
.cf .col .bar{width:100%;max-width:40px;border-radius:7px 7px 3px 3px;background:linear-gradient(180deg,#2a93ff,#0071e3);min-height:3px;transition:.2s}
.cf .col .bar:hover{filter:brightness(1.08)}
.cf .col .bv{font-size:11px;font-weight:700;white-space:nowrap;font-variant-numeric:tabular-nums}
.cf .col .bl{font-size:11px;color:var(--mut2);font-weight:500}
.cf .danger{border-color:#f6d2da}.cf .danger .ph{background:#fff7f9;border-color:#f6d2da}.cf .danger .ph h2{color:var(--red)}
.cf .ph .pt{font-size:12px;color:var(--mut2)}
@media(max-width:820px){.cf .cgrid{grid-template-columns:1fr}.cf .kgrid{grid-template-columns:1fr 1fr}.cf .chead{flex-wrap:wrap}.cf .chead .acts{width:100%}.cf .chead .acts .btn{flex:1;justify-content:center}}
</style>
<div class="cf">
<a href="customers.php" class="btn btn-g btn-sm" style="margin-bottom:16px"><?=ico('chev',15)?>Volver a clientes</a>

<div class="chead">
 <div class="av"><?=e($ini)?></div>
 <div class="idc">
  <h1><?=e($u['name'])?:'Cliente'?> <?php if($norders>=5):?><span class="badge am"><?=ico('star',13)?> Cliente frecuente</span><?php endif;?></h1>
  <div class="meta">
   <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><?=e($u['email'])?></span>
   <?php if($u['phone']):?><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h5l2 5-3 2a14 14 0 0 0 6 6l2-3 5 2v5a2 2 0 0 1-2 2A18 18 0 0 1 2 6a2 2 0 0 1 2-2z"/></svg><?=e($u['phone'])?></span><?php endif;?>
   <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Cliente desde <?=e($alta)?></span>
  </div>
 </div>
 <div class="acts">
  <button type="button" class="btn btn-g" onclick="var n=document.querySelector('[name=name]');n.focus();n.scrollIntoView({behavior:'smooth',block:'center'})"><?=ico('edit',16)?>Editar</button>
  <button type="button" class="btn btn-d" onclick="document.getElementById('delForm').requestSubmit()"><?=ico('trash',15)?>Eliminar</button>
 </div>
</div>

<div class="kgrid">
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h2l2.5 13h12l2-9H6"/></svg>Pedidos</div><div class="kv"><?=$norders?></div><div class="ks"><?=$thisMonth?> este mes</div></div>
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Total gastado</div><div class="kv">$<?=number_format($spent,0,',','.')?></div><div class="ks">con IVA</div></div>
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18"/><path d="m7 14 3-3 3 2 5-6"/></svg>Ticket promedio</div><div class="kv">$<?=number_format($avg,0,',','.')?></div><div class="ks">por pedido</div></div>
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Última compra</div><div class="kv" style="font-size:21px"><?=$rel?></div><div class="ks"><?=$last?e(date('d/m/Y',strtotime($last))):'sin compras'?></div></div>
</div>

<div class="cgrid">
 <div>
  <div class="panel"><div class="ph"><h2>Compras por mes</h2><span class="pt">Últimos 6 meses · con IVA</span></div>
   <div class="chart">
    <?php foreach($months as $m): $h=$maxv>0?max(3,round($m['v']/$maxv*100)):3; ?>
    <div class="col"><span class="bv"><?=$kf($m['v'])?></span><div class="bar" style="height:<?=$h?>%"></div><span class="bl"><?=$m['lbl']?></span></div>
    <?php endforeach;?>
   </div>
  </div>
  <div class="panel"><div class="ph"><h2>Pedidos</h2><span class="pt"><?=$norders?> pedidos</span></div>
   <?php if($orders):?><table><thead><tr><th>#</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
   <?php foreach($orders as $o):?><tr><td>#<?=$o['id']?></td><td style="font-weight:600"><?=money($o['total'])?></td><td><span class="badge <?=$bcl[$o['status']]??'new'?>"><?=e($o['status'])?></span></td><td style="color:var(--mut2)"><?=e(substr($o['created'],0,10))?></td><td><a class="btn btn-g btn-sm" href="orders.php?id=<?=$o['id']?>"><?=ico('eye',14)?>Ver</a></td></tr><?php endforeach;?>
   </tbody></table>
   <?php else:?><div style="padding:30px;text-align:center;color:var(--mut2)">Este cliente todavía no hizo pedidos.</div><?php endif;?>
  </div>
 </div>
 <div>
  <form method="post" class="panel"><div class="ph"><h2>Datos del cliente</h2></div><div style="padding:16px 20px">
   <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit?>"><input type="hidden" name="act" value="save">
   <div class="field"><label>Nombre</label><input class="inp" name="name" value="<?=e($u['name'])?>" required></div>
   <div class="field"><label>Email <?=tip('Debe ser único. Es con lo que el cliente inicia sesión.')?></label><input class="inp" name="email" type="email" value="<?=e($u['email'])?>" required></div>
   <div class="field"><label>Teléfono</label><input class="inp" name="phone" value="<?=e($u['phone'])?>"></div>
   <div class="field"><label>Nueva contraseña <?=tip('Dejá vacío para no cambiarla. Mínimo 6 caracteres.')?></label><input class="inp" name="pass" type="text" placeholder="(dejar vacío para no cambiar)"></div>
   <button class="btn btn-pri btn-block" style="width:100%;justify-content:center"><?=ico('save',16)?>Guardar cambios</button>
  </div></form>
  <form method="post" id="delForm" class="panel danger" onsubmit="return confirm('¿Eliminar a <?=e(addslashes($u['name']))?>? Esta acción no se puede deshacer.')"><div class="ph"><h2>Zona peligrosa</h2></div><div style="padding:16px 20px">
   <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$edit?>"><input type="hidden" name="act" value="del">
   <p style="font-size:12.5px;color:var(--mut);margin-bottom:12px;line-height:1.5">Eliminar el cliente borra su cuenta y acceso. Sus pedidos quedan registrados.</p>
   <button class="btn btn-d btn-block" style="width:100%;justify-content:center"><?=ico('trash',15)?>Eliminar cliente</button>
  </div></form>
 </div>
</div>
</div>
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
