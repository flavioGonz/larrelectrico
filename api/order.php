<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
$in=json_decode(file_get_contents('php://input'),true)?:[];
try{
 $pdo=db();
 $items=$in['items']??[];
 if(!$items) { http_response_code(400); echo json_encode(['error'=>'carrito vacío']); exit; }
 $ids=array_values(array_unique(array_map(fn($i)=>(int)$i['id'],$items)));
 $ph=implode(',',array_fill(0,count($ids),'?'));
 $s=$pdo->prepare("SELECT id,name,sku,price,stock FROM products WHERE id IN ($ph)"); $s->execute($ids);
 $pr=[]; foreach($s->fetchAll() as $r)$pr[$r['id']]=$r;
 $subtotal=0; $lines=[]; $warn=[];
 foreach($items as $it){
   $p=$pr[(int)$it['id']]??null; if(!$p)continue;
   $q=max(1,(int)$it['qty']);
   $price=(float)$p['price'];
   $subtotal+=$price*$q;
   $stk=(int)$p['stock'];
   if($stk<=0) $warn[]=['id'=>(int)$p['id'],'name'=>$p['name'],'type'=>'bajo_pedido'];
   elseif($stk<$q) $warn[]=['id'=>(int)$p['id'],'name'=>$p['name'],'type'=>'stock_parcial','disponible'=>$stk,'pedido'=>$q];
   $lines[]=['id'=>(int)$p['id'],'name'=>$p['name'],'sku'=>$p['sku'],'qty'=>$q,'price'=>$price,'stock'=>$stk];
 }
 if(!$lines){ http_response_code(400); echo json_encode(['error'=>'productos no válidos']); exit; }
 $IVA=0.22; $iva=round($subtotal*$IVA,2); $total=round($subtotal+$iva,2);
 $u=function_exists('user')?user():null;
 $cust=trim($in['customer']??($u['name']??''));
 $email=trim($in['email']??($u['email']??''));
 $phone=trim($in['phone']??($u['phone']??''));
 $note=trim($in['note']??'');
 $pay=trim($in['payment']??'');            // transfer|giros|cod|pickup|mp
 global $CFG;
 $disc = ($pay==='transfer') ? (float)($CFG['transfer_discount']??0.10) : 0;   // 10% off por transferencia
 $disc_amt = round($total*$disc,2);
 $pagar = round($total-$disc_amt,2);       // lo que realmente paga el cliente
 // total guardado = lo que paga el cliente (con descuento aplicado si corresponde)
 $payload=['lines'=>$lines,'subtotal'=>$subtotal,'iva'=>$iva,'total'=>$total,'discount'=>$disc,'discount_amt'=>$disc_amt,'total_pagar'=>$pagar,'note'=>$note,'payment'=>$pay];
 $pdo->prepare("INSERT INTO orders(user_id,customer,email,phone,total,items,status,created) VALUES(?,?,?,?,?,?,'nuevo',NOW())")
     ->execute([$u['id']??null,$cust,$email,$phone,$pagar,json_encode($payload,JSON_UNESCAPED_UNICODE)]);
 $oid=$pdo->lastInsertId();
 if(!empty($u['id'])){ try{ $pdo->prepare("DELETE FROM carts WHERE user_id=?")->execute([$u['id']]); }catch(Exception $e){} } // carrito convertido → sin recordatorio
 echo json_encode(['ok'=>true,'order'=>(int)$oid,'subtotal'=>$subtotal,'iva'=>$iva,'total'=>$total,'discount'=>$disc,'discount_amt'=>$disc_amt,'total_pagar'=>$pagar,'lines'=>$lines,'warnings'=>$warn]);
}catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
