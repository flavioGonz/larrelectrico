<?php
session_start();
$CFG = require(__DIR__.'/../config.php');
function db(){ static $pdo=null; global $CFG;
  if($pdo===null){ $pdo=new PDO("mysql:host={$CFG['db_host']};dbname={$CFG['db_name']};charset=utf8mb4",$CFG['db_user'],$CFG['db_pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }
  return $pdo; }
function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function money($n){ if($n===null||$n==='') return null; return '$'.number_format((float)$n,2,',','.'); }
function img_url($u){ global $CFG; if(!$u) return null; return (strpos($u,'http')===0)?$u:$CFG['img_base'].ltrim($u,'/'); }
function slugify($s){ $s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s); $s=preg_replace('/[^a-zA-Z0-9]+/','-',strtolower($s)); return trim($s,'-'); }
function user(){ return $_SESSION['user'] ?? null; }
// Overrides de configuración editables desde el admin (tabla settings)
function load_settings(){ global $CFG; try{ $pdo=db(); $pdo->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(60) PRIMARY KEY, v TEXT) DEFAULT CHARSET=utf8mb4");
  foreach($pdo->query("SELECT k,v FROM settings") as $r){ $v=$r['v']; if($r['k']==='pay_methods'){ $d=json_decode($v,true); if(is_array($d)) $CFG['pay_methods']=$d; continue; } $CFG[$r['k']]=$v; }
}catch(Exception $e){} }
load_settings();
function cat_icon($name){
  $n=mb_strtoupper($name,'UTF-8');
  if(preg_match('/CABLE|GOMA|SUPER|CONDUCT|FLEX|UNIPOLAR/',$n)) return 'cable';
  if(preg_match('/DIFERENC/',$n)) return 'shield';
  if(preg_match('/TERMIC|T.RMIC|LLAVE|BREAKER|MAGNET/',$n)) return 'bolt';
  if(preg_match('/TABLERO|GABINET|CAJA|MODUL/',$n)) return 'grid';
  if(preg_match('/LUZ|LED|ILUMIN|LAMPAR|REFLECT|PANEL|TUBO|FOCO/',$n)) return 'bulb';
  if(preg_match('/DUCTO|CANAL|CA.O|TUBO|CORRUG/',$n)) return 'pipe';
  if(preg_match('/TOMA|ENCHUFE|PRESTA|COMBINAC|LLAVE DE LUZ|PUNTO/',$n)) return 'plug';
  if(preg_match('/CONTACT|RELE|REL.|AUTOMAT|CONTROL|TEMPOR|TERM/',$n)) return 'chip';
  if(preg_match('/FERRET|TORNILL|GRAMPA|PRECINT|ACCESOR|HERRAM/',$n)) return 'tool';
  return 'package';
}
function icon($name,$cls=''){
  $p=[
   'bolt'=>'<path d="M13 2 3 14h7l-1 8 10-12h-7z"/>',
   'cable'=>'<path d="M4 4v6a4 4 0 0 0 4 4h8a4 4 0 0 1 4 4v2"/><circle cx="4" cy="3" r="1.6"/><circle cx="20" cy="21" r="1.6"/>',
   'shield'=>'<path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
   'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
   'bulb'=>'<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.3 1 2.5h6c0-1.2.3-1.8 1-2.5A6 6 0 0 0 12 3z"/>',
   'pipe'=>'<path d="M4 8h10a4 4 0 0 1 4 4v8"/><path d="M4 5v6M14 17h8"/>',
   'plug'=>'<path d="M9 2v6M15 2v6M7 8h10v3a5 5 0 0 1-10 0z"/><path d="M12 16v6"/>',
   'chip'=>'<rect x="7" y="7" width="10" height="10" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>',
   'tool'=>'<path d="M14.5 5.5a3.5 3.5 0 0 0-4.8 4.5l-6 6 2.3 2.3 6-6a3.5 3.5 0 0 0 4.5-4.8l-2 2-2-2z"/>',
   'package'=>'<path d="m12 2 8 4.5v9L12 20l-8-4.5v-9z"/><path d="m4 7 8 4.5L20 7M12 20v-8.5"/>',
   'search'=>'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
   'cart'=>'<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2l2.5 13h12l2-9H6"/>',
   'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
   'menu'=>'<path d="M3 6h18M3 12h18M3 18h18"/>',
   'x'=>'<path d="M6 6l12 12M18 6 6 18"/>',
   'chev'=>'<path d="m6 9 6 6 6-6"/>',
   'chevr'=>'<path d="m9 6 6 6-6 6"/>',
   'check'=>'<path d="M20 6 9 17l-5-5"/>',
   'plus'=>'<path d="M12 5v14M5 12h14"/>',
   'minus'=>'<path d="M5 12h14"/>',
   'truck'=>'<path d="M3 6h11v9H3zM14 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
   'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
   'headset'=>'<path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13a2 2 0 0 1 2 2v2a2 2 0 0 1-4 0v-2a2 2 0 0 1 2-2zM20 13a2 2 0 0 0-2 2v2a2 2 0 0 0 4 0v-2a2 2 0 0 0-2-2z"/><path d="M18 18a6 6 0 0 1-6 4"/>',
   'wa'=>'<path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.5A10 10 0 1 0 12 2z"/><path d="M8.5 8c-.3 0-.6.1-.8.4-.3.3-.9.9-.9 2.1s.9 2.4 1.1 2.6c.1.2 1.8 2.9 4.5 3.9 2.2.9 2.7.7 3.2.7.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2-.1-.1-.3-.2-.6-.4z"/>',
   'star'=>'<path d="m12 3 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8L3.6 9.1l5.8-.8z"/>',
   'filter'=>'<path d="M3 5h18l-7 8v6l-4-2v-4z"/>',
   'pin'=>'<path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
   'mail'=>'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
   'tag'=>'<path d="M3 3h8l10 10-8 8L3 11z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
   'arrow'=>'<path d="M5 12h14M13 6l6 6-6 6"/>',
   'logout'=>'<path d="M15 4h4v16h-4"/><path d="M10 12H3m4-4-4 4 4 4"/>',
  ];
  $d=$p[$name] ?? $p['package'];
  return '<svg class="ic '.$cls.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">'.$d.'</svg>';
}
