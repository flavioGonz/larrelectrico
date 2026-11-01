<?php
/* Tipo de cambio USD->UYU (dólar billete venta, Banco República). Base de la tienda: UYU. */
function fx_rate($pdo){
  static $r=null; if($r!==null) return $r; $r=41.0;
  try{ $x=$pdo->query("SELECT v FROM settings WHERE k='usd_rate'")->fetch(); if($x && (float)$x['v']>0) $r=(float)$x['v']; }catch(\Throwable $e){}
  return $r;
}
function fx_info($pdo){
  $o=['rate'=>fx_rate($pdo),'date'=>null,'src'=>null,'auto'=>1];
  try{ foreach($pdo->query("SELECT k,v FROM settings WHERE k IN('usd_rate_date','usd_rate_src','fx_auto')") as $x){
    if($x['k']==='usd_rate_date')$o['date']=$x['v'];
    if($x['k']==='usd_rate_src')$o['src']=$x['v'];
    if($x['k']==='fx_auto')$o['auto']=(int)$x['v'];
  } }catch(\Throwable $e){}
  return $o;
}
function fx_to_uyu($price,$currency,$rate){
  if($price===null) return null;
  return strtoupper((string)$currency)==='USD' ? round(((float)$price)*$rate,2) : (float)$price;
}
function fx_set($pdo,$rate,$src){
  $rate=round((float)$rate,4); if($rate<=0) return false;
  $up=$pdo->prepare("INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");
  $up->execute(['usd_rate',(string)$rate]);
  $up->execute(['usd_rate_date',date('Y-m-d')]);
  $up->execute(['usd_rate_src',(string)$src]);
  return true;
}
function fx_fetch_brou(){
  // 1) Dólar billete venta del Banco República (brou.com.uy)
  $ch=curl_init('https://www.brou.com.uy/web/guest/cotizaciones');
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_USERAGENT=>'Mozilla/5.0']);
  $h=(string)curl_exec($ch); curl_close($ch);
  if($h){
    $t=strip_tags($h); $t=str_replace('&nbsp;',' ',$t); $t=preg_replace('/\s+/',' ',$t);
    // "... Dólar 38,95000 41,45000 ..." (billete: compra venta). El eBROU trae "Dólar eBROU" y no matchea.
    if(preg_match('/D[oó]lar\s+([0-9.]+,[0-9]+)\s+([0-9.]+,[0-9]+)/u',$t,$m)){
      $venta=(float)str_replace(['.',','],['','.'],$m[2]);
      if($venta>5 && $venta<300) return ['rate'=>$venta,'src'=>'BROU billete venta'];
    }
  }
  // 2) respaldo: dolarapi UY (dólar venta)
  $ch=curl_init('https://uy.dolarapi.com/v1/cotizaciones');
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_USERAGENT=>'Mozilla/5.0']);
  $b=(string)curl_exec($ch); curl_close($ch);
  $j=json_decode($b,true);
  if(is_array($j)) foreach($j as $r){ if(strtoupper($r['moneda']??'')==='USD' && !empty($r['venta'])) return ['rate'=>(float)$r['venta'],'src'=>'dólar venta (ref. Uruguay)']; }
  return null;
}
function fx_maybe_update($pdo){
  try{
    $inf=fx_info($pdo);
    if($inf['auto']===0) return;                 // auto desactivado
    if($inf['date']===date('Y-m-d')) return;     // ya actualizado hoy
    $f=fx_fetch_brou();
    if($f && $f['rate']>0) fx_set($pdo,$f['rate'],$f['src']);
  }catch(\Throwable $e){}
}
