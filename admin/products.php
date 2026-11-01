<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  if(($_POST['act']??'')==='togglefeat'){ $pdo->prepare("UPDATE products SET featured=1-featured WHERE id=?")->execute([(int)$_POST['id']]); }
  if(($_POST['act']??'')==='stock'){ $pdo->prepare("UPDATE products SET stock=? WHERE id=?")->execute([(int)$_POST['stock'],(int)$_POST['id']]); }
  if(($_POST['act']??'')==='price'){ $pv=(float)str_replace(',','.',(string)($_POST['price']??'')); if($pv<0)$pv=0; $pdo->prepare("UPDATE products SET price=? WHERE id=?")->execute([$pv,(int)$_POST['id']]); }
  if(($_POST['act']??'')==='del'){ $pid=(int)$_POST['id'];
    foreach(['product_images','product_specs','product_categories','product_docs','cart_items','order_items','reviews'] as $t){ try{ $col=in_array($t,['cart_items','order_items'])?'product_id':'product_id'; $pdo->prepare("DELETE FROM $t WHERE product_id=?")->execute([$pid]); }catch(\Throwable $e){} }
    try{ $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$pid]); }catch(\Throwable $e){ exit('err'); }
  }
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
<div class="panel prodtbl"><table class="ptbl"><thead><tr><th></th><th>Producto</th><th>Precio</th><th>Stock</th><th>Ventas</th><th class="tc">Destacado</th><th class="tc">Acciones</th></tr></thead><tbody>
<?php foreach($rows as $p): $im=imgu($p['img']); $stk=(int)$p['stock']; $scls=$stk<=0?'no':($stk<=5?'low':'ok'); ?>
<tr id="prow-<?=$p['id']?>">
<td class="c-img"><a class="pic" href="product.php?id=<?=$p['id']?>"><?php if($im):?><img src="<?=e($im)?>" onerror="this.parentNode.classList.add('empty');this.remove()"><?php else:?><span class="noimg"><?=ico('image',18)?></span><?php endif;?></a></td>
<td class="c-name"><a href="product.php?id=<?=$p['id']?>" class="pn"><?=e($p['name'])?></a><div class="meta"><?php if($p['sku']):?><span class="sku"><?=e($p['sku'])?></span><?php endif;?><?php if($p['brand']):?><span class="br"><?=e($p['brand'])?></span><?php endif;?></div></td>
<td class="c-price"><div class="iedit"><span class="pfx">$</span><input type="number" step="0.01" min="0" value="<?=number_format((float)$p['price'],2,'.','')?>" onchange="setPrice(<?=$p['id']?>,this.value)" onfocus="this.select()"></div></td>
<td class="c-stock"><div class="iedit stk <?=$scls?>"><input type="number" min="0" value="<?=$stk?>" onchange="setStock(<?=$p['id']?>,this.value);stkTint(this)" onfocus="this.select()"></div></td>
<td class="c-sales"><span class="sal"><?=ico('bolt',13)?><?=(int)$p['sales']?></span></td>
<td class="c-feat tc"><button type="button" class="fstar <?=$p['featured']?'on':''?>" title="Marcar como destacado" onclick="toggleFeat(<?=$p['id']?>,this)"><?=ico('star',17)?></button></td>
<td class="c-acts tc"><a class="iact" href="product.php?id=<?=$p['id']?>" title="Editar producto"><?=ico('edit',16)?></a><button type="button" class="iact del" title="Eliminar producto" onclick="delProd(<?=$p['id']?>,this)"><?=ico('trash',16)?></button></td>
</tr><?php endforeach;?>
</tbody></table></div>

