<?php require(__DIR__.'/inc.php'); require_once(__DIR__.'/../inc/webauthn.php');
header('Content-Type: application/json');
$act=$_GET['act']??($_POST['act']??'');

if($act==='options'){
  $chal=wa_b64url_enc(random_bytes(32)); $_SESSION['wa_login_chal']=$chal;
  echo json_encode([
    'challenge'=>$chal,'rpId'=>wa_rp_id(),'timeout'=>60000,
    'userVerification'=>'preferred','allowCredentials'=>[]   // discoverable (resident keys)
  ]);
  exit;
}

if($act==='verify'){
  $in=json_decode(file_get_contents('php://input'),true) ?: [];
  $chal=$_SESSION['wa_login_chal']??''; unset($_SESSION['wa_login_chal']);
  if(!$chal){ http_response_code(400); echo json_encode(['error'=>'nochal']); exit; }
  $credId=$in['id']??''; $resp=$in['response']??[];
  if(!$credId){ http_response_code(400); echo json_encode(['error'=>'nocred']); exit; }
  try{
    $st=db()->prepare("SELECT c.id,c.public_key,c.sign_count,c.user_id,u.name,u.email,u.role
                       FROM webauthn_creds c JOIN users u ON u.id=c.user_id
                       WHERE c.cred_id=? LIMIT 1");
    $st->execute([$credId]); $row=$st->fetch();
  }catch(Exception $e){ $row=null; }
  if(!$row || $row['role']!=='admin'){ http_response_code(403); echo json_encode(['error'=>'nocred']); exit; }
  $ok=wa_login_verify($resp['authenticatorData']??'', $resp['clientDataJSON']??'', $resp['signature']??'', $chal, $row['public_key']);
  if(!$ok){ http_response_code(403); echo json_encode(['error'=>'verify']); exit; }
  // éxito: iniciar sesión admin
  $_SESSION['admin']=['id'=>$row['user_id'],'name'=>$row['name'],'email'=>$row['email']];
  try{ db()->prepare("UPDATE webauthn_creds SET sign_count=sign_count WHERE id=?")->execute([$row['id']]); }catch(Exception $e){}
  session_regenerate_id(true);
  echo json_encode(['ok'=>true,'redirect'=>'index.php']);
  exit;
}
http_response_code(400); echo json_encode(['error'=>'noact']);
