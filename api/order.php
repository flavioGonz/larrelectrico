<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
$in=json_decode(file_get_contents('php://input'),true)?:[];
try{
 $pdo=db();
 $items=$in['items']??[];
 if(!$items) { http_response_code(400); echo json_encode(['error'=>'carrito vacío']); exit; }
 $ids=array_map(fn($i)=>(int)$i['id'],$items);
 $ph=implode(',',array_fill(0,count($ids),'?'));
 $s=$pdo->prepare("SELECT id,name,sku,price FROM products WHERE id IN ($ph)"); $s->execute($ids);
 $pr=[]; foreach($s->fetchAll() as $r)$pr[$r['id']]=$r;
 $total=0; $lines=[];
 foreach($items as $it){ $p=$pr[(int)$it['id']]??null; if(!$p)continue; $q=max(1,(int)$it['qty']); $total+=((float)$p['price'])*$q; $lines[]=['id'=>$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'qty'=>$q,'price'=>(float)$p['price']]; }
 $u=user();
 $cust=trim($in['customer']??($u['name']??'')); $email=trim($in['email']??($u['email']??'')); $phone=trim($in['phone']??($u['phone']??''));
 $pdo->prepare("INSERT INTO orders(user_id,customer,email,phone,total,items,status,created) VALUES(?,?,?,?,?,?,'nuevo',NOW())")
     ->execute([$u['id']??null,$cust,$email,$phone,$total,json_encode($lines,JSON_UNESCAPED_UNICODE)]);
 $oid=$pdo->lastInsertId();
 echo json_encode(['ok'=>true,'order'=>(int)$oid,'total'=>$total,'lines'=>$lines]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
