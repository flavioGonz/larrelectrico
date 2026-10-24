<?php
session_start();
$CFG=require(__DIR__.'/../config.php');
function db(){ static $p=null; global $CFG; if($p===null){ $p=new PDO("mysql:host={$CFG['db_host']};dbname={$CFG['db_name']};charset=utf8mb4",$CFG['db_user'],$CFG['db_pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); } return $p; }
(function(){ global $CFG; try{ $pdo=db(); $pdo->exec("CREATE TABLE IF NOT EXISTS settings(k VARCHAR(60) PRIMARY KEY, v TEXT) DEFAULT CHARSET=utf8mb4"); foreach($pdo->query("SELECT k,v FROM settings") as $r){ if($r['k']==='pay_methods'){ $d=json_decode($r['v'],true); if(is_array($d))$CFG['pay_methods']=$d; continue;} $CFG[$r['k']]=$r['v']; } }catch(Exception $e){} })();
function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function money($n){ return $n===null?'—':'$'.number_format((float)$n,2,',','.'); }
function imgu($u){ global $CFG; if(!$u)return null; return strpos($u,'http')===0?$u:$CFG['img_base'].ltrim($u,'/'); }
function admin(){ return $_SESSION['admin'] ?? null; }
function tip($t){ return '<span class="tip">?<span class="tipbox">'.e($t).'</span></span>'; }
function require_admin(){ if(!admin()){ header('Location: login.php'); exit; } }
function csrf(){ if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function check_csrf(){ if(($_POST['csrf']??'')!==($_SESSION['csrf']??'x')){ http_response_code(400); exit('CSRF'); } }
function flash($m=null){ if($m!==null){$_SESSION['flash']=$m;return;} $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function ico($n,$s=18){ $P=[
 'dash'=>'<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
 'box'=>'<path d="m12 2 8 4.5v9L12 20l-8-4.5v-9z"/><path d="m4 7 8 4.5L20 7M12 20v-8.5"/>',
 'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 'tag'=>'<path d="M3 3h8l10 10-8 8L3 11z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
 'cart'=>'<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2l2.5 13h12l2-9H6"/>',
 'star'=>'<path d="m12 3 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8L3.6 9.1l5.8-.8z"/>',
 'users'=>'<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5"/><path d="M17 8.5a3 3 0 0 1 0 5.5M22 20c0-2.5-1.5-4.2-4-5"/>',
 'logout'=>'<path d="M15 4h4v16h-4"/><path d="M10 12H3m4-4-4 4 4 4"/>','out'=>'<path d="M15 4h4v16h-4"/><path d="M10 12H3m4-4-4 4 4 4"/>',
 'search'=>'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>','plus'=>'<path d="M12 5v14M5 12h14"/>','trash'=>'<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14"/>',
 'save'=>'<path d="M5 3h12l4 4v14H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/>','img'=>'<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.8"/><path d="m4 18 5-5 4 4 3-3 4 4"/>',
 'video'=>'<rect x="3" y="5" width="14" height="14" rx="2"/><path d="m21 7-4 3 4 3z"/>','chev'=>'<path d="m9 6 6 6-6 6"/>','edit'=>'<path d="M4 20h4L18 10l-4-4L4 16z"/><path d="m13 5 4 4"/>',
 'bolt'=>'<path d="M13 2 3 14h7l-1 8 10-12h-7z"/>','eye'=>'<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
 'image'=>'<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.8"/><path d="m4 18 5-5 4 4 3-3 4 4"/>','file'=>'<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4M9 13h6M9 17h6"/>',
 'bell'=>'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
 'cog'=>'<circle cx="12" cy="12" r="3.2"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9 17 7M7 17l-2.1 2.1"/>','card'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>','key'=>'<circle cx="8" cy="15" r="4"/><path d="M10.8 12.2 20 3M18 5l2 2M15 8l2 2"/>'
 ]; return '<svg viewBox="0 0 24 24" width="'.$s.'" height="'.$s.'" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'.($P[$n]??$P['box']).'</svg>'; }
