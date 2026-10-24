<?php require(__DIR__.'/inc.php'); require_once(__DIR__.'/../inc/webauthn.php');
header('Content-Type: application/json');
if(!admin()){ http_response_code(401); echo json_encode(['error'=>'auth']); exit; }
$u=admin(); $act=$_GET['act']??($_POST['act']??'');

if($act==='options'){
  $chal=wa_b64url_enc(random_bytes(32)); $_SESSION['wa_reg_chal']=$chal;
  $exclude=[];
  try{ $st=db()->prepare("SELECT cred_id FROM webauthn_creds WHERE user_id=?"); $st->execute([$u['id']]);
       foreach($st->fetchAll() as $r){ $exclude[]=['type'=>'public-key','id'=>$r['cred_id']]; } }catch(Exception $e){}
  echo json_encode([
    'challenge'=>$chal,
    'rp'=>['id'=>wa_rp_id(),'name'=>'Larr Eléctrico Admin'],
    'user'=>['id'=>wa_b64url_enc('u'.$u['id']),'name'=>$u['email'],'displayName'=>$u['name']],
    'pubKeyCredParams'=>[['type'=>'public-key','alg'=>-7]],
    'authenticatorSelection'=>['residentKey'=>'preferred','userVerification'=>'preferred'],
    'timeout'=>60000,'attestation'=>'none','excludeCredentials'=>$exclude
  ]);
  exit;
}

if($act==='verify'){
  $in=json_decode(file_get_contents('php://input'),true) ?: [];
  $resp=$in['response']??[]; $chal=$_SESSION['wa_reg_chal']??''; unset($_SESSION['wa_reg_chal']);
  if(!$chal){ http_response_code(400); echo json_encode(['error'=>'nochal']); exit; }
  $res=wa_register_verify($resp['attestationObject']??'', $resp['clientDataJSON']??'', $chal);
  if(!$res){ http_response_code(400); echo json_encode(['error'=>'verify']); exit; }
  list($credId,$pem,$sc)=$res;
  $label=substr(trim($in['label']??'')?:'Este dispositivo',0,60);
  try{
    db()->prepare("INSERT INTO webauthn_creds(user_id,cred_id,public_key,sign_count,label) VALUES(?,?,?,?,?)
      ON DUPLICATE KEY UPDATE public_key=VALUES(public_key),sign_count=VALUES(sign_count),label=VALUES(label)")
      ->execute([$u['id'],$credId,$pem,(int)$sc,$label]);
    echo json_encode(['ok'=>true]);
  }catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>'db']); }
  exit;
}

if($act==='list'){
  $out=[]; try{ $st=db()->prepare("SELECT id,label,created_at FROM webauthn_creds WHERE user_id=? ORDER BY id DESC"); $st->execute([$u['id']]); $out=$st->fetchAll(); }catch(Exception $e){}
  echo json_encode(['creds'=>$out]); exit;
}

if($act==='delete'){
  check_csrf(); $id=(int)($_POST['id']??0);
  try{ db()->prepare("DELETE FROM webauthn_creds WHERE id=? AND user_id=?")->execute([$id,$u['id']]); echo json_encode(['ok'=>true]); }
  catch(Exception $e){ http_response_code(500); echo json_encode(['error'=>'db']); }
  exit;
}
http_response_code(400); echo json_encode(['error'=>'noact']);
