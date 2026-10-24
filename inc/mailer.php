<?php
// Plantillas de correo profesionales (branded) para todos los mails del sistema.
if(!function_exists('mail_money')){ function mail_money($n){ return '$'.number_format((float)$n,2,',','.'); } }

function mail_shell($inner){
  return '<div style="background:#eef0f4;padding:30px 0;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif">
  <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden;border:1px solid #e3e5ea;box-shadow:0 10px 30px -14px rgba(10,27,58,.25)">
    <div style="background:#0b1b3a;padding:24px 30px;color:#fff">
      <table width="100%"><tr>
        <td style="font-size:19px;font-weight:800;letter-spacing:-.3px">⚡ Larr <span style="color:#7db5ff">Eléctrico</span></td>
        <td align="right" style="font-size:11px;color:#9fb2d4;text-transform:uppercase;letter-spacing:1px">Materiales eléctricos</td>
      </tr></table>
    </div>
    <div style="padding:32px 30px">'.$inner.'</div>
    <div style="padding:18px 30px;border-top:1px solid #eee;color:#9aa0aa;font-size:12px;line-height:1.7">
      <b style="color:#6e6e73">Larr Eléctrico</b> · Materiales eléctricos · Montevideo, Uruguay<br>
      WhatsApp <a href="https://wa.me/59894726950" style="color:#0071e3;text-decoration:none">+598 94 726 950</a> · <a href="mailto:ventas@larrelectrico.uy" style="color:#0071e3;text-decoration:none">ventas@larrelectrico.uy</a><br>
      <a href="https://www.larrelectrico.uy/final/index.html" style="color:#9aa0aa">www.larrelectrico.uy</a>
    </div>
  </div></div>';
}
function mail_send($to,$subject,$inner){
  global $CFG;
  $from=$CFG['mail_from']??'no-reply@larrelectrico.uy'; $fromName=$CFG['mail_from_name']??'Larr Eléctrico';
  $reply=$CFG['mail_reply']??$from;
  $html=mail_shell($inner);
  $h="MIME-Version: 1.0\r\n"."Content-Type: text/html; charset=UTF-8\r\n"."From: ".$fromName." <".$from.">\r\n"."Reply-To: ".$reply."\r\n";
  return @mail($to,'=?UTF-8?B?'.base64_encode($subject).'?=',$html,$h);
}
function mail_h1($t){ return '<h1 style="font-size:22px;font-weight:800;color:#1d1d1f;margin:0 0 6px;letter-spacing:-.4px">'.$t.'</h1>'; }
function mail_p($t){ return '<p style="font-size:14.5px;color:#5a5a62;line-height:1.65;margin:0 0 16px">'.$t.'</p>'; }
function mail_btn($url,$label){ return '<a href="'.$url.'" style="display:block;text-align:center;background:#0071e3;color:#fff;text-decoration:none;font-weight:600;font-size:15px;padding:15px;border-radius:12px;margin:8px 0 6px">'.$label.'</a>'; }
function mail_btn_wa($url,$label){ return '<a href="'.$url.'" style="display:block;text-align:center;background:#25d366;color:#fff;text-decoration:none;font-weight:600;font-size:15px;padding:15px;border-radius:12px;margin:8px 0 6px">'.$label.'</a>'; }
function mail_items_table($lines){
  $rows=''; foreach($lines as $it){
    $rows.='<tr><td style="padding:10px 0;border-bottom:1px solid #eef0f3;font-size:14px;color:#1d1d1f">'.htmlspecialchars($it['name']).' <span style="color:#9aa0aa">× '.(int)($it['qty']??1).'</span></td>'
      .'<td align="right" style="padding:10px 0;border-bottom:1px solid #eef0f3;font-size:14px;font-weight:700;color:#1d1d1f;white-space:nowrap">'.mail_money(($it['price']??0)*($it['qty']??1)).'</td></tr>';
  }
  return '<table style="width:100%;border-collapse:collapse;margin:4px 0 6px">'.$rows.'</table>';
}
function mail_total_row($label,$val,$strong=false,$color='#1d1d1f'){
  $fw=$strong?'800':'500'; $fs=$strong?'17px':'13.5px';
  return '<tr><td style="padding:4px 0;font-size:'.$fs.';color:'.($strong?$color:'#86868b').';font-weight:'.$fw.'">'.$label.'</td>'
    .'<td align="right" style="padding:4px 0;font-size:'.$fs.';color:'.$color.';font-weight:'.$fw.'">'.$val.'</td></tr>';
}

