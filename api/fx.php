<?php
require(__DIR__.'/../inc/init.php');
require_once(__DIR__.'/../inc/fx.php');
header('Content-Type: application/json; charset=utf-8');
global $CFG; $pdo=db();
$do=$_GET['do']??'rate';

if($do==='update'){            // forzar actualización (cron o lazy externo)
  if(($_GET['key']??'')!==($CFG['cron_key']??'x')){ http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
  $f=fx_fetch_brou();
  if($f){ fx_set($pdo,$f['rate'],$f['src']); echo json_encode(['ok'=>true]+fx_info($pdo)); }
  else { echo json_encode(['ok'=>false,'error'=>'sin cotización']+fx_info($pdo)); }
  exit;
}
if($do==='setup'){             // una sola vez: columna currency + BRUMA=USD + rate inicial
  if(($_GET['key']??'')!==($CFG['install_key']??'x')){ http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
  $log=[];
  try{ $cols=array_column($pdo->query("SHOW COLUMNS FROM products")->fetchAll(),'Field');
    if(!in_array('currency',$cols)){ $pdo->exec("ALTER TABLE products ADD COLUMN currency VARCHAR(3) NOT NULL DEFAULT 'UYU'"); $log[]='columna currency creada'; }
    else $log[]='columna currency ya existía';
  }catch(\Throwable $e){ $log[]='ALTER error: '.$e->getMessage(); }
  try{ $n=$pdo->exec("UPDATE products SET currency='USD' WHERE brand='BRUMA'"); $log[]="BRUMA->USD filas=$n"; }catch(\Throwable $e){ $log[]='update BRUMA error: '.$e->getMessage(); }
  try{ $pdo->prepare("INSERT INTO settings(k,v) VALUES('fx_auto','1') ON DUPLICATE KEY UPDATE v=v")->execute(); }catch(\Throwable $e){}
  $f=fx_fetch_brou(); if($f){ fx_set($pdo,$f['rate'],$f['src']); $log[]='rate inicial '.$f['rate'].' ('.$f['src'].')'; } else $log[]='no se pudo obtener rate inicial';
  echo json_encode(['ok'=>true,'log'=>$log]+fx_info($pdo)); exit;
}
// default: devolver cotización actual
echo json_encode(fx_info($pdo));
