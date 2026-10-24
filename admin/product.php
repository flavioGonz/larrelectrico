<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$isNew=($_GET['id']??'')==='new'; $id=$isNew?0:(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $d=$_POST;
  $fields=[trim($d['name']),trim($d['sku']),trim($d['brand']),($d['price']===''?null:(float)$d['price']),(int)$d['stock'],trim($d['unit']),trim($d['video']),(int)!empty($d['featured']),trim($d['s_desc']),$d['descr'],($d['vendor_id']?:null)];
  if($isNew){
    $nid=(int)$pdo->query("SELECT COALESCE(MAX(id),1000)+1 n FROM products")->fetch()['n'];
    $img=trim($d['img_main']??'');
    $st=$pdo->prepare("INSERT INTO products(id,name,sku,brand,price,stock,unit,video,featured,s_desc,descr,vendor_id,img) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $st->execute(array_merge([$nid],$fields,[$img])); $id=$nid;
  }else{
    $st=$pdo->prepare("UPDATE products SET name=?,sku=?,brand=?,price=?,stock=?,unit=?,video=?,featured=?,s_desc=?,descr=?,vendor_id=? WHERE id=?");
    $st->execute(array_merge($fields,[$id]));
  }
  // categories
  $pdo->prepare("DELETE FROM product_categories WHERE product_id=?")->execute([$id]);
  foreach(($d['cats']??[]) as $cid){ $pdo->prepare("INSERT INTO product_categories(product_id,category_id) VALUES(?,?)")->execute([$id,(int)$cid]); }
  // specs
  $pdo->prepare("DELETE FROM product_specs WHERE product_id=?")->execute([$id]);
  foreach(($d['spec_k']??[]) as $i=>$k){ $k=trim($k); $v=trim($d['spec_v'][$i]??''); if($k!==''&&$v!=='') $pdo->prepare("INSERT INTO product_specs(product_id,k,v,ordering) VALUES(?,?,?,?)")->execute([$id,$k,$v,$i]); }
  // images / media
  $pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$id]);
  $first=null;
  foreach(($d['img_url']??[]) as $i=>$u){ $u=trim($u); if($u!==''){ if($first===null)$first=$u; $pdo->prepare("INSERT INTO product_images(product_id,url,ordering) VALUES(?,?,?)")->execute([$id,$u,$i]); } }
  if($first!==null) $pdo->prepare("UPDATE products SET img=? WHERE id=?")->execute([$first,$id]);
  flash('Producto guardado correctamente.'); header('Location: product.php?id='.$id); exit;
}
$p=$isNew?['id'=>0,'name'=>'','sku'=>'','brand'=>'','price'=>'','stock'=>0,'unit'=>'','video'=>'','featured'=>0,'s_desc'=>'','descr'=>'','vendor_id'=>'','img'=>'']:$pdo->query("SELECT * FROM products WHERE id=$id")->fetch();
if(!$p){ flash('Producto no encontrado.'); header('Location: products.php'); exit; }
$cats=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$pcats=$isNew?[]:array_column($pdo->query("SELECT category_id FROM product_categories WHERE product_id=$id")->fetchAll(),'category_id');
$specs=$isNew?[]:$pdo->query("SELECT k,v FROM product_specs WHERE product_id=$id ORDER BY ordering")->fetchAll();
$imgs=$isNew?[]:array_column($pdo->query("SELECT url FROM product_images WHERE product_id=$id ORDER BY ordering")->fetchAll(),'url');
if(!$imgs && $p['img']) $imgs=[$p['img']];
$vendors=$pdo->query("SELECT id,name FROM vendors ORDER BY name")->fetchAll();
ah($isNew?'Nuevo producto':'Editar producto');
?>
<form method="post" style="display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start">
<input type="hidden" name="csrf" value="<?=csrf()?>">
<div>
 <div class="panel"><div class="ph"><h2>Datos del producto</h2></div><div style="padding:18px 20px">
  <div class="field"><label>Nombre</label><input class="inp" name="name" value="<?=e($p['name'])?>" required></div>
  <div class="grid2"><div class="field"><label>Código / SKU</label><input class="inp" name="sku" value="<?=e($p['sku'])?>"></div>
  <div class="field"><label>Unidad</label><input class="inp" name="unit" value="<?=e($p['unit'])?>" placeholder="Unidad, Metro, KG…"></div></div>
  <div class="grid2"><div class="field"><label>Precio (UYU)</label><input class="inp" name="price" type="number" step="0.01" value="<?=e($p['price'])?>"></div>
  <div class="field"><label>Stock</label><input class="inp" name="stock" type="number" value="<?=(int)$p['stock']?>"></div></div>
  <div class="field"><label>Descripción corta</label><input class="inp" name="s_desc" value="<?=e($p['s_desc'])?>"></div>
  <div class="field"><label>Descripción completa</label>
   <textarea name="descr" id="descrHidden" style="display:none"><?=e($p['descr'])?></textarea>
   <div id="descrEditor" style="background:#fff"></div></div>
 </div></div>
 <div class="panel"><div class="ph"><h2>Especificaciones técnicas</h2><button type="button" class="btn btn-g btn-sm" onclick="addSpec()"><?=ico('plus',15)?>Agregar</button></div><div style="padding:14px 20px" id="specBox">
  <?php foreach($specs?:[['k'=>'','v'=>'']] as $s):?><div class="specrow" style="display:flex;gap:10px;margin-bottom:10px"><input class="inp" name="spec_k[]" placeholder="Característica (ej. Amperaje)" value="<?=e($s['k'])?>"><input class="inp" name="spec_v[]" placeholder="Valor (ej. 40A)" value="<?=e($s['v'])?>"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()"><?=ico('trash',15)?></button></div><?php endforeach;?>
 </div></div>
 <div class="panel"><div class="ph"><h2>Imágenes y unboxing</h2><button type="button" class="btn btn-g btn-sm" onclick="addImg()"><?=ico('plus',15)?>Agregar imagen</button></div><div style="padding:14px 20px">
  <div id="imgBox"><?php foreach($imgs?:[''] as $u):?><div class="imgrow" style="display:flex;gap:10px;margin-bottom:10px;align-items:center"><img class="thumb" src="<?=e(imgu($u))?>" onerror="this.style.visibility='hidden'"><input class="inp" name="img_url[]" placeholder="images/stories/virtuemart/product/... o URL" value="<?=e($u)?>"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()"><?=ico('trash',15)?></button></div><?php endforeach;?></div>
  <div class="field" style="margin-top:8px"><label><?=ico('video',15)?> Video de unboxing (URL YouTube/MP4)</label><input class="inp" name="video" value="<?=e($p['video'])?>" placeholder="https://youtube.com/watch?v=..."></div>
 </div></div>
