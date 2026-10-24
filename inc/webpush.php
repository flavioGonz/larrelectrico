<?php
/* Web Push con VAPID (payload-less). Compatible con admin/ y api/ (ambos definen db()). */

function wp_b64url($b){ return rtrim(strtr(base64_encode($b),'+/','-_'),'='); }

function wp_setting_get($k){
  try{ $st=db()->prepare("SELECT v FROM settings WHERE k=?"); $st->execute([$k]); $r=$st->fetch(); return $r?$r['v']:null; }catch(Exception $e){ return null; }
}
function wp_setting_set($k,$v){
  try{ db()->prepare("INSERT INTO settings(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$k,$v]); }catch(Exception $e){}
}
function wp_ensure_table(){
  try{ db()->exec("CREATE TABLE IF NOT EXISTS push_subs(id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, endpoint VARCHAR(512) NOT NULL, p256dh VARCHAR(255) NOT NULL, auth VARCHAR(255) NOT NULL, token VARCHAR(64) NULL, ua VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_ep(endpoint)) DEFAULT CHARSET=utf8mb4"); }catch(Exception $e){}
  try{ db()->exec("ALTER TABLE push_subs ADD COLUMN token VARCHAR(64) NULL"); }catch(Exception $e){}
}

/* Par de llaves VAPID (se genera una vez y se guarda en settings) */
function webpush_vapid(){
  $pem=wp_setting_get('vapid_pem'); $pub=wp_setting_get('vapid_pub');
  if($pem && $pub) return ['pem'=>$pem,'pub'=>$pub];
  if(!function_exists('openssl_pkey_new')) return null;
  $res=openssl_pkey_new(['curve_name'=>'prime256v1','private_key_type'=>OPENSSL_KEYTYPE_EC]);
  if(!$res) return null;
  openssl_pkey_export($res,$pem);
  $d=openssl_pkey_get_details($res);
  if(empty($d['ec']['x'])||empty($d['ec']['y'])) return null;
  $x=str_pad($d['ec']['x'],32,"\x00",STR_PAD_LEFT);
  $y=str_pad($d['ec']['y'],32,"\x00",STR_PAD_LEFT);
  $pub=wp_b64url("\x04".$x.$y);
  wp_setting_set('vapid_pem',$pem); wp_setting_set('vapid_pub',$pub);
  return ['pem'=>$pem,'pub'=>$pub];
}
function webpush_public_key(){ $v=webpush_vapid(); return $v?$v['pub']:null; }

/* DER (SEQUENCE{INTEGER r, INTEGER s}) -> raw R||S de 64 bytes */
function wp_der_to_raw($der){
  $off=0; $len=strlen($der);
  $rd=function() use (&$off,$der){ $l=ord($der[$off++]); if($l<0x80) return $l; $n=$l&0x7f; $v=0; for($i=0;$i<$n;$i++){$v=($v<<8)|ord($der[$off++]);} return $v; };
  if($off>=$len || ord($der[$off++])!=0x30) return false; $rd();
  if($off>=$len || ord($der[$off++])!=0x02) return false; $rl=$rd(); $r=substr($der,$off,$rl); $off+=$rl;
  if($off>=$len || ord($der[$off++])!=0x02) return false; $sl=$rd(); $s=substr($der,$off,$sl); $off+=$sl;
  $r=ltrim($r,"\x00"); $s=ltrim($s,"\x00");
  if(strlen($r)>32||strlen($s)>32) return false;
  return str_pad($r,32,"\x00",STR_PAD_LEFT).str_pad($s,32,"\x00",STR_PAD_LEFT);
}

/* JWT firmado ES256 para el audience del endpoint */
function webpush_jwt($aud,$vapid){
  global $CFG;
  $sub='mailto:'.($CFG['mail_reply']??'admin@larrelectrico.uy');
  $h=wp_b64url(json_encode(['typ'=>'JWT','alg'=>'ES256']));
  $p=wp_b64url(json_encode(['aud'=>$aud,'exp'=>time()+43200,'sub'=>$sub]));
  $input=$h.'.'.$p; $der='';
  if(!openssl_sign($input,$der,$vapid['pem'],OPENSSL_ALGO_SHA256)) return null;
  $raw=wp_der_to_raw($der); if($raw===false) return null;
  return $input.'.'.wp_b64url($raw);
}

/* Envía un push vacío a una suscripción. Devuelve el código HTTP. */
function webpush_send_one($sub,$vapid){
  $ep=$sub['endpoint']; $u=parse_url($ep); if(empty($u['host'])) return 0;
  $aud=$u['scheme'].'://'.$u['host'];
  $jwt=webpush_jwt($aud,$vapid); if(!$jwt) return 0;
  $headers=['Authorization: vapid t='.$jwt.', k='.$vapid['pub'],'TTL: 86400','Content-Length: 0'];
  $ch=curl_init($ep);
  curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>'',CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>true]);
  curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  return (int)$code;
}

/* Notifica a todos los admins suscriptos (se llama al crear un pedido) */
function webpush_notify_admins(){
  try{ $pdo=db(); }catch(Exception $e){ return; }
  $vapid=webpush_vapid(); if(!$vapid) return;
  try{ $subs=$pdo->query("SELECT endpoint,p256dh,auth FROM push_subs")->fetchAll(); }catch(Exception $e){ return; }
  foreach($subs as $s){
    $code=webpush_send_one($s,$vapid);
    if($code===404||$code===410){ try{ $pdo->prepare("DELETE FROM push_subs WHERE endpoint=?")->execute([$s['endpoint']]); }catch(Exception $e){} }
  }
}
