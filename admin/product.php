<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$pdo->exec("CREATE TABLE IF NOT EXISTS product_docs(id INT AUTO_INCREMENT PRIMARY KEY, product_id INT, url VARCHAR(400), name VARCHAR(200), ordering INT) DEFAULT CHARSET=utf8mb4");
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
  // documentos / fichas técnicas
  $pdo->prepare("DELETE FROM product_docs WHERE product_id=?")->execute([$id]);
  foreach(($d['doc_url']??[]) as $i=>$u){ $u=trim($u); if($u!==''){ $nm=trim($d['doc_name'][$i]??'')?:'Documento'; $pdo->prepare("INSERT INTO product_docs(product_id,url,name,ordering) VALUES(?,?,?,?)")->execute([$id,$u,$nm,$i]); } }
  flash('Producto guardado correctamente.'); header('Location: product.php?id='.$id); exit;
}
$p=$isNew?['id'=>0,'name'=>'','sku'=>'','brand'=>'','price'=>'','stock'=>0,'unit'=>'','video'=>'','featured'=>0,'s_desc'=>'','descr'=>'','vendor_id'=>'','img'=>'']:$pdo->query("SELECT * FROM products WHERE id=$id")->fetch();
if(!$p){ flash('Producto no encontrado.'); header('Location: products.php'); exit; }
$cats=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$pcats=$isNew?[]:array_column($pdo->query("SELECT category_id FROM product_categories WHERE product_id=$id")->fetchAll(),'category_id');
$specs=$isNew?[]:$pdo->query("SELECT k,v FROM product_specs WHERE product_id=$id ORDER BY ordering")->fetchAll();
$imgs=$isNew?[]:array_column($pdo->query("SELECT url FROM product_images WHERE product_id=$id ORDER BY ordering")->fetchAll(),'url');
if(!$imgs && $p['img']) $imgs=[$p['img']];
$docs=$isNew?[]:$pdo->query("SELECT url,name FROM product_docs WHERE product_id=$id ORDER BY ordering")->fetchAll();
$vendors=$pdo->query("SELECT id,name FROM vendors ORDER BY name")->fetchAll();
ah($isNew?'Nuevo producto':'Editar producto');
?>
<form method="post" class="prodform">
<input type="hidden" name="csrf" value="<?=csrf()?>">
<div>
 <div class="panel"><div class="ph"><h2>Datos del producto</h2></div><div style="padding:18px 20px">
  <div class="field"><label>Nombre</label><input class="inp" name="name" value="<?=e($p['name'])?>" required></div>
  <div class="grid2"><div class="field"><label>Código / SKU</label><input class="inp" name="sku" value="<?=e($p['sku'])?>"></div>
  <div class="field"><label>Unidad</label><input class="inp" name="unit" value="<?=e($p['unit'])?>" placeholder="Unidad, Metro, KG…"></div></div>
  <div class="grid2"><div class="field"><label>Precio (UYU, sin IVA) <?=tip('Cargá el precio SIN IVA. La tienda agrega el 22% automáticamente y muestra el precio final con IVA.')?></label><input class="inp" name="price" type="number" step="0.01" value="<?=e($p['price'])?>"></div>
  <div class="field"><label>Stock <?=tip('Cantidad disponible. Si es 0 el producto se muestra como "Bajo pedido"; 1-5 muestra "Últimas unidades".')?></label><input class="inp" name="stock" type="number" value="<?=(int)$p['stock']?>"></div></div>
  <div class="field"><label>Descripción corta</label><input class="inp" name="s_desc" value="<?=e($p['s_desc'])?>"></div>
  <div class="field"><label>Descripción completa</label>
   <textarea name="descr" id="descrHidden" style="display:none"><?=e($p['descr'])?></textarea>
   <div id="descrEditor" style="background:#fff"></div></div>
 </div></div>
 <div class="panel"><div class="ph"><h2>Especificaciones técnicas</h2><button type="button" class="btn btn-g btn-sm" onclick="addSpec()"><?=ico('plus',15)?>Agregar</button></div><div style="padding:14px 20px" id="specBox">
  <?php foreach($specs?:[['k'=>'','v'=>'']] as $s):?><div class="specrow" style="display:flex;gap:10px;margin-bottom:10px"><input class="inp" name="spec_k[]" placeholder="Característica (ej. Amperaje)" value="<?=e($s['k'])?>"><input class="inp" name="spec_v[]" placeholder="Valor (ej. 40A)" value="<?=e($s['v'])?>"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()"><?=ico('trash',15)?></button></div><?php endforeach;?>
 </div></div>
 <div class="panel"><div class="ph"><h2>Imágenes y unboxing</h2><button type="button" class="btn btn-g btn-sm" onclick="addImg()"><?=ico('plus',15)?>Agregar por URL</button></div><div style="padding:14px 20px">
  <div class="dropzone" id="dzImg" data-accept="image">
   <div class="dz-ic"><?=ico('image',26)?></div>
   <div class="dz-tx"><b>Arrastrá tus fotos acá</b><span>o hacé clic para elegir · JPG, PNG, WEBP · hasta 15MB</span></div>
   <input type="file" accept="image/*" multiple hidden>
   <div class="dz-bar"><div class="dz-barfill"></div></div>
  </div>
  <div class="cam-row"><button type="button" class="btn btn-g btn-sm" id="camBtn"><?=ico('image',15)?> Tomar foto con la cámara</button><span class="cam-hint">Abre la cámara en el teléfono</span></div>
  <input type="file" id="camInput" accept="image/*" capture="environment" hidden>
  <div id="imgBox" style="margin-top:14px"><?php foreach($imgs?:[''] as $u):?><div class="imgrow" style="display:flex;gap:10px;margin-bottom:10px;align-items:center"><img class="thumb" src="<?=e(imgu($u))?>" onerror="this.style.visibility='hidden'"><input class="inp" name="img_url[]" placeholder="images/stories/virtuemart/product/... o URL" value="<?=e($u)?>"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()"><?=ico('trash',15)?></button></div><?php endforeach;?></div>
  <div class="field" style="margin-top:8px"><label><?=ico('video',15)?> Video de unboxing (URL YouTube/MP4)</label><input class="inp" name="video" value="<?=e($p['video'])?>" placeholder="https://youtube.com/watch?v=..."></div>
 </div></div>
 <div class="panel"><div class="ph"><h2>Documentos y fichas técnicas</h2></div><div style="padding:14px 20px">
  <div class="dropzone" id="dzDoc" data-accept="doc">
   <div class="dz-ic"><?=ico('file',26)?></div>
   <div class="dz-tx"><b>Soltá fichas técnicas o catálogos</b><span>o hacé clic · PDF, DOC, XLS · hasta 15MB</span></div>
   <input type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple hidden>
   <div class="dz-bar"><div class="dz-barfill"></div></div>
  </div>
  <div id="docBox" style="margin-top:14px"><?php foreach($docs as $dc):?><div class="docrow"><span class="dc-ic"><?=ico('file',16)?></span><input class="inp" name="doc_name[]" value="<?=e($dc['name'])?>" placeholder="Nombre del documento"><input type="hidden" name="doc_url[]" value="<?=e($dc['url'])?>"><a href="<?=e(imgu($dc['url']))?>" target="_blank" class="btn btn-g btn-sm"><?=ico('eye',14)?></a><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()"><?=ico('trash',15)?></button></div><?php endforeach;?></div>
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
  <div class="field"><label>Vendor</label><select class="inp sel2" name="vendor_id"><option value="">— Sin vendor —</option><?php foreach($vendors as $v):?><option value="<?=$v['id']?>" <?=$p['vendor_id']==$v['id']?'selected':''?>><?=e($v['name'])?></option><?php endforeach;?></select></div>
 </div></div>
 <div class="panel"><div class="ph"><h2>Categorías</h2></div><div style="padding:14px 20px;max-height:300px;overflow:auto">
  <?php foreach($cats as $c):?><label style="display:flex;align-items:center;gap:9px;padding:5px 0;font-size:13.5px;cursor:pointer"><input type="checkbox" name="cats[]" value="<?=$c['id']?>" <?=in_array($c['id'],$pcats)?'checked':''?>> <?=e($c['name'])?></label><?php endforeach;?>
 </div></div>
