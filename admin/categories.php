<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  if(($_POST['act']??'')==='rename'){ $pdo->prepare("UPDATE categories SET name=? WHERE id=?")->execute([trim($_POST['name']),(int)$_POST['id']]); }
  if(($_POST['act']??'')==='new'){ $nid=(int)$pdo->query("SELECT COALESCE(MAX(id),500)+1 n FROM categories")->fetch()['n']; $pdo->prepare("INSERT INTO categories(id,name,slug,ordering) VALUES(?,?,?,0)")->execute([$nid,trim($_POST['name']),''] ); }
  exit('ok');
}
$rows=$pdo->query("SELECT c.*, (SELECT COUNT(*) FROM product_categories pc WHERE pc.category_id=c.id) n FROM categories c ORDER BY n DESC, c.name")->fetchAll();
ah('Categorías');
?>
<div class="toolbar"><div class="srch" style="max-width:300px"><?=ico('plus')?><input id="newcat" placeholder="Nueva categoría…"></div><button class="btn btn-pri" onclick="addCat()">Crear categoría</button></div>
<div class="panel"><table><thead><tr><th>Categoría</th><th>Productos</th><th></th></tr></thead><tbody>
<?php foreach($rows as $c):?><tr><td><input class="inp catname" data-id="<?=$c['id']?>" value="<?=e($c['name'])?>" style="max-width:360px;border-color:transparent;background:transparent" onblur="rename(<?=$c['id']?>,this.value)"></td>
<td><span class="badge <?=$c['n']?'new':'no'?>"><?=(int)$c['n']?></span></td>
<td><a class="btn btn-g btn-sm" href="products.php?q=<?=urlencode($c['name'])?>"><?=ico('box',15)?>Ver</a></td></tr><?php endforeach;?>
</tbody></table></div>
<script>const CSRF='<?=csrf()?>';
function post(d){return fetch('categories.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({csrf:CSRF,...d})});}
function rename(id,name){post({act:'rename',id,name});}
function addCat(){const v=document.getElementById('newcat').value.trim();if(v)post({act:'new',name:v}).then(()=>location.reload());}
</script>
<?php af();
