<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  if(($_POST['act']??'')==='togglefeat'){ $pdo->prepare("UPDATE products SET featured=1-featured WHERE id=?")->execute([(int)$_POST['id']]); }
  if(($_POST['act']??'')==='stock'){ $pdo->prepare("UPDATE products SET stock=? WHERE id=?")->execute([(int)$_POST['stock'],(int)$_POST['id']]); }
  exit('ok');
}
$q=trim($_GET['q']??''); $sort=$_GET['sort']??'id'; $page=max(1,(int)($_GET['p']??1)); $per=20; $off=($page-1)*$per;
$where=''; $args=[];
if($q){ $where="WHERE p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?"; $args=["%$q%","%$q%","%$q%"]; }
$order=['id'=>'p.id DESC','sales'=>'p.sales DESC','price'=>'p.price DESC','name'=>'p.name ASC','stock'=>'p.stock ASC'][$sort]??'p.id DESC';
$total=$pdo->prepare("SELECT COUNT(*) c FROM products p $where"); $total->execute($args); $total=$total->fetch()['c'];
$st=$pdo->prepare("SELECT p.* FROM products p $where ORDER BY $order LIMIT $per OFFSET $off"); $st->execute($args); $rows=$st->fetchAll();
$pages=ceil($total/$per);
ah('Productos');
?>
<div class="toolbar">
<form class="srch" method="get"><?=ico('search')?><input name="q" value="<?=e($q)?>" placeholder="Buscar por nombre, código o marca…"></form>
<a class="btn btn-g <?=$sort==='sales'?'':''?>" href="?sort=sales"><?=ico('star',16)?>Más vendidos</a>
<a class="btn btn-pri" href="product.php?id=new"><?=ico('plus',16)?>Nuevo producto</a>
</div>
<div class="panel"><table><thead><tr><th></th><th>Producto</th><th>Marca</th><th>Precio</th><th>Stock</th><th>Vendidos</th><th>Destacado</th><th></th></tr></thead><tbody>
<?php foreach($rows as $p): $im=imgu($p['img']); ?>
<tr>
<td><?php if($im):?><img class="thumb" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="thumb"></div><?php endif;?></td>
<td><a href="product.php?id=<?=$p['id']?>" style="font-weight:500;color:var(--blue)"><?=e($p['name'])?></a><div style="color:var(--mut2);font-size:12px"><?=e($p['sku'])?></div></td>
<td><?=e($p['brand'])?:'—'?></td>
<td><?=money($p['price'])?></td>
<td><input type="number" value="<?=(int)$p['stock']?>" class="inp" style="width:78px;padding:6px 9px" onchange="setStock(<?=$p['id']?>,this.value)"></td>
<td><span class="badge new"><?=(int)$p['sales']?></span></td>
<td><button class="badge <?=$p['featured']?'ok':'no'?>" style="border:none;cursor:pointer" onclick="toggleFeat(<?=$p['id']?>,this)"><?=$p['featured']?'Sí':'No'?></button></td>
<td><a class="btn btn-g btn-sm" href="product.php?id=<?=$p['id']?>"><?=ico('edit',15)?>Editar</a></td>
</tr><?php endforeach;?>
</tbody></table></div>
<div style="color:var(--mut2);font-size:13px"><?=number_format($total,0,',','.')?> productos</div>
<?php if($pages>1): $base='?q='.urlencode($q).'&sort='.$sort.'&p='; ?>
<div class="pag">
<?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++):?><a class="<?=$i==$page?'on':''?>" href="<?=$base.$i?>"><?=$i?></a><?php endfor;?>
</div><?php endif;?>
<form id="cf"><input type="hidden" name="csrf" value="<?=csrf()?>"></form>
<script>
const CSRF='<?=csrf()?>';
function post(d){return fetch('products.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({csrf:CSRF,...d})});}
function toggleFeat(id,el){post({act:'togglefeat',id}).then(()=>{const on=el.textContent==='No';el.textContent=on?'Sí':'No';el.className='badge '+(on?'ok':'no');});}
let st;function setStock(id,v){clearTimeout(st);st=setTimeout(()=>post({act:'stock',id,stock:v}),400);}
</script>
<?php af();