</div>
</form>
<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
<style>
.prodform{display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start}
@media(max-width:820px){.prodform{grid-template-columns:1fr;gap:16px}}
#descrEditor{height:200px}.ql-toolbar.ql-snow,.ql-container.ql-snow{border-color:var(--line)}.ql-toolbar.ql-snow{border-radius:10px 10px 0 0}.ql-container.ql-snow{border-radius:0 0 10px 10px;font-family:Inter;font-size:14px}
.dropzone{position:relative;display:flex;align-items:center;gap:14px;padding:18px;border:1.8px dashed var(--line2,#d4d4d8);border-radius:14px;background:var(--bg2,#fafafa);cursor:pointer;transition:.2s;overflow:hidden}
.dropzone:hover{border-color:#8ab4ff;background:#f5f9ff}
.dropzone.drag{border-color:#2f6bff;background:#eef4ff;box-shadow:0 0 0 4px rgba(47,107,255,.12)}
.dropzone.busy{pointer-events:none;opacity:.8}
.dz-ic{width:50px;height:50px;border-radius:12px;background:#fff;border:1px solid var(--line);display:grid;place-items:center;color:#2f6bff;flex-shrink:0}
.dz-tx{display:flex;flex-direction:column;gap:2px}.dz-tx b{font-size:14px;font-weight:600;color:var(--ink,#18181b)}.dz-tx span{font-size:12px;color:var(--mut2,#8a8a94)}
.dz-bar{position:absolute;left:0;bottom:0;height:3px;width:100%;background:transparent}.dz-barfill{height:100%;width:0;background:linear-gradient(90deg,#2f6bff,#6aa6ff);transition:width .25s}
.docrow{display:flex;gap:10px;margin-bottom:10px;align-items:center}
.docrow .dc-ic{width:36px;height:36px;border-radius:9px;background:var(--bg3,#f1f1f4);display:grid;place-items:center;color:#d04343;flex-shrink:0}
.imgrow .thumb,.imgrow img.thumb{width:46px;height:46px;border-radius:9px;object-fit:contain;background:var(--bg3,#f1f1f4);border:1px solid var(--line);flex-shrink:0}
.cam-row{display:flex;align-items:center;gap:12px;margin-top:11px;flex-wrap:wrap}
.cam-hint{font-size:12px;color:var(--mut2)}
@media(max-width:820px){.cam-row #camBtn{flex:1;justify-content:center;min-height:46px;font-size:14px}.cam-hint{flex-basis:100%;order:2}}</style>
<script>
(function(){ if(!window.Quill)return; var q=new Quill('#descrEditor',{theme:'snow',modules:{toolbar:[['bold','italic','underline'],[{header:[2,3,false]}],[{list:'ordered'},{list:'bullet'}],['link'],['clean']]}});
 var h=document.getElementById('descrHidden'); q.root.innerHTML=h.value||''; 
 q.closest=null; document.querySelector('form').addEventListener('submit',function(){ h.value=q.root.innerHTML; });
})();
function addSpec(){const d=document.createElement('div');d.className='specrow';d.style.cssText='display:flex;gap:10px;margin-bottom:10px';d.innerHTML='<input class="inp" name="spec_k[]" placeholder="Característica"><input class="inp" name="spec_v[]" placeholder="Valor"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()">✕</button>';document.getElementById('specBox').appendChild(d);}
function addImg(){const d=document.createElement('div');d.className='imgrow';d.style.cssText='display:flex;gap:10px;margin-bottom:10px;align-items:center';d.innerHTML='<div class="thumb"></div><input class="inp" name="img_url[]" placeholder="URL o ruta de imagen"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()">✕</button>';document.getElementById('imgBox').appendChild(d);}
/* ===== drag & drop subida ===== */
(function(){
 var CSRF=(document.querySelector('[name=csrf]')||{}).value||'';
 var IMGB='<?=e($GLOBALS['CFG']['img_base']??'')?>';
 function imgu(u){ if(!u)return ''; return /^https?:/.test(u)?u:IMGB+u.replace(/^\/+/,''); }
 function addImgRow(url){
  var box=document.getElementById('imgBox');
  // reutiliza una fila vacía si existe
  var empty=[].find.call(box.querySelectorAll('input[name="img_url[]"]'),i=>!i.value.trim());
  if(empty){ empty.value=url; var th=empty.parentNode.querySelector('.thumb'); if(th){ th.outerHTML='<img class="thumb" src="'+imgu(url)+'">'; } return; }
  var d=document.createElement('div'); d.className='imgrow'; d.style.cssText='display:flex;gap:10px;margin-bottom:10px;align-items:center';
  d.innerHTML='<img class="thumb" src="'+imgu(url)+'"><input class="inp" name="img_url[]" value="'+url.replace(/"/g,'&quot;')+'"><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()">✕</button>';
  box.appendChild(d);
 }
 function addDocRow(url,name){
  var box=document.getElementById('docBox');
  var d=document.createElement('div'); d.className='docrow';
  d.innerHTML='<span class="dc-ic"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4M9 13h6M9 17h6"/></svg></span>'
   +'<input class="inp" name="doc_name[]" value="'+(name||'Documento').replace(/"/g,'&quot;')+'"><input type="hidden" name="doc_url[]" value="'+url.replace(/"/g,'&quot;')+'">'
   +'<a href="'+imgu(url)+'" target="_blank" class="btn btn-g btn-sm">ver</a><button type="button" class="btn btn-d btn-sm" onclick="this.parentNode.remove()">✕</button>';
  box.appendChild(d);
 }
 function upload(file,zone){
  var fill=zone.querySelector('.dz-barfill'); zone.classList.add('busy'); if(fill)fill.style.width='15%';
  var fd=new FormData(); fd.append('file',file); fd.append('csrf',CSRF);
  var xhr=new XMLHttpRequest(); xhr.open('POST','upload.php');
  xhr.upload.onprogress=function(e){ if(e.lengthComputable&&fill)fill.style.width=(15+e.loaded/e.total*80)+'%'; };
  xhr.onload=function(){
   zone.classList.remove('busy'); if(fill)fill.style.width='100%'; setTimeout(function(){ if(fill)fill.style.width='0'; },500);
   var r={}; try{ r=JSON.parse(xhr.responseText); }catch(e){}
   if(!r.ok){ alert('Error al subir "'+file.name+'": '+(r.error||'desconocido')); return; }
   if(r.type==='image') addImgRow(r.url); else addDocRow(r.url,r.name);
  };
  xhr.onerror=function(){ zone.classList.remove('busy'); alert('Error de red al subir el archivo.'); };
  xhr.send(fd);
 }
 function wire(id){
  var zone=document.getElementById(id); if(!zone)return;
  var inp=zone.querySelector('input[type=file]');
  zone.addEventListener('click',function(e){ if(e.target.tagName!=='INPUT')inp.click(); });
  inp.addEventListener('change',function(){ [].forEach.call(inp.files,function(f){ upload(f,zone); }); inp.value=''; });
  ['dragenter','dragover'].forEach(function(ev){ zone.addEventListener(ev,function(e){ e.preventDefault(); zone.classList.add('drag'); }); });
  ['dragleave','drop'].forEach(function(ev){ zone.addEventListener(ev,function(e){ e.preventDefault(); zone.classList.remove('drag'); }); });
  zone.addEventListener('drop',function(e){ [].forEach.call(e.dataTransfer.files,function(f){ upload(f,zone); }); });
 }
 wire('dzImg'); wire('dzDoc');
 /* cámara: usa el mismo subidor, muestra progreso en la zona de imágenes */
 var camBtn=document.getElementById('camBtn'), camInput=document.getElementById('camInput'), dzI=document.getElementById('dzImg');
 if(camBtn&&camInput&&dzI){
  camBtn.addEventListener('click',function(){ camInput.click(); });
  camInput.addEventListener('change',function(){ [].forEach.call(camInput.files,function(f){ upload(f,dzI); }); camInput.value=''; });
 }
})();
</script>
<?php af();
