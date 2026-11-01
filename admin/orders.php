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
$bcl=['nuevo'=>'new','pagado'=>'ok','en proceso'=>'am','enviado'=>'am','entregado'=>'ok','cancelado'=>'no'];

// ============ FICHA DEL PEDIDO ============
$view=(int)($_GET['id']??0);
if($view){ $o=$pdo->query("SELECT * FROM orders WHERE id=$view")->fetch();
  if(!$o){ flash('Pedido no encontrado.'); header('Location: orders.php'); exit; }
  $items=order_lines($o['items']); $meta=order_meta($o['items']);
  $sub=$meta['subtotal']??array_sum(array_map(fn($it)=>($it['price']??0)*($it['qty']??1),$items));
  $iva=$meta['iva']??round($sub*0.22,2);
  $grand=$meta['total']??$o['total'];
  $discAmt=$meta['discount_amt']??0; $pagar=$meta['total_pagar']??$o['total'];
  $nitems=array_sum(array_map(fn($it)=>(int)($it['qty']??1),$items));
  $PM=['transfer'=>'Transferencia / Giro bancario','giros'=>'Giros Redpagos / Abitab','cod'=>'Pago contra entrega','pickup'=>'Retira en local','mp'=>'Mercado Pago'];
  $pmk=$meta['payment']??'';
  $flow=['nuevo','pagado','en proceso','enviado','entregado'];
  $flowLbl=['nuevo'=>'Nuevo','pagado'=>'Pagado','en proceso'=>'En proceso','enviado'=>'Enviado','entregado'=>'Entregado'];
  $cancel=($o['status']==='cancelado');
  $curIdx=$cancel?-1:array_search($o['status'],$flow);
  $ini=mb_strtoupper(mb_substr(trim($o['customer'])?:'C',0,1));
  ah('Pedido #'.$view);
?>
<style>
.om .ohead{background:#fff;border:1px solid var(--line);border-radius:18px;padding:20px 24px;display:flex;align-items:center;gap:16px;margin-bottom:16px;flex-wrap:wrap}
.om .ohead .onum{font-size:22px;font-weight:800;letter-spacing:-.3px}
.om .ohead .osub{color:var(--mut2);font-size:13px;margin-top:2px}
.om .ohead .osp{flex:1}
.om .step{display:flex;align-items:center;gap:0;background:#fff;border:1px solid var(--line);border-radius:16px;padding:20px 24px;margin-bottom:16px;overflow-x:auto}
.om .step .s{display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;flex:1;min-width:72px}
.om .step .s .dot{width:30px;height:30px;border-radius:50%;display:grid;place-items:center;background:#eef1f6;color:var(--mut2);border:2px solid #eef1f6;z-index:1;transition:.2s;font-size:13px;font-weight:700}
.om .step .s.done .dot{background:var(--blue);border-color:var(--blue);color:#fff}
.om .step .s.now .dot{background:#fff;border-color:var(--blue);color:var(--blue);box-shadow:0 0 0 4px rgba(10,132,255,.14)}
.om .step .s .sl{font-size:12px;color:var(--mut2);font-weight:500;white-space:nowrap}
.om .step .s.done .sl,.om .step .s.now .sl{color:var(--ink);font-weight:600}
.om .step .s::before{content:"";position:absolute;top:15px;left:-50%;width:100%;height:2px;background:#eef1f6}
.om .step .s:first-child::before{display:none}
.om .step .s.done::before,.om .step .s.now::before{background:var(--blue)}
.om .cancelbar{background:#fdecef;border:1px solid #f6d2da;color:var(--red);border-radius:14px;padding:13px 18px;font-weight:600;font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:9px}
.om .ogrid{display:grid;grid-template-columns:1fr 330px;gap:16px;align-items:start}
.om .ocard{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px 20px;margin-bottom:14px}
.om .ocard h3{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--mut2);margin-bottom:12px;font-weight:600}
.om .cust{display:flex;gap:12px;align-items:center;margin-bottom:12px}
.om .cust .av{width:44px;height:44px;border-radius:13px;background:linear-gradient(135deg,#1b3a6b,#0b1b3a);display:grid;place-items:center;color:#cfe0ff;font-size:18px;font-weight:800;flex-shrink:0}
.om .cust .cn{font-weight:700;font-size:15px}.om .cust .ce{color:var(--mut);font-size:13px}
.om .crow{display:flex;align-items:center;gap:9px;font-size:13.5px;color:var(--mut);padding:5px 0}
.om .crow svg{width:15px;height:15px;color:var(--mut2);flex-shrink:0}
.om .trow td{color:var(--mut)}.om .grand td{border-top:2px solid var(--line);font-size:15px}
.om .ks{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.om .kc{background:#fff;border:1px solid var(--line);border-radius:14px;padding:13px 16px}
.om .kc .kl{font-size:11px;color:var(--mut2);font-weight:500}.om .kc .kv{font-size:19px;font-weight:800;margin-top:4px;font-variant-numeric:tabular-nums}
.om .note{margin-top:4px;padding:11px 13px;background:var(--bg);border-radius:10px;font-size:13px;color:var(--ink);line-height:1.5}
@media(max-width:820px){.om .ogrid{grid-template-columns:1fr}}
</style>
<div class="om">
<a href="orders.php" class="btn btn-g btn-sm" style="margin-bottom:16px"><?=ico('chev',15)?>Volver a pedidos</a>

<div class="ohead">
 <div><div class="onum">Pedido #<?=$view?></div><div class="osub"><?=e(date('d/m/Y · H:i',strtotime($o['created'])))?> · <?=$nitems?> <?=$nitems==1?'ítem':'ítems'?></div></div>
 <div class="osp"></div>
 <span class="badge <?=$bcl[$o['status']]??'new'?>" style="font-size:13px;padding:6px 12px"><?=e($o['status'])?></span>
</div>

<?php if($cancel):?>
<div class="cancelbar"><?=ico('bolt',16)?>Este pedido fue cancelado.</div>
<?php else:?>
<div class="step">
 <?php foreach($flow as $i=>$st): $cls=$i<$curIdx?'done':($i===$curIdx?'now':''); ?>
 <div class="s <?=$cls?>">
  <div class="dot"><?php if($i<$curIdx):?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="15" height="15"><path d="M20 6 9 17l-5-5"/></svg><?php else:?><?=$i+1?><?php endif;?></div>
  <span class="sl"><?=$flowLbl[$st]?></span>
 </div>
 <?php endforeach;?>
</div>
<?php endif;?>

<div class="ogrid">
 <div class="ocard" style="padding:0">
  <div class="ph"><h2>Ítems del pedido</h2></div>
  <table><thead><tr><th>Producto</th><th>Código</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>
  <?php foreach($items as $it){ $s=($it['price']??0)*($it['qty']??1); ?>
  <tr><td style="font-weight:500"><?=e($it['name']??'')?></td><td style="color:var(--mut2)"><?=e($it['sku']??'')?></td><td><?=(int)($it['qty']??1)?></td><td style="font-variant-numeric:tabular-nums"><?=money($it['price']??0)?></td><td style="font-variant-numeric:tabular-nums;font-weight:500"><?=money($s)?></td></tr>
  <?php } ?>
  <tr class="trow"><td colspan="4" style="text-align:right">Subtotal (sin IVA)</td><td style="font-variant-numeric:tabular-nums"><?=money($sub)?></td></tr>
  <tr class="trow"><td colspan="4" style="text-align:right">IVA (22%)</td><td style="font-variant-numeric:tabular-nums"><?=money($iva)?></td></tr>
  <tr class="trow"><td colspan="4" style="text-align:right">Total con IVA</td><td style="font-variant-numeric:tabular-nums"><?=money($grand)?></td></tr>
  <?php if($discAmt>0):?><tr class="trow"><td colspan="4" style="text-align:right;color:var(--ok)">Descuento transferencia (10%)</td><td style="color:var(--ok);font-variant-numeric:tabular-nums">− <?=money($discAmt)?></td></tr><?php endif;?>
  <tr class="trow grand"><td colspan="4" style="text-align:right;font-weight:700">Total a pagar</td><td style="font-weight:800;font-variant-numeric:tabular-nums"><?=money($pagar)?></td></tr>
  </tbody></table>
 </div>
 <div>
  <div class="ks">
   <div class="kc"><div class="kl">Total a pagar</div><div class="kv">$<?=number_format($pagar,0,',','.')?></div></div>
   <div class="kc"><div class="kl">Ítems</div><div class="kv"><?=$nitems?></div></div>
  </div>
  <div class="ocard"><h3>Cliente</h3>
   <div class="cust"><div class="av"><?=e($ini)?></div><div><div class="cn"><?=e($o['customer'])?:'—'?></div><div class="ce"><?=e($o['email'])?:''?></div></div></div>
   <?php if($o['phone']):?><div class="crow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h5l2 5-3 2a14 14 0 0 0 6 6l2-3 5 2v5a2 2 0 0 1-2 2A18 18 0 0 1 2 6a2 2 0 0 1 2-2z"/></svg><?=e($o['phone'])?></div><?php endif;?>
   <?php if($pmk):?><div class="crow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg><?=e($PM[$pmk]??$pmk)?></div><?php endif;?>
   <?php if(!empty($meta['note'])):?><div class="note"><b>Nota:</b> <?=e($meta['note'])?></div><?php endif;?>
  </div>
  <form method="post" class="ocard"><h3>Cambiar estado</h3>
   <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$view?>"><input type="hidden" name="view" value="1">
   <select class="inp sel2" name="status" style="margin-bottom:12px"><?php foreach($ESTADOS as $s):?><option <?=$o['status']===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select>
   <button class="btn btn-pri btn-block" style="width:100%;justify-content:center"><?=ico('save',16)?>Actualizar estado</button>
   <p style="font-size:12px;color:var(--mut2);margin-top:10px;line-height:1.5">Al marcar <b>entregado</b> se descuenta el stock y se suman las ventas. Cancelar un pedido entregado reintegra el stock.</p>
  </form>
 </div>
</div>
</div>
<?php af(); exit;
}

// ============ LISTA DE PEDIDOS ============
$rows=$pdo->query("SELECT id,customer,email,phone,total,status,created FROM orders ORDER BY id DESC")->fetchAll();
$tot=count($rows);
$cnt=['nuevo'=>0,'pagado'=>0,'en proceso'=>0,'enviado'=>0,'entregado'=>0,'cancelado'=>0];
$ingMes=0; $cm=date('Y-m'); $porEnviar=0; $pedMes=0;
foreach($rows as $r){ if(isset($cnt[$r['status']]))$cnt[$r['status']]++; if(substr($r['created'],0,7)===$cm){ if($r['status']!=='cancelado'){$ingMes+=(float)$r['total'];$pedMes++;} } if(in_array($r['status'],['nuevo','pagado','en proceso']))$porEnviar++; }
$f=$_GET['f']??'todos';
$fmap=['nuevos'=>['nuevo'],'proceso'=>['pagado','en proceso'],'enviados'=>['enviado'],'entregados'=>['entregado'],'cancelados'=>['cancelado']];
$shown=($f!=='todos'&&isset($fmap[$f]))?array_values(array_filter($rows,fn($r)=>in_array($r['status'],$fmap[$f]))):$rows;
$tabs=[['todos','Todos',$tot],['nuevos','Nuevos',$cnt['nuevo']],['proceso','En proceso',$cnt['pagado']+$cnt['en proceso']],['enviados','Enviados',$cnt['enviado']],['entregados','Entregados',$cnt['entregado']],['cancelados','Cancelados',$cnt['cancelado']]];
ah('Pedidos');
?>
<style>
.om .ks{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.om .kc{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px 18px}
.om .kc .kl{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--mut2);font-weight:500}.om .kc .kl svg{width:15px;height:15px;color:var(--blue)}
.om .kc .kv{font-size:24px;font-weight:800;letter-spacing:-.02em;margin-top:9px;font-variant-numeric:tabular-nums}
.om .kc .ks2{font-size:12px;color:var(--mut2);margin-top:2px}
.om .tabs{display:flex;gap:4px;margin-bottom:16px;border-bottom:1px solid var(--line);overflow-x:auto}
.om .tabs a{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;font-size:13.5px;font-weight:500;color:var(--mut);border-bottom:2px solid transparent;white-space:nowrap;margin-bottom:-1px}
.om .tabs a:hover{color:var(--ink)}
.om .tabs a.on{color:var(--blue);border-bottom-color:var(--blue);font-weight:600}
.om .tabs a .tc{background:#eef1f6;color:var(--mut);border-radius:20px;padding:1px 8px;font-size:11px;font-weight:700;font-variant-numeric:tabular-nums}
.om .tabs a.on .tc{background:rgba(10,132,255,.12);color:var(--blue)}
.om td.amt{font-variant-numeric:tabular-nums;font-weight:600}
@media(max-width:820px){.om .ks{grid-template-columns:1fr 1fr}}
</style>
<div class="om">
<div class="ks">
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h2l2.5 13h12l2-9H6"/></svg>Pedidos totales</div><div class="kv"><?=$tot?></div><div class="ks2"><?=$pedMes?> este mes</div></div>
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/><circle cx="12" cy="12" r="4"/></svg>Nuevos</div><div class="kv"><?=$cnt['nuevo']?></div><div class="ks2">sin procesar</div></div>
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="1.8"/><circle cx="18.5" cy="18.5" r="1.8"/></svg>Por enviar</div><div class="kv"><?=$porEnviar?></div><div class="ks2">nuevo · proceso</div></div>
 <div class="kc"><div class="kl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Ingresos del mes</div><div class="kv">$<?=number_format($ingMes,0,',','.')?></div><div class="ks2">sin cancelados</div></div>
</div>

<div class="tabs">
 <?php foreach($tabs as $t):?><a href="orders.php?f=<?=$t[0]?>" class="<?=$f===$t[0]?'on':''?>"><?=$t[1]?><span class="tc"><?=$t[2]?></span></a><?php endforeach;?>
</div>

<div class="panel">
<?php if(!$shown):?><div style="padding:40px;text-align:center;color:var(--mut2)"><?=$tot?'No hay pedidos en este estado.':'Todavía no hay pedidos. Cuando un cliente finalice una compra, aparecerá acá.'?></div>
<?php else:?><table><thead><tr><th>#</th><th>Cliente</th><th>Contacto</th><th>Total c/IVA</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
<?php foreach($shown as $o):?><tr><td style="color:var(--mut2)">#<?=$o['id']?></td><td style="font-weight:500"><?=e($o['customer'])?:'—'?></td><td style="color:var(--mut)"><?=e($o['phone']?:$o['email'])?:'—'?></td>
<td class="amt"><?=money($o['total'])?></td><td><span class="badge <?=$bcl[$o['status']]??'new'?>"><?=e($o['status'])?></span></td>
<td style="color:var(--mut2)"><?=e(date('d/m/Y H:i',strtotime($o['created'])))?></td><td><a class="btn btn-g btn-sm" href="orders.php?id=<?=$o['id']?>"><?=ico('eye',15)?>Ver</a></td></tr><?php endforeach;?>
</tbody></table><?php endif;?>
</div>
</div>
<?php af();
