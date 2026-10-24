<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$updir=__DIR__.'/../assets/vendors'; if(!is_dir($updir)) @mkdir($updir,0755,true);
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $vid=(int)$_POST['id']; $logo=trim($_POST['logo']??'');
  if(!empty($_FILES['logofile']['tmp_name']) && is_uploaded_file($_FILES['logofile']['tmp_name'])){
    $ext=strtolower(pathinfo($_FILES['logofile']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,['png','jpg','jpeg','webp','svg','gif'])){ $fn='v'.$vid.'_'.time().'.'.$ext; if(move_uploaded_file($_FILES['logofile']['tmp_name'],"$updir/$fn")) $logo='final/assets/vendors/'.$fn; }
  }
  $pdo->prepare("UPDATE vendors SET logo=?, site=?, featured=? WHERE id=?")->execute([$logo,trim($_POST['site']??''),(int)!empty($_POST['featured']),$vid]);
  flash('Vendor actualizado.'); header('Location: vendors.php'); exit;
}
$rows=$pdo->query("SELECT v.*, (SELECT COUNT(*) FROM products p WHERE p.vendor_id=v.id) n FROM vendors v ORDER BY v.name")->fetchAll();
ah('Vendors');
?>
<p style="color:var(--mut);font-size:14px;margin-bottom:18px">Los vendors se crean automáticamente desde las marcas de los productos. Subí su logo para que aparezca en la tienda.</p>
<div class="cards" style="grid-template-columns:repeat(auto-fill,minmax(340px,1fr))">
<?php foreach($rows as $v): $lg=imgu($v['logo']); ?>
<form class="panel" method="post" enctype="multipart/form-data" style="margin:0"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$v['id']?>">
<div style="padding:18px 20px">
 <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
  <div style="width:70px;height:48px;border:1px solid var(--line);border-radius:10px;display:grid;place-items:center;background:#fff;overflow:hidden">
   <?php if($lg):?><img src="<?=e($lg)?>" style="max-width:100%;max-height:100%;object-fit:contain" onerror="this.style.display='none'"><?php else:?><span style="color:var(--mut2);font-size:11px">sin logo</span><?php endif;?></div>
  <div><div style="font-weight:600"><?=e($v['name'])?></div><div style="color:var(--mut2);font-size:12.5px"><?=(int)$v['n']?> productos</div></div>
 </div>
 <div class="field"><label>Logo (subir archivo)</label><input class="inp" type="file" name="logofile" accept="image/*"></div>
 <div class="field"><label>o URL del logo</label><input class="inp" name="logo" value="<?=e($v['logo'])?>" placeholder="https://..."></div>
 <div class="field"><label>Sitio web</label><input class="inp" name="site" value="<?=e($v['site'])?>" placeholder="https://..."></div>
 <label style="display:flex;align-items:center;gap:9px;font-size:13.5px;margin-bottom:14px;cursor:pointer"><input type="checkbox" name="featured" <?=$v['featured']?'checked':''?>> Destacar en la tienda</label>
 <button class="btn btn-pri btn-block" style="width:100%;justify-content:center"><?=ico('save',16)?>Guardar</button>
</div></form>
<?php endforeach;?>
</div>
<?php af();