<style>
.pm-list{display:none}
/* ===== tabla de productos escritorio ===== */
.prodtbl{padding:6px 4px}
.ptbl{width:100%;border-collapse:separate;border-spacing:0}
.ptbl thead th{font-size:11px;text-transform:uppercase;letter-spacing:.045em;color:var(--mut2);font-weight:700;text-align:left;padding:4px 14px 12px;border-bottom:1px solid var(--line)}
.ptbl thead th.tc{text-align:center}
.ptbl tbody td{padding:10px 14px;border-bottom:1px solid var(--line);vertical-align:middle}
.ptbl tbody tr:last-child td{border-bottom:none}
.ptbl tbody tr{transition:background .12s}
.ptbl tbody tr:hover{background:#f8fafc}
.c-img{width:64px}
.pic{display:grid;place-items:center;width:52px;height:52px;border-radius:12px;background:#f6f7f9;border:1px solid var(--line);overflow:hidden;padding:5px;text-decoration:none}
.pic img{max-width:100%;max-height:100%;object-fit:contain;mix-blend-mode:multiply}
.pic .noimg,.pic.empty::after{color:var(--mut2);display:grid;place-items:center}
.pic.empty::after{content:'';width:18px;height:18px;background:var(--line);border-radius:4px;opacity:.6}
.c-name{max-width:440px}
.c-name .pn{font-weight:600;color:var(--ink);font-size:14.5px;text-decoration:none;display:block;line-height:1.3}
.c-name .pn:hover{color:var(--blue)}
.c-name .meta{display:flex;gap:7px;align-items:center;margin-top:4px;flex-wrap:wrap}
.c-name .sku{font-size:11.5px;color:var(--mut2);font-family:ui-monospace,SFMono-Regular,monospace;max-width:270px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.c-name .br{font-size:11px;font-weight:600;color:var(--mut);background:var(--bg,#f1f2f4);padding:2px 9px;border-radius:999px;white-space:nowrap}
.iedit{display:inline-flex;align-items:center;border:1px solid var(--line2,#d2d2d7);border-radius:10px;background:#fff;overflow:hidden;transition:border-color .15s,box-shadow .15s}
.iedit:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(10,110,230,.12)}
.iedit .pfx{padding:0 1px 0 11px;color:var(--mut2);font-size:13px;font-weight:600}
.iedit input{border:none;outline:none;width:76px;height:38px;padding:0 10px;font-size:14px;font-weight:600;color:var(--ink);background:transparent;-moz-appearance:textfield}
.iedit input::-webkit-outer-spin-button,.iedit input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.iedit.stk input{width:58px;text-align:center;padding:0 6px}
.iedit.stk.ok{border-color:#bfe6cd}
.iedit.stk.low{border-color:#ffd9a8;background:#fffaf3}
.iedit.stk.no{border-color:#f6c9d2;background:#fff7f9}
.sal{display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:700;color:var(--mut);background:var(--bg,#f1f2f4);padding:4px 11px;border-radius:999px}
.sal svg{opacity:.55}
.fstar{width:38px;height:38px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--mut2);cursor:pointer;display:inline-grid;place-items:center;transition:.15s}
.fstar:hover{border-color:var(--amber);color:var(--amber)}
.fstar.on{background:#fff4e9;border-color:#ffd9a8;color:var(--amber)}
.c-acts{white-space:nowrap}
.iact{width:38px;height:38px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--mut);cursor:pointer;display:inline-grid;place-items:center;transition:.15s;text-decoration:none;vertical-align:middle}
.iact+.iact{margin-left:7px}
.iact:hover{border-color:var(--blue);color:var(--blue);background:#f4f8fe}
.iact.del:hover{border-color:#f6c9d2;color:var(--red);background:#fff0f3}
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
 .pm-swipe{position:relative;overflow:hidden;border-bottom:1px solid var(--line)}
 .pm-swipe:last-child{border-bottom:none}
 .pm-acts{position:absolute;top:0;right:0;bottom:0;display:flex;align-items:stretch}
 .pm-acts .pa{width:48px;border:none;font-size:21px;display:grid;place-items:center;cursor:pointer;color:var(--blue)}
 .pm-acts .pa.minus,.pm-acts .pa.plus{background:#eaf0f7}
 .pm-acts .pa-stk{width:46px;display:grid;place-items:center;font-weight:800;font-size:15px;background:#fff;border-left:1px solid var(--line);border-right:1px solid var(--line)}
 .pm-acts .pa.feat{width:54px;background:#fff4e9;color:var(--amber);font-size:19px}
 .pm-acts .pa.feat.on{background:var(--amber);color:#fff}
 .pm-acts .pa.del{width:52px;background:#fdeef3;color:var(--red)}
 .pm-acts .pa.del svg{width:19px;height:19px}
 .pm-row2{position:relative;z-index:1;display:flex;align-items:center;gap:12px;padding:10px 14px;background:#fff;color:var(--ink);transition:transform .22s cubic-bezier(.3,.9,.3,1);will-change:transform}
 .pm-row2:active{background:#fafbfd}
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
 <div class="pm-swipe">
  <div class="pm-acts">
   <button type="button" class="pa minus" onclick="swStock(<?=$p['id']?>,-1)">−</button>
   <span class="pa-stk" id="pstk-<?=$p['id']?>"><?=$stk?></span>
   <button type="button" class="pa plus" onclick="swStock(<?=$p['id']?>,1)">+</button>
   <button type="button" class="pa feat <?=$p['featured']?'on':''?>" onclick="swFeat(<?=$p['id']?>,this)">★</button>
   <button type="button" class="pa del" onclick="delProd(<?=$p['id']?>,this)"><?=ico('trash',18)?></button>
  </div>
  <a class="pm-row2" href="product.php?id=<?=$p['id']?>">
   <?php if($im):?><img class="th" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="th"></div><?php endif;?>
   <div class="nm"><b><?=e($p['name'])?></b><span><?=e($p['sku'])?:'—'?> · <?=e($p['brand'])?:'Sin marca'?></span></div>
   <div class="rt"><div class="pr"><?=money($p['price'])?></div><span class="stk <?=$scls?>" id="pbad-<?=$p['id']?>"><?=$stk?> u.</span></div>
   <span class="chev"><?=ico('chev',17)?></span>
  </a>
 </div>
<?php endforeach;?>
</div>
<div class="swipe-hint" style="display:none;text-align:center;font-size:12px;color:var(--mut2);margin:-6px 0 14px">← Deslizá una fila para stock rápido y destacar</div>

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
function stkTint(inp){var w=inp.closest('.iedit');if(!w)return;var n=parseInt(inp.value,10)||0;w.classList.remove('ok','low','no');w.classList.add(n<=0?'no':(n<=5?'low':'ok'));}
function delProd(id,el){ if(!confirm('¿Eliminar este producto definitivamente?\n\nEsta acción no se puede deshacer.'))return; if(el){el.disabled=true;el.style.opacity='.4';}
  post({act:'del',id:id}).then(function(r){return r.text();}).then(function(t){ t=(t||'').trim();
    if(t==='ok'){ var row=document.getElementById('prow-'+id); if(row){row.style.transition='opacity .22s';row.style.opacity='0';setTimeout(function(){row.remove();},220);}
      var sw=el&&el.closest?el.closest('.pm-swipe'):null; if(sw){sw.style.transition='opacity .22s,height .22s';sw.style.opacity='0';setTimeout(function(){sw.remove();},220);} }
    else { if(el){el.disabled=false;el.style.opacity='1';} alert('No se pudo eliminar el producto.'); }
  }).catch(function(){ if(el){el.disabled=false;el.style.opacity='1';} alert('Error de conexión.'); }); }
function bump(id,d){var i=document.getElementById('stk'+id);if(!i)return;var n=Math.max(0,(parseInt(i.value,10)||0)+d);i.value=n;setStock(id,n);}
/* ===== acciones del swipe (stock rápido + destacar) ===== */
var swT={};
function swStock(id,d){ var s=document.getElementById('pstk-'+id); if(!s)return; var n=Math.max(0,(parseInt(s.textContent,10)||0)+d); s.textContent=n;
  var bad=document.getElementById('pbad-'+id); if(bad){ bad.textContent=n+' u.'; bad.className='stk '+(n<=0?'no':(n<=5?'low':'ok')); }
  clearTimeout(swT[id]); swT[id]=setTimeout(function(){post({act:'stock',id:id,stock:n});},450); }
function swFeat(id,btn){ post({act:'togglefeat',id:id}).then(function(){ btn.classList.toggle('on'); }); }
/* ===== gestos swipe en la lista ===== */
(function(){
 var ACT=248;
 function closeAll(except){ var rs=document.querySelectorAll('.pm-row2'); for(var i=0;i<rs.length;i++){ if(rs[i]!==except){ rs[i].style.transform=''; rs[i].removeAttribute('data-open'); } } }
 var rows=document.querySelectorAll('.pm-swipe');
 for(var k=0;k<rows.length;k++){ (function(sw){
   var row=sw.querySelector('.pm-row2'); if(!row)return;
   var sx=0,cx=0,sy=0,drag=false,moved=false,horiz=false;
   row.addEventListener('touchstart',function(e){ sx=cx=e.touches[0].clientX; sy=e.touches[0].clientY; drag=true; moved=false; horiz=false; },{passive:true});
   row.addEventListener('touchmove',function(e){ if(!drag)return; cx=e.touches[0].clientX; var dx=cx-sx, dy=e.touches[0].clientY-sy;
     if(!horiz && Math.abs(dx)<Math.abs(dy)+2){ drag=false; return; }
     horiz=true; if(Math.abs(dx)>6)moved=true;
     var base=row.getAttribute('data-open')?-ACT:0; var t=Math.min(0,Math.max(-ACT,base+dx)); row.style.transform='translateX('+t+'px)';
   },{passive:true});
   row.addEventListener('touchend',function(){ if(!drag)return; drag=false; var dx=cx-sx; var wasOpen=!!row.getAttribute('data-open');
     if(!wasOpen){ if(dx<-48){ closeAll(row); row.style.transform='translateX(-'+ACT+'px)'; row.setAttribute('data-open','1'); } else { row.style.transform=''; } }
     else { if(dx>48){ row.style.transform=''; row.removeAttribute('data-open'); } else { row.style.transform='translateX(-'+ACT+'px)'; } }
   });
   row.addEventListener('click',function(e){ if(moved){ e.preventDefault(); return; } if(row.getAttribute('data-open')){ e.preventDefault(); row.style.transform=''; row.removeAttribute('data-open'); } });
 })(rows[k]); }
 document.addEventListener('touchstart',function(e){ if(!e.target.closest('.pm-swipe')) closeAll(null); },{passive:true});
 var hint=document.querySelector('.swipe-hint'); if(hint && rows.length && 'ontouchstart' in window){ hint.style.display='block'; }
})();
</script>
<?php af();
