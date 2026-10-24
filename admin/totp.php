<?php
/* TOTP (RFC 6238) autocontenido — compatible con Google Authenticator / Authy */
function b32_decode($s){
  $s=preg_replace('/[^A-Z2-7]/','',strtoupper($s));
  $map='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  $bits=''; $out='';
  for($i=0;$i<strlen($s);$i++){ $bits.=str_pad(decbin(strpos($map,$s[$i])),5,'0',STR_PAD_LEFT); }
  for($i=0;$i+8<=strlen($bits);$i+=8){ $out.=chr(bindec(substr($bits,$i,8))); }
  return $out;
}
function b32_encode($bin){
  $map='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  $bits=''; for($i=0;$i<strlen($bin);$i++){ $bits.=str_pad(decbin(ord($bin[$i])),8,'0',STR_PAD_LEFT); }
  $out=''; for($i=0;$i<strlen($bits);$i+=5){ $chunk=substr($bits,$i,5); $chunk=str_pad($chunk,5,'0',STR_PAD_RIGHT); $out.=$map[bindec($chunk)]; }
  return $out;
}
function totp_secret($bytes=20){ return b32_encode(random_bytes($bytes)); }
function totp_at($secret,$counter,$digits=6){
  $key=b32_decode($secret);
  $bin=pack('N*',0).pack('N*',$counter); // 8 bytes big-endian
  $hash=hash_hmac('sha1',$bin,$key,true);
  $off=ord($hash[19]) & 0x0f;
  $part=((ord($hash[$off])&0x7f)<<24)|((ord($hash[$off+1])&0xff)<<16)|((ord($hash[$off+2])&0xff)<<8)|(ord($hash[$off+3])&0xff);
  return str_pad((string)($part % (10**$digits)),$digits,'0',STR_PAD_LEFT);
}
function totp_now($secret,$period=30,$digits=6){ return totp_at($secret,(int)floor(time()/$period),$digits); }
function totp_verify($secret,$code,$window=1,$period=30,$digits=6){
  $code=preg_replace('/\D/','',(string)$code);
  if(strlen($code)!==$digits) return false;
  $c=(int)floor(time()/$period);
  for($i=-$window;$i<=$window;$i++){ if(hash_equals(totp_at($secret,$c+$i,$digits),$code)) return true; }
  return false;
}
function totp_uri($secret,$label,$issuer){
  return 'otpauth://totp/'.rawurlencode($issuer.':'.$label).'?secret='.$secret.'&issuer='.rawurlencode($issuer).'&algorithm=SHA1&digits=6&period=30';
}
