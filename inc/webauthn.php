<?php
/* WebAuthn (passkey / Face ID) — verificación fail-closed, sin dependencias.
   rpId = dominio registrable; origin = esquema+host del sitio. */

function wa_rp_id(){ return 'larrelectrico.uy'; }
function wa_origin(){
  $host=$_SERVER['HTTP_HOST'] ?? 'www.larrelectrico.uy';
  return 'https://'.$host;
}
function wa_b64url_enc($b){ return rtrim(strtr(base64_encode($b),'+/','-_'),'='); }
function wa_b64url_dec($s){ $s=strtr($s,'-_','+/'); $p=strlen($s)%4; if($p)$s.=str_repeat('=',4-$p); return base64_decode($s); }

/* ---- decoder CBOR mínimo (uint, negint, bytes, text, array, map) ---- */
function wa_cbor($d,&$o){
  if($o>=strlen($d)) throw new Exception('cbor eof');
  $ib=ord($d[$o++]); $mt=$ib>>5; $ai=$ib&0x1f;
  $val=$ai;
  if($ai==24){ $val=ord($d[$o++]); }
  elseif($ai==25){ $val=(ord($d[$o])<<8)|ord($d[$o+1]); $o+=2; }
  elseif($ai==26){ $val=(ord($d[$o])<<24)|(ord($d[$o+1])<<16)|(ord($d[$o+2])<<8)|ord($d[$o+3]); $o+=4; }
  elseif($ai==27){ $hi=(ord($d[$o])<<24)|(ord($d[$o+1])<<16)|(ord($d[$o+2])<<8)|ord($d[$o+3]); $lo=(ord($d[$o+4])<<24)|(ord($d[$o+5])<<16)|(ord($d[$o+6])<<8)|ord($d[$o+7]); $val=$hi*4294967296+$lo; $o+=8; }
  elseif($ai>=28&&$ai<=30){ throw new Exception('cbor reserved'); }
  switch($mt){
    case 0: return $val;                       // uint
    case 1: return -1-$val;                     // negint
    case 2: $s=substr($d,$o,$val); $o+=$val; return $s;   // bytes
    case 3: $s=substr($d,$o,$val); $o+=$val; return $s;   // text
    case 4: $a=[]; for($i=0;$i<$val;$i++)$a[]=wa_cbor($d,$o); return $a;  // array
    case 5: $m=[]; for($i=0;$i<$val;$i++){ $k=wa_cbor($d,$o); $m[is_int($k)?$k:(string)$k]=wa_cbor($d,$o); } return $m; // map
    case 7: return $val;
  }
  throw new Exception('cbor type');
}

/* COSE EC2 (x,y) -> PEM clave pública P-256 */
function wa_cose_to_pem($cose){
  if(!is_array($cose)) return null;
  $kty=$cose[1]??null; $alg=$cose[3]??null; $crv=$cose[-1]??null;
  $x=$cose[-2]??null; $y=$cose[-3]??null;
  if($kty!=2 || (int)$alg!==-7 || $crv!=1) return null;      // solo EC2 P-256 ES256
  if(!is_string($x)||!is_string($y)||strlen($x)!=32||strlen($y)!=32) return null;
  $der = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00\x04".$x.$y;
  $pem="-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($der),64,"\n")."-----END PUBLIC KEY-----\n";
  return $pem;
}

/* Parse authenticatorData -> rpIdHash, flags, signCount, (credId, cosePub) si AT */
function wa_parse_authdata($ad){
  if(strlen($ad)<37) return null;
  $r=['rpIdHash'=>substr($ad,0,32),'flags'=>ord($ad[32]),
      'signCount'=>(ord($ad[33])<<24)|(ord($ad[34])<<16)|(ord($ad[35])<<8)|ord($ad[36])];
  $off=37;
  if($r['flags'] & 0x40){ // AT: attested credential data
    if(strlen($ad)<$off+18) return null;
    $off+=16; // aaguid
    $cidLen=(ord($ad[$off])<<8)|ord($ad[$off+1]); $off+=2;
    if(strlen($ad)<$off+$cidLen) return null;
    $r['credId']=substr($ad,$off,$cidLen); $off+=$cidLen;
    try{ $r['cose']=wa_cbor($ad,$off); }catch(Exception $e){ return null; }
  }
  return $r;
}

/* ---- REGISTRO: valida attestation y devuelve [credId_b64url, pubPem] o null ---- */
function wa_register_verify($attestationObject_b64, $clientDataJSON_b64, $challenge_b64){
  $att=wa_b64url_dec($attestationObject_b64); $cdj=wa_b64url_dec($clientDataJSON_b64);
  if(!$att||!$cdj) return null;
  $cd=json_decode($cdj,true); if(!is_array($cd)) return null;
  if(($cd['type']??'')!=='webauthn.create') return null;
  if(!hash_equals($challenge_b64,(string)($cd['challenge']??''))) return null;
  if(($cd['origin']??'')!==wa_origin()) return null;
  $o=0; try{ $ao=wa_cbor($att,$o); }catch(Exception $e){ return null; }
  if(!isset($ao['authData']) || !is_string($ao['authData'])) return null;
  $p=wa_parse_authdata($ao['authData']); if(!$p) return null;
  if(!hash_equals(hash('sha256',wa_rp_id(),true),$p['rpIdHash'])) return null;
  if(!($p['flags'] & 0x01)) return null; // user present
  if(empty($p['credId']) || empty($p['cose'])) return null;
  $pem=wa_cose_to_pem($p['cose']); if(!$pem) return null;
  return [wa_b64url_enc($p['credId']), $pem, $p['signCount']];
}

/* ---- LOGIN: valida assertion contra la clave guardada. true/false ---- */
function wa_login_verify($authData_b64,$clientDataJSON_b64,$signature_b64,$challenge_b64,$pubPem){
  $ad=wa_b64url_dec($authData_b64); $cdj=wa_b64url_dec($clientDataJSON_b64); $sig=wa_b64url_dec($signature_b64);
  if(!$ad||!$cdj||!$sig||!$pubPem) return false;
  $cd=json_decode($cdj,true); if(!is_array($cd)) return false;
  if(($cd['type']??'')!=='webauthn.get') return false;
  if(!hash_equals($challenge_b64,(string)($cd['challenge']??''))) return false;
  if(($cd['origin']??'')!==wa_origin()) return false;
  $p=wa_parse_authdata($ad); if(!$p) return false;
  if(!hash_equals(hash('sha256',wa_rp_id(),true),$p['rpIdHash'])) return false;
  if(!($p['flags'] & 0x01)) return false; // user present
  $signed=$ad.hash('sha256',$cdj,true);
  $ok=openssl_verify($signed,$sig,$pubPem,OPENSSL_ALGO_SHA256);
  return $ok===1;
}