</div>
<div>
 <div class="panel"><div style="padding:18px 20px">
  <button class="btn btn-pri btn-block" style="width:100%;justify-content:center"><?=ico('save',16)?>Guardar producto</button>
  <a href="products.php" class="btn btn-g btn-block" style="width:100%;justify-content:center;margin-top:10px">Volver</a>
  <?php if(!$isNew):?><a href="../producto.html?id=<?=$id?>" target="_blank" class="btn btn-g btn-block" style="width:100%;justify-content:center;margin-top:10px"><?=ico('eye',16)?>Ver en tienda</a><?php endif;?>
 </div></div>
 <div class="panel"><div class="ph"><h2>Visibilidad</h2></div><div style="padding:16px 20px">
  <label style="display:flex;align-items:center;gap:10px;cursor:pointer"><input type="checkbox" name="featured" <?=$p['featured']?'checked':''?>> <span><?=ico('star',16)?> Producto destacado</span></label>
 </div></div>
 <div class="panel"><div class="ph"><h2>Marca / Vendor</h2></div><div style="padding:16px 20px">
  <div class="field"><label>Marca (texto)</label><input class="inp" name="brand" value="<?=e($p['brand'])?>"></div>
  <div class="field"><label>Vendor</label><select class="inp" name="vendor_id"><option value="">— Sin vendor —</option><?php foreach($vendors as $v):?><option value="<?=$v['id']?>" <?=$p['vendor_id']==$v['id']?'selected':''?>><?=e($v['name'])?></option><?php endforeach;?></select></div>
 </div></div>
 <div class="panel"><div class="ph"><h2>Categorías</h2></div><div style="padding:14px 20px;max-height:300px;overflow:auto">
  <?php foreach($cats as $c):?><label style="display:flex;align-items:center;gap:9px;padding:5px 0;font-size:13.5px;cursor:pointer"><input type="checkbox" name="cats[]" value="<?=$c['id']?>" <?=in_array($c['id'],$pcats)?'checked':''?>> <?=e($c['name'])?></label><?php endforeach;?>
 </div></div>
</div>
</form>
<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
<style>#descrEditor{height:200px}.ql-toolbar.ql-snow,.ql-container.ql-snow{border-color:var(--line)}.ql-toolbar.ql-snow{border-radius:10px 10px 0 0}.ql-container.ql-snow{border-radius:0 0 10px 10px;font-family:Inter;font-size:14px}</style>
<script>
(function(){ if(!window.Quill)return; var q=new Quill('#descrEditor',{theme:'snow',modules:{toolbar:[['bold','italic','underline'],[{header:[2,3,false]}],[{list:'ordered'},{list:'bullet'}],['link'],['clean']]}});
 var h=document.getElementById('descrHidden'); q.root.innerHTML=h.value||''; 
 q.closest=null; document.querySelector('form').addEventListener('submit',function(){ h.value=q.root.innerHTML; });
})();
function addSpec(){const d=document.createElement('div');d.className='specrow';d.style.cssText='display:flex;gap:10px;margin-bottom:10px';d.innerHTML='<input class="inp" name="spec_k[]" placeholder="Característica"><input class="inp" name="spec_v[]" placeholder="Valor"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()">✕</button>';document.getElementById('specBox').appendChild(d);}
function addImg(){const d=document.createElement('div');d.className='imgrow';d.style.cssText='display:flex;gap:10px;margin-bottom:10px;align-items:center';d.innerHTML='<div class="thumb"></div><input class="inp" name="img_url[]" placeholder="URL o ruta de imagen"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()">✕</button>';document.getElementById('imgBox').appendChild(d);}
</script>
<?php af();