/* ---- correos específicos ---- */
function mail_welcome($name){
  $site='https://www.larrelectrico.uy/final';
  $in=mail_h1('¡Bienvenido/a, '.htmlspecialchars(explode(' ',trim($name))[0]).'!')
    .mail_p('Tu cuenta en <b>Larr Eléctrico</b> ya está lista. Desde tu panel podés seguir tus pedidos, guardar tus datos de envío, comparar productos y comprar más rápido.')
    .'<table style="width:100%;margin:18px 0"><tr>'
    .'<td style="padding:6px 0;font-size:13.5px;color:#5a5a62">✓ Seguimiento de tus pedidos en tiempo real</td></tr>'
    .'<tr><td style="padding:6px 0;font-size:13.5px;color:#5a5a62">✓ 10% de descuento pagando por transferencia</td></tr>'
    .'<tr><td style="padding:6px 0;font-size:13.5px;color:#5a5a62">✓ Asesoramiento técnico por WhatsApp</td></tr></table>'
    .mail_btn($site.'/catalogo.html','Ver el catálogo')
    .mail_p('<span style="font-size:12.5px;color:#9aa0aa">Si no creaste esta cuenta, ignorá este mensaje.</span>');
  return $in;
}
function mail_order($oid,$lines,$subtotal,$iva,$total,$pagar,$disc_amt,$payment,$name){
  $site='https://www.larrelectrico.uy/final';
  $PM=['transfer'=>'Transferencia / Giro bancario','giros'=>'Giros Redpagos / Abitab','cod'=>'Pago contra entrega','pickup'=>'Retira en local','mp'=>'Mercado Pago'];
  $totrows=mail_total_row('Subtotal',mail_money($subtotal))
    .mail_total_row('IVA (22%)',mail_money($iva));
  if($disc_amt>0) $totrows.=mail_total_row('Descuento transferencia (10%)','− '.mail_money($disc_amt),false,'#1a7f44');
  $totrows.=mail_total_row('Total a pagar',mail_money($pagar),true);
  $in=mail_h1('Recibimos tu pedido #'.$oid)
    .mail_p('Hola '.htmlspecialchars(explode(' ',trim($name))[0]).', ¡gracias por tu compra! Registramos tu pedido y lo estamos preparando. Te contactamos por WhatsApp para coordinar el pago y la entrega.')
    .'<div style="background:#f7f8fa;border:1px solid #eef0f3;border-radius:14px;padding:18px 20px;margin:4px 0 18px">'
    .mail_items_table($lines)
    .'<table style="width:100%;border-collapse:collapse;border-top:1px solid #e3e5ea;margin-top:8px;padding-top:8px">'.$totrows.'</table>'
    .'<div style="margin-top:12px;font-size:12.5px;color:#86868b">Medio de pago elegido: <b style="color:#1d1d1f">'.($PM[$payment]??'A coordinar').'</b></div></div>'
    .mail_btn_wa('https://wa.me/59894726950?text='.rawurlencode('Hola! Consulto por mi pedido #'.$oid),'Coordinar por WhatsApp')
    .mail_btn($site.'/cuenta.html','Ver mis pedidos');
  return $in;
}
function mail_password_changed($name){
  return mail_h1('Tu contraseña fue cambiada')
    .mail_p('Hola '.htmlspecialchars(explode(' ',trim($name))[0]).', te confirmamos que la contraseña de tu cuenta en Larr Eléctrico se actualizó correctamente.')
    .mail_p('<span style="font-size:12.5px;color:#9aa0aa">Si no fuiste vos, escribinos cuanto antes a ventas@larrelectrico.uy o por WhatsApp.</span>');
}
function mail_cart($name,$lines,$total,$link){
  $in=mail_h1('¿Te quedó algo en el carrito?')
    .mail_p('Hola '.htmlspecialchars(explode(' ',trim($name))[0]).', guardamos tu carrito para que puedas terminar la compra cuando quieras.')
    .'<div style="background:#f7f8fa;border:1px solid #eef0f3;border-radius:14px;padding:18px 20px;margin:4px 0 18px">'
    .mail_items_table($lines)
    .'<table style="width:100%;border-top:1px solid #e3e5ea;margin-top:8px">'.mail_total_row('Total (IVA incl.)',mail_money($total),true).'</table></div>'
    .mail_btn($link,'Seguí tu compra →')
    .mail_p('<span style="font-size:12.5px;color:#9aa0aa">Pagando por transferencia tenés 10% de descuento. Si ya compraste, ignorá este mensaje.</span>');
  return $in;
}
