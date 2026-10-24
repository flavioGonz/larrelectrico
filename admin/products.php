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
 .pm-list{display:flex;flex-direction:column;gap:12px;margin-bottom:16px}
 .pm-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:14px}
 .pm-top{display:flex;align-items:center;gap:12px}
 .pm-th{width:50px;height:50px;border-radius:11px;object-fit:contain;background:var(--bg3,#fafafc);border:1px solid var(--line);padding:5px;flex-shrink:0}
 .pm-nm{flex:1;min-width:0;color:var(--ink);display:block}
 .pm-nm b{font-size:14.5px;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
 .pm-nm span{font-size:12px;color:var(--mut2)}
 .pm-feat{border:none;cursor:pointer;flex-shrink:0;font-size:17px;width:36px;height:36px;border-radius:10px;line-height:1}
 .pm-row{display:flex;align-items:flex-end;gap:12px;margin-top:13px;flex-wrap:wrap}
 .pm-fld label{display:block;font-size:11px;color:var(--mut2);font-weight:600;margin-bottom:5px;text-transform:uppercase;letter-spacing:.03em}
 .pm-price{display:flex;align-items:center;gap:3px;border:1px solid var(--line2,#d2d2d7);border-radius:10px;padding:0 11px;height:42px}
 .pm-price span{color:var(--mut2);font-weight:600}
 .pm-price input{border:none;outline:none;width:92px;height:42px;font-size:15px;font-weight:700;-moz-appearance:textfield}
 .pm-price input::-webkit-outer-spin-button,.pm-price input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
 .pm-ed{margin-left:auto;height:42px;align-self:flex-end}
 .pm-meta{margin-top:11px;font-size:12px;color:var(--mut2);display:flex;align-items:center;gap:5px}
}
</style>
<div class="pm-list">
<?php foreach($rows as $p): $im=imgu($p['img']); $stk=(int)$p['stock']; $pv=$p['price']!==null?rtrim(rtrim(number_format((float)$p['price'],2,'.',''),'0'),'.'):''; ?>
 <div class="pm-card">
  <div class="pm-top">
   <?php if($im):?><img class="pm-th" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="pm-th"></div><?php endif;?>
   <a class="pm-nm" href="product.php?id=<?=$p['id']?>"><b><?=e($p['name'])?></b><span><?=e($p['sku'])?:'—'?> · <?=e($p['brand'])?:'Sin marca'?></span></a>
   <button class="pm-feat badge <?=$p['featured']?'ok':'no'?>" onclick="toggleFeat(<?=$p['id']?>,this)" title="Destacado"><?=$p['featured']?'★':'☆'?></button>
  </div>
  <div class="pm-row">
   <div class="pm-fld"><label>Stock</label>
    <div class="stepper2"><button type="button" onclick="bump(<?=$p['id']?>,-1)">−</button><input id="stk<?=$p['id']?>" type="number" value="<?=$stk?>" onchange="setStock(<?=$p['id']?>,this.value)"><button type="button" onclick="bump(<?=$p['id']?>,1)">+</button></div>
   </div>
   <div class="pm-fld"><label>Precio</label>
    <div class="pm-price"><span>$</span><input type="number" step="0.01" value="<?=e($pv)?>" onchange="setPrice(<?=$p['id']?>,this.value)"></div>
   </div>
   <a class="btn btn-g btn-sm pm-ed" href="product.php?id=<?=$p['id']?>"><?=ico('edit',16)?></a>
  </div>
  <div class="pm-meta"><?=ico('star',13)?> <?=(int)$p['sales']?> vendidos</div>
 </div>
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
