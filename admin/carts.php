<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$pdo->exec("CREATE TABLE IF NOT EXISTS carts(id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, email VARCHAR(190), name VARCHAR(190), items MEDIUMTEXT, total DECIMAL(12,2) DEFAULT 0, token VARCHAR(40), updated DATETIME, reminded TINYINT DEFAULT 0, UNIQUE KEY uq_user(user_id)) DEFAULT CHARSET=utf8mb4");
global $CFG; $ck=$CFG['cron_key']??''; $hrs=(int)($CFG['cart_remind_after']??2);
$rows=$pdo->query("SELECT c.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=c.user_id AND o.created>=c.updated) conv FROM carts c ORDER BY c.updated DESC")->fetchAll();
$tot=count($rows); $rem=0; $pend=0; $conv=0; $val=0;
foreach($rows as $c){ if($c['conv']>0)$conv++; elseif($c['reminded'])$rem++; else $pend++; $val+=(float)$c['total']; }
ah('Carritos abandonados');
?>
<div class="cards">
 <div class="kpi"><div class="l"><?=ico('cart',15)?> Carritos activos</div><div class="v"><?=$tot?></div></div>
 <div class="kpi"><div class="l"><?=ico('eye',15)?> Pendientes de aviso</div><div class="v" style="color:var(--amber)"><?=$pend?></div></div>
 <div class="kpi"><div class="l"><?=ico('save',15)?> Recordatorios enviados</div><div class="v" style="color:var(--blue)"><?=$rem?></div></div>
 <div class="kpi"><div class="l"><?=ico('star',15)?> Recuperados</div><div class="v" style="color:var(--ok)"><?=$conv?></div></div>
</div>

<div class="panel"><div class="ph"><h2>Recordatorios automáticos</h2>
  <div style="display:flex;gap:10px">
    <button class="btn btn-g btn-sm" id="btnTest"><?=ico('img',15)?> Enviar prueba a mi correo</button>
    <button class="btn btn-pri btn-sm" id="btnRun"><?=ico('save',15)?> Ejecutar ahora</button>
  </div>
</div>
<div style="padding:16px 20px;font-size:13.5px;color:var(--mut);line-height:1.7">
  El sistema guarda el carrito de cada cliente que inició sesión. Un <b>cron automático cada hora</b> envía un correo de recordatorio a quienes dejaron productos sin comprar por más de <b><?=$hrs?> horas</b>, con un botón que recupera el carrito. Si el cliente ya hizo el pedido, no se le envía.
  <div id="cronMsg" style="margin-top:10px;font-weight:600"></div>
</div></div>

<div class="panel">
<?php if(!$rows):?><div style="padding:40px;text-align:center;color:var(--mut2)">Todavía no hay carritos guardados. Cuando un cliente logueado agregue productos, aparecerá acá.</div>
<?php else:?><table><thead><tr><th>Cliente</th><th>Contacto</th><th>Ítems</th><th>Total</th><th>Última actividad</th><th>Estado</th></tr></thead><tbody>
<?php foreach($rows as $c){ $its=json_decode($c['items'],true)?:[]; $n=0; foreach($its as $i)$n+=(int)($i['qty']??1);
  $names=implode(', ',array_map(fn($i)=>$i['name']??'',array_slice($its,0,3))).(count($its)>3?'…':'');
  if($c['conv']>0){$bc='ok';$st='Recuperado';}elseif($c['reminded']){$bc='new';$st='Avisado';}else{$bc='am';$st='Pendiente';}
?>
<tr><td style="font-weight:500"><?=e($c['name'])?:'—'?></td>
<td style="color:var(--mut)"><?=e($c['email'])?></td>
<td title="<?=e($names)?>"><?=$n?> <span style="color:var(--mut2)">(<?=count($its)?> prod.)</span></td>
<td style="font-weight:600"><?=money($c['total'])?></td>
<td style="color:var(--mut2)"><?=e(substr($c['updated'],0,16))?></td>
<td><span class="badge <?=$bc?>"><?=$st?></span></td></tr>
<?php } ?>
</tbody></table><?php endif;?>
</div>
<script>
const CK=<?=json_encode($ck)?>;
function run(url,label){const m=document.getElementById('cronMsg');m.textContent='Procesando…';m.style.color='var(--mut)';
  fetch(url).then(r=>r.json()).then(d=>{ if(d.ok){m.style.color='var(--ok)';m.textContent='✓ '+label+' — candidatos: '+d.candidates+', enviados: '+d.sent+(d.skipped_converted?(', ya compraron: '+d.skipped_converted):'');}else{m.style.color='var(--red)';m.textContent='Error: '+(d.error||'desconocido');} })
  .catch(e=>{m.style.color='var(--red)';m.textContent='Error de red';});}
document.getElementById('btnRun').onclick=()=>run('../api/cart_cron.php?key='+encodeURIComponent(CK),'Recordatorios enviados');
document.getElementById('btnTest').onclick=()=>run('../api/cart_cron.php?key='+encodeURIComponent(CK)+'&test='+encodeURIComponent('<?=e(admin()['email']??'')?>'),'Prueba enviada');
</script>
<?php af();
