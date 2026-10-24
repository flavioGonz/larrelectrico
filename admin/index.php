<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
function q1($sql){ try{ return db()->query($sql)->fetch(); }catch(Exception $e){ return null; } }
function qa_($sql){ try{ return db()->query($sql)->fetchAll(); }catch(Exception $e){ return []; } }
$u=admin();
$nprod=($r=q1("SELECT COUNT(*) c FROM products"))?$r['c']:0;
$nord =($r=q1("SELECT COUNT(*) c FROM orders"))?$r['c']:0;
$ncli =($r=q1("SELECT COUNT(*) c FROM users WHERE role<>'admin'"))?$r['c']:0;
$rev  =($r=q1("SELECT COALESCE(SUM(total),0) s FROM orders"))?$r['s']:0;
$rev_month=($r=q1("SELECT COALESCE(SUM(total),0) s FROM orders WHERE created>=DATE_FORMAT(NOW(),'%Y-%m-01')"))?$r['s']:0;
$rev_today=($r=q1("SELECT COALESCE(SUM(total),0) s FROM orders WHERE DATE(created)=CURDATE()"))?$r['s']:0;
$new_orders=($r=q1("SELECT COUNT(*) c FROM orders WHERE status='nuevo'"))?$r['c']:0;
$nlow=($r=q1("SELECT COUNT(*) c FROM products WHERE stock IS NOT NULL AND stock<=5"))?$r['c']:0;
$top=qa_("SELECT id,name,brand,price,sales,img FROM products ORDER BY sales DESC LIMIT 6");
$low=qa_("SELECT id,name,brand,stock,img FROM products WHERE stock IS NOT NULL AND stock<=5 ORDER BY stock ASC LIMIT 6");
$orders=qa_("SELECT id,customer,total,status,created FROM orders ORDER BY id DESC LIMIT 6");
$h=(int)date('H'); $saludo = $h<12?'Buenos días':($h<19?'Buenas tardes':'Buenas noches');
$fecha = date('d/m/Y');
function stbadge($s){ $m=['nuevo'=>'new','pagado'=>'ok','en proceso'=>'am','entregado'=>'ok','cancelado'=>'no']; return $m[strtolower($s)]??'new'; }
ah('Inicio');
?>
<style>
.dash-hero{background:linear-gradient(135deg,#0b1f49,#0a4ea0);border-radius:20px;padding:22px 22px 24px;color:#eaf1ff;position:relative;overflow:hidden;margin-bottom:18px}
.dash-hero::after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(26,158,75,.4),transparent 70%);top:-120px;right:-60px}
.dash-hero>*{position:relative;z-index:2}
.dash-hero .sal{font-size:13px;color:#aec6ef}
.dash-hero .nm{font-size:23px;font-weight:800;letter-spacing:-.02em;margin-top:2px}
.dash-hero .dt{font-size:12.5px;color:#8fb0e6;margin-top:6px}
.kpi4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.kpic{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px;position:relative;overflow:hidden}
.kpic .kc{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;margin-bottom:11px}
.kpic .kc svg{width:19px;height:19px}
.kpic .kl{font-size:12px;color:var(--mut2);font-weight:500}
.kpic .kv{font-size:22px;font-weight:800;letter-spacing:-.02em;margin-top:3px;line-height:1.1}
.kpic.b1 .kc{background:#eef4ff;color:#0071e3}.kpic.b2 .kc{background:#eafaf0;color:#1a9e4b}
.kpic.b3 .kc{background:#fff4e9;color:#ff8a00}.kpic.b4 .kc{background:#fdeef3;color:#e0245e}
.qa{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
.qab{background:#fff;border:1px solid var(--line);border-radius:16px;padding:16px 12px;display:flex;flex-direction:column;align-items:center;gap:9px;text-align:center;transition:.18s;color:var(--ink)}
.qab:hover{border-color:var(--blue);box-shadow:0 10px 26px -14px rgba(0,113,227,.5);transform:translateY(-2px)}
.qab .qi{width:46px;height:46px;border-radius:13px;background:linear-gradient(135deg,#eaf2ff,#dcebff);color:#0071e3;display:grid;place-items:center}
.qab .qi svg{width:23px;height:23px}
.qab span{font-size:12.5px;font-weight:600}
.alerts{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
.alert{display:flex;align-items:center;gap:13px;background:#fff;border:1px solid var(--line);border-left-width:4px;border-radius:14px;padding:13px 16px;transition:.16s}
.alert:hover{box-shadow:var(--sh)}
.alert.warn{border-left-color:#ff8a00}.alert.info{border-left-color:#0071e3}.alert.okk{border-left-color:#1a9e4b}
.alert .ai{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;flex-shrink:0}
.alert.warn .ai{background:#fff4e9;color:#ff8a00}.alert.info .ai{background:#eef4ff;color:#0071e3}.alert.okk .ai{background:#eafaf0;color:#1a9e4b}
.alert .at{flex:1;min-width:0}.alert .at b{font-size:14.5px;font-weight:700;display:block}.alert .at span{font-size:12.5px;color:var(--mut2)}
.alert .ac{color:var(--mut2)}
.dpanel{background:#fff;border:1px solid var(--line);border-radius:18px;overflow:hidden;margin-bottom:18px}
.dpanel .dh{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
.dpanel .dh h2{font-size:15px;font-weight:700}
.dl{padding:6px 10px}
.dl-item{display:flex;align-items:center;gap:13px;padding:11px 8px;border-radius:12px;transition:.14s}
.dl-item:hover{background:var(--bg,#f5f6f8)}
.dl-item .dthumb{width:46px;height:46px;border-radius:11px;object-fit:contain;background:var(--bg3,#fafafc);border:1px solid var(--line);padding:5px;flex-shrink:0}
.dl-item .di{flex:1;min-width:0}
.dl-item .di b{font-size:14px;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dl-item .di span{font-size:12px;color:var(--mut2)}
.dl-item .dr{text-align:right;flex-shrink:0}
.dl-item .dr .pr{font-size:14px;font-weight:700}
.dl-item .dr .sb{font-size:11.5px;color:var(--mut2)}
.pill{font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px}
.pill.lowk{background:#fdeef3;color:#e0245e}.pill.midk{background:#fff4e9;color:#ff8a00}
.badge2{font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px}
.badge2.new{background:#e9f2ff;color:#0071e3}.badge2.ok{background:#eafaf0;color:#1a9e4b}.badge2.am{background:#fff4e9;color:#ff8a00}.badge2.no{background:#f2f2f5;color:#86868b}
@media(max-width:820px){
 .kpi4{grid-template-columns:1fr 1fr;gap:11px}
 .qa{grid-template-columns:1fr 1fr;gap:11px}
 .dash-hero .nm{font-size:21px}
}
</style>

<div class="dash-hero">
 <div class="sal"><?=$saludo?>,</div>
 <div class="nm"><?=e($u['name'])?> 👋</div>
 <div class="dt"><?=ico('dash',13)?> Resumen de tu tienda · <?=$fecha?></div>
</div>

<div class="kpi4">
 <div class="kpic b1"><div class="kc"><?=ico('star',19)?></div><div class="kl">Ventas hoy</div><div class="kv"><?=money($rev_today)?></div></div>
 <div class="kpic b2"><div class="kc"><?=ico('star',19)?></div><div class="kl">Ventas del mes</div><div class="kv"><?=money($rev_month)?></div></div>
 <div class="kpic b3"><div class="kc"><?=ico('cart',19)?></div><div class="kl">Pedidos nuevos</div><div class="kv"><?=(int)$new_orders?></div></div>
 <div class="kpic b4"><div class="kc"><?=ico('box',19)?></div><div class="kl">Stock bajo</div><div class="kv"><?=(int)$nlow?></div></div>
</div>

<div class="qa">
 <a class="qab" href="product.php"><span class="qi"><?=ico('plus',23)?></span><span>Nuevo producto</span></a>
 <a class="qab" href="orders.php"><span class="qi"><?=ico('cart',23)?></span><span>Pedidos</span></a>
 <a class="qab" href="products.php"><span class="qi"><?=ico('box',23)?></span><span>Productos</span></a>
 <a class="qab" href="carts.php"><span class="qi"><?=ico('bell',23)?></span><span>Carritos</span></a>
</div>

<div class="alerts">
 <?php if($new_orders>0):?>
 <a class="alert warn"><span class="ai"><?=ico('cart',19)?></span><div class="at"><b><?=(int)$new_orders?> pedido<?=$new_orders==1?'':'s'?> nuevo<?=$new_orders==1?'':'s'?></b><span>Esperando que los proceses</span></div><span class="ac"><?=ico('chev',18)?></span></a>
 <?php endif;?>
 <?php if($nlow>0):?>
 <a class="alert info" href="products.php"><span class="ai"><?=ico('box',19)?></span><div class="at"><b><?=(int)$nlow?> producto<?=$nlow==1?'':'s'?> con stock bajo</b><span>Revisá y reponé antes de quedarte sin stock</span></div><span class="ac"><?=ico('chev',18)?></span></a>
 <?php endif;?>
 <?php if($new_orders==0 && $nlow==0):?>
 <div class="alert okk"><span class="ai"><?=ico('star',19)?></span><div class="at"><b>Todo al día</b><span>Sin pedidos pendientes ni stock crítico</span></div></div>
 <?php endif;?>
</div>

<?php if($low): ?>
<div class="dpanel"><div class="dh"><h2>⚠️ Stock bajo</h2><a class="btn btn-g btn-sm" href="products.php"><?=ico('box',15)?>Gestionar</a></div>
<div class="dl">
<?php foreach($low as $p): $im=imgu($p['img']); $st=(int)$p['stock']; ?>
 <a class="dl-item" href="product.php?id=<?=$p['id']?>">
  <?php if($im):?><img class="dthumb" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="dthumb"></div><?php endif;?>
  <div class="di"><b><?=e($p['name'])?></b><span><?=e($p['brand'])?:'Sin marca'?></span></div>
  <div class="dr"><span class="pill <?=$st<=2?'lowk':'midk'?>"><?=$st?> en stock</span></div>
 </a>
<?php endforeach;?>
</div></div>
<?php endif;?>

<div class="dpanel"><div class="dh"><h2>🔥 Más vendidos</h2><a class="btn btn-g btn-sm" href="products.php?sort=sales"><?=ico('box',15)?>Ver todos</a></div>
<div class="dl">
<?php foreach($top as $p): $im=imgu($p['img']); ?>
 <a class="dl-item" href="product.php?id=<?=$p['id']?>">
  <?php if($im):?><img class="dthumb" src="<?=e($im)?>" onerror="this.style.visibility='hidden'"><?php else:?><div class="dthumb"></div><?php endif;?>
  <div class="di"><b><?=e($p['name'])?></b><span><?=e($p['brand'])?:'Sin marca'?></span></div>
  <div class="dr"><div class="pr"><?=money($p['price'])?></div><div class="sb"><?=(int)$p['sales']?> vendidos</div></div>
 </a>
<?php endforeach;?>
</div></div>

<div class="dpanel"><div class="dh"><h2>🧾 Pedidos recientes</h2><a class="btn btn-g btn-sm" href="orders.php">Ver todos</a></div>
<?php if(!$orders):?><div style="padding:26px;color:var(--mut2);font-size:14px">Todavía no hay pedidos.</div>
<?php else:?><div class="dl">
<?php foreach($orders as $o):?>
 <a class="dl-item" href="orders.php">
  <div class="dthumb" style="display:grid;place-items:center;color:var(--mut2);font-weight:700;font-size:13px">#<?=$o['id']?></div>
  <div class="di"><b><?=e($o['customer'])?:'Sin nombre'?></b><span><?=e(substr($o['created'],0,16))?></span></div>
  <div class="dr"><div class="pr"><?=money($o['total'])?></div><span class="badge2 <?=stbadge($o['status'])?>"><?=e($o['status'])?></span></div>
 </a>
<?php endforeach;?>
</div><?php endif;?></div>
<?php af();
