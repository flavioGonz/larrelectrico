<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $a=$_POST['act']??''; $rid=(int)$_POST['id'];
  if($a==='approve') $pdo->prepare("UPDATE reviews SET approved=1 WHERE id=?")->execute([$rid]);
  if($a==='hide') $pdo->prepare("UPDATE reviews SET approved=0 WHERE id=?")->execute([$rid]);
  if($a==='del') $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$rid]);
  exit('ok');
}
$rows=$pdo->query("SELECT r.*, p.name pname FROM reviews r LEFT JOIN products p ON p.id=r.product_id ORDER BY r.id DESC")->fetchAll();
ah('Reseñas');
?>
<div class="panel">
<?php if(!$rows):?><div style="padding:40px;text-align:center;color:var(--mut2)">Todavía no hay reseñas de clientes.</div>
<?php else:?><table><thead><tr><th>Producto</th><th>Cliente</th><th>Puntaje</th><th>Comentario</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td style="font-weight:500"><?=e($r['pname'])?></td><td><?=e($r['name'])?></td>
<td><?=str_repeat('★',(int)$r['rating']).str_repeat('☆',5-(int)$r['rating'])?></td><td style="color:var(--mut);max-width:300px"><?=e($r['body'])?></td>
<td><span class="badge <?=$r['approved']?'ok':'no'?>"><?=$r['approved']?'Visible':'Oculta'?></span></td>
<td style="white-space:nowrap"><button class="btn btn-g btn-sm" onclick="rv('<?=$r['approved']?'hide':'approve'?>',<?=$r['id']?>)"><?=$r['approved']?'Ocultar':'Aprobar'?></button> <button class="btn btn-d btn-sm" onclick="rv('del',<?=$r['id']?>)"><?=ico('trash',15)?></button></td></tr><?php endforeach;?>
</tbody></table><?php endif;?></div>
<script>const CSRF='<?=csrf()?>';function rv(act,id){fetch('reviews.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({csrf:CSRF,act,id})}).then(()=>location.reload());}</script>
<?php af();
