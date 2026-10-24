<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
try{
  $pdo=db();
  $cats=$pdo->query("SELECT c.id,c.name,c.slug,c.ordering,(SELECT COUNT(*) FROM product_categories pc WHERE pc.category_id=c.id) n FROM categories c ORDER BY c.ordering, c.name")->fetchAll();
  $prods=$pdo->query("SELECT id,sku,name,slug,brand,price,stock,unit,s_desc,descr,img,featured,sales,video,vendor_id FROM products ORDER BY featured DESC, sales DESC, id DESC")->fetchAll();
  // map categories per product
  $pc=$pdo->query("SELECT product_id,category_id FROM product_categories")->fetchAll();
  $catName=[]; foreach($cats as $c)$catName[$c['id']]=$c['name'];
  $map=[]; foreach($pc as $r){ $map[$r['product_id']][]=(int)$r['category_id']; }
  $imgsAll=[]; foreach($pdo->query("SELECT product_id,url FROM product_images ORDER BY ordering") as $r){ $imgsAll[$r['product_id']][]=$r['url']; }
  $specsAll=[]; foreach($pdo->query("SELECT product_id,k,v FROM product_specs ORDER BY ordering") as $r){ $specsAll[$r['product_id']][]=['k'=>$r['k'],'v'=>$r['v']]; }
  $vend=[]; foreach($pdo->query("SELECT id,name,logo,site FROM vendors") as $r){ $vend[$r['id']]=$r; }
  foreach($prods as &$p){
    $p['specs']=$specsAll[$p['id']]??[];
    $p['vendor']=(!empty($p['vendor_id'])&&isset($vend[$p['vendor_id']]))?$vend[$p['vendor_id']]:null;
    $ids=$map[$p['id']]??[];
    $p['catids']=array_values(array_unique($ids));
    $p['categories']=array_values(array_filter(array_map(fn($i)=>$catName[$i]??null,$p['catids'])));
    $p['images']=$imgsAll[$p['id']]??($p['img']?[$p['img']]:[]);
    $p['price']=$p['price']!==null?(float)$p['price']:null;
    $p['stock']=(int)$p['stock']; $p['id']=(int)$p['id']; $p['featured']=(int)$p['featured']; $p['sales']=(int)$p['sales'];
  }
  echo json_encode(['site'=>'larrelectrico.uy','generated'=>date('c'),'categories'=>$cats,'count'=>count($prods),'products'=>$prods], JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
}catch(Exception $ex){ http_response_code(500); echo json_encode(['error'=>$ex->getMessage()]); }
