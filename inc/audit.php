<?php
/* Auditoría de productos: fechas + bitácora de cambios (quién / cuándo / qué) */

function audit_now(){
  try{ return (new DateTime('now', new DateTimeZone('America/Montevideo')))->format('Y-m-d H:i:s'); }
  catch(\Throwable $e){ return date('Y-m-d H:i:s'); }
}

/* Agrega created_at/updated_at a products y crea la tabla product_audit (idempotente). */
function audit_migrate($pdo){
  static $done=false; if($done) return; $done=true;
  try{
    $cols=$pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products'")->fetchAll(PDO::FETCH_COLUMN);
    if($cols){
      if(!in_array('created_at',$cols)) $pdo->exec("ALTER TABLE products ADD COLUMN created_at DATETIME NULL");
      if(!in_array('updated_at',$cols)) $pdo->exec("ALTER TABLE products ADD COLUMN updated_at DATETIME NULL");
    }
  }catch(\Throwable $e){}
  try{
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_audit(
      id INT AUTO_INCREMENT PRIMARY KEY,
      product_id INT,
      action VARCHAR(24),
      changes TEXT,
      admin_id INT,
      admin_name VARCHAR(120),
      note VARCHAR(255),
      created_at DATETIME,
      INDEX idx_pid(product_id), INDEX idx_cr(created_at)
    ) DEFAULT CHARSET=utf8mb4");
  }catch(\Throwable $e){}
}

/* Registra un evento. $changes = [ ['f'=>'price','label'=>'Precio','old'=>..,'new'=>..], ... ] */
function audit_log($pdo,$pid,$action,$changes=[],$note=''){
  try{
    $a=function_exists('admin')?(admin()?:[]):[];
    $pdo->prepare("INSERT INTO product_audit(product_id,action,changes,admin_id,admin_name,note,created_at) VALUES(?,?,?,?,?,?,?)")
        ->execute([(int)$pid,$action,json_encode($changes,JSON_UNESCAPED_UNICODE),(int)($a['id']??0),(string)($a['name']??'Sistema'),mb_substr((string)$note,0,255),audit_now()]);
  }catch(\Throwable $e){}
}

/* Compara dos arrays sobre un set de campos [campo=>Etiqueta] y devuelve los cambios. */
function audit_diff($before,$after,$fields){
  $ch=[];
  foreach($fields as $f=>$label){
    $o=$before[$f]??null; $n=$after[$f]??null;
    if(is_numeric($o)&&is_numeric($n)){ if(abs((float)$o-(float)$n)<0.0001) continue; }
    elseif((string)$o===(string)$n) continue;
    $ch[]=['f'=>$f,'label'=>$label,'old'=>$o,'new'=>$n];
  }
  return $ch;
}

/* Etiqueta/clase para el badge de acción en la UI. */
function audit_badge($action){
  $m=[
    'crear'=>['Alta','ok'],
    'editar'=>['Edición','am'],
    'precio'=>['Precio','am'],
    'stock'=>['Stock','am'],
    'destacado'=>['Destacado','new'],
    'eliminar'=>['Eliminado','no'],
    'masivo'=>['Masivo','new'],
  ];
  return $m[$action]??[ucfirst($action),'new'];
}

/* Formatea un valor viejo/nuevo para mostrar en el historial. */
function audit_val($v){
  if($v===null||$v==='') return '—';
  $s=(string)$v;
  return mb_strlen($s)>44?mb_substr($s,0,44).'…':$s;
}
