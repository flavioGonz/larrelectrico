<?php
require(__DIR__.'/../inc/init.php');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
global $CFG;
$pm=$CFG['pay_methods']??[];
$tok=$CFG['mp_sandbox'] ? ($CFG['mp_test_access_token']?:'') : ($CFG['mp_access_token']?:'');
echo json_encode([
  'methods'    => $pm,                 // datos de cobro (no sensibles)
  'surcharge'  => (float)($CFG['mp_surcharge']??0.09),
  'discount'   => (float)($CFG['transfer_discount']??0.10),
  'mp_enabled' => $tok!=='',           // true solo si hay credencial cargada
  'wa'         => $CFG['wa_number']??'',
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
