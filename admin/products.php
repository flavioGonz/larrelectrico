<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  if(($_POST['act']??'')==='togglefeat'){ $pdo->prepare("UPDATE products SET featured=1-featured WHERE id=?")->execute([(int)$_POST['id']]); }
  if(($_POST['act']??'')==='stock'){ $pdo->prepare("UPDATE products SET stock=? WHERE id=?")->execute([(int)$_POST['stock'],(int)$_POST['id']]); }
  if(($_POST['act']??'')==='price'){ $pv=(float)str_replace(',','.',(string)($_POST['price']??'')); if($pv<0)$pv=0; $pdo->prepare("UPDATE products SET price=? WHERE id=?")->execute([$pv,(int)$_POST['id']]); }
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
<div class="panel prodtbl"><table><thead><tr><th></th><th>Producto</th><th>Marca</th><th>Precio</th><th>Stock</th><th>Vendidos</th><th>Destacado</th><th></th></tr></thead><tbody>
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

<style>
.pm-list{display:none}
.stepper2{display:inline-flex;align-items:center;border:1px solid var(--line2,#d2d2d7);border-radius:10px;overflow:hidden}
.stepper2 button{width:40px;height:42px;font-size:19px;background:#fff;color:var(--ink);line-height:1}
.stepper2 button:active{background:var(--bg,#f5f6f8)}
.stepper2 input{width:54px;height:42px;border:none;border-left:1px solid var(--line);border-right:1px solid var(--line);text-align:center;font-size:15px;font-weight:700;outline:none;-moz-appearance:textfield}
.stepper2 input::-webkit-outer-spin-button,.stepper2 input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
@media(max-width:820px){
 .prodtbl{display:none}
 .toolbar{flex-wrap:wrap}
 /* lista compacta: una fila por producto, toda la fila es tappable */
 .pm-list{display:block;background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;margin-bottom:16px}
 .pm-row2{display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--line);color:var(--ink);transition:background .12s}
 .pm-row2:last-child{border-bottom:none}
 .pm-row2:active{background:#eef4ff}
 .pm-row2 .th{width:44px;height:44px;border-radius:10px;object-fit:contain;background:var(--bg3,#fafafc);border:1px solid var(--line);padding:4px;flex-shrink:0}
 .pm-row2 .nm{flex:1;min-width:0}
 .pm-row2 .nm b{font-size:14px;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.35}
 .pm-row2 .nm span{font-size:11.5px;color:var(--mut2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block}
 .pm-row2 .rt{text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:3px}
 .pm-row2 .rt .pr{font-size:14px;font-weight:700;white-space:nowrap;letter-spacing:-.01em}
 .pm-row2 .rt .stk{font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:999px;white-space:nowrap}
 .pm-row2 .rt .stk.ok{background:#eafaf0;color:var(--ok)}
 .pm-row2 .rt .stk.low{background:#fff4e9;color:var(--amber)}
 .pm-row2 .rt .stk.no{background:#fdeef3;color:var(--red)}
 .pm-row2 .chev{color:var(--mut2);flex-shrink:0;display:grid;place-items:center}
 .pm-row2 .chev svg{width:17px;height:17px}
}
</style>
<div class="pm-list">
<?php foreach($rows as $p): $im=imgu($p['img']); $stk=(int)$p['stock']; $scls=$stk<=0?'no':($stk<=5?'low':'ok'); ?>
 <a class="pm-row2" href="product.php?id=<?=$p['id']?>">
  <?php if($im):?><img class="th" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="th"></div><?php endif;?>
  <div class="nm"><b><?=e($p['name'])?></b><span><?=e($p['sku'])?:'—'?> · <?=e($p['brand'])?:'Sin marca'?></span></div>
  <div class="rt"><div class="pr"><?=money($p['price'])?></div><span class="stk <?=$scls?>"><?=$stk?> u.</span></div>
  <span class="chev"><?=ico('chev',17)?></span>
 </a>
<?php endforeach;?>
</div>

<div style="color:var(--mut2);font-size:13px"><?=number_format($total,0,',','.')?> productos</div>
<?php if($pages>1): $base='?q='.urlencode($q).'&sort='.$sort.'&p='; ?>
<div class="pag">
<?php for($i=max(1,$page-3);$i<=min($pages,$page+3);$i++):?><a class="<?=$i==$page?'on':''?>" href="<?=$base.$i?>"><?=$i?></a><?php endfor;?>
</div><?php endif;?>
<form id="cf"><input type="hidden" name="csrf" value="<?=csrf()?>"></form>
<script>
const CSRF='<?=csrf()?>';
function post(d){return fetch('products.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({csrf:CSRF,...d})});}
function toggleFeat(id,el){post({act:'togglefeat',id}).then(function(){var on=el.classList.contains('no');el.classList.remove('ok','no');el.classList.add(on?'ok':'no');if(el.classList.contains('pm-feat'))el.textContent=on?'★':'☆';else el.textContent=on?'Sí':'No';});}
let st;function setStock(id,v){clearTimeout(st);st=setTimeout(function(){post({act:'stock',id:id,stock:v});},400);}
let pt;function setPrice(id,v){clearTimeout(pt);pt=setTimeout(function(){post({act:'price',id:id,price:v});},500);}
function bump(id,d){var i=document.getElementById('stk'+id);if(!i)return;var n=Math.max(0,(parseInt(i.value,10)||0)+d);i.value=n;setStock(id,n);}
</script>
<?php af();
