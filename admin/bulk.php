<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
require_once(__DIR__.'/../inc/audit.php'); audit_migrate($pdo);
$COLS=[['id','ID',0],['sku','Código / SKU',0],['name','Nombre',0],['brand','Marca',0],['price','Precio (sin IVA)',1],['stock','Stock',1],['featured','Destacado',1]];

/* ---------- motor de precios ---------- */
function bulk_scope($pdo,$type,$val){
  $from="FROM products p"; $where="p.price IS NOT NULL"; $params=[]; $label='todos los productos';
  if($type==='cat'){ $from.=" JOIN product_categories pc ON pc.product_id=p.id"; $where.=" AND pc.category_id=?"; $params[]=(int)$val;
    $r=$pdo->query("SELECT name FROM categories WHERE id=".(int)$val)->fetch(); $label='categoría '.($r['name']??('#'.$val)); }
  elseif($type==='brand'){ $where.=" AND p.brand=?"; $params[]=(string)$val; $label='marca '.$val; }
  elseif($type==='currency'){ $cc=strtoupper($val)==='USD'?'USD':'UYU'; $where.=" AND COALESCE(p.currency,'UYU')=?"; $params[]=$cc; $label='moneda '.$cc; }
  return [$from,$where,$params,$label];
}
function bulk_expr($op,$v,$round){
  $v=(float)$v;
  switch($op){
    case 'incpct': $e="p.price*(1+($v)/100)"; break;
    case 'decpct': $e="p.price*(1-($v)/100)"; break;
    case 'add':    $e="p.price+($v)"; break;
    case 'sub':    $e="p.price-($v)"; break;
    case 'factor': $e="p.price*($v)"; break;
    case 'set':    $e="($v)"; break;
    default:       $e="p.price";
  }
  $e="GREATEST(0,$e)";
  switch($round){
    case 'int':  $e="ROUND($e,0)"; break;
    case 'd90':  $e="FLOOR($e)+0.90"; break;
    case 'd99':  $e="FLOOR($e)+0.99"; break;
    case 'tens': $e="ROUND($e/10)*10"; break;
    default:     $e="ROUND($e,2)";
  }
  return $e;
}
function bulk_op_label($op,$v){ $v=(float)$v; $nf=rtrim(rtrim(number_format($v,2,',','.'),'0'),',');
  switch($op){ case 'incpct':return "Aumentar $nf%"; case 'decpct':return "Bajar $nf%"; case 'add':return "Sumar \$$nf"; case 'sub':return "Restar \$$nf"; case 'factor':return "Multiplicar ×$nf"; case 'set':return "Fijar en \$$nf"; } return 'Ajuste'; }
function bulk_round_label($r){ $m=['none'=>'','int'=>' · entero','d90'=>' · termina en ,90','d99'=>' · termina en ,99','tens'=>' · a la decena']; return $m[$r]??''; }

/* ---------- XLSX helpers ---------- */
function xq($s){ return htmlspecialchars((string)$s,ENT_QUOTES|ENT_XML1,'UTF-8'); }
function colLetter($i){ $s=''; $i++; while($i>0){ $m=($i-1)%26; $s=chr(65+$m).$s; $i=intdiv($i-1,26);} return $s; }
function build_xlsx($headers,$rows,$numCols){
  // sheet
  $sheet='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
   .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
   .'<cols><col min="1" max="1" width="8"/><col min="2" max="2" width="22"/><col min="3" max="3" width="46"/><col min="4" max="4" width="16"/><col min="5" max="5" width="16"/><col min="6" max="6" width="10"/><col min="7" max="7" width="12"/></cols><sheetData>';
  // header row
  $sheet.='<row r="1" ht="22" customHeight="1">';
  foreach($headers as $i=>$h){ $sheet.='<c r="'.colLetter($i).'1" t="inlineStr" s="1"><is><t>'.xq($h).'</t></is></c>'; }
  $sheet.='</row>';
  $r=2;
  foreach($rows as $row){ $sheet.='<row r="'.$r.'">';
    foreach($row as $i=>$v){ $ref=colLetter($i).$r;
      if(in_array($i,$numCols)){ $st=($i===4)?3:2; $sheet.='<c r="'.$ref.'" s="'.$st.'"><v>'.($v===''||$v===null?0:$v).'</v></c>'; }
      else{ $sheet.='<c r="'.$ref.'" t="inlineStr" s="2"><is><t xml:space="preserve">'.xq($v).'</t></is></c>'; }
    }
    $sheet.='</row>'; $r++;
  }
  $sheet.='</sheetData><autoFilter ref="A1:'.colLetter(count($headers)-1).($r-1).'"/></worksheet>';
  $styles='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
   .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
   .'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
   .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
   .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0B1B3A"/></patternFill></fill></fills>'
   .'<borders count="2"><border><left/><right/><top/><bottom/></border><border><left style="thin"><color rgb="FFE3E3E6"/></left><right style="thin"><color rgb="FFE3E3E6"/></right><top style="thin"><color rgb="FFE3E3E6"/></top><bottom style="thin"><color rgb="FFE3E3E6"/></bottom></border></borders>'
   .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
   .'<cellXfs count="4">'
   .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
   .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
   .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1"/>'
   .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyBorder="1" applyNumberFormat="1"/>'
   .'</cellXfs></styleSheet>';
  $wb='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Productos" sheetId="1" r:id="rId1"/></sheets></workbook>';
  $wbRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
  $rels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
  $ct='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
  $tmp=tempnam(sys_get_temp_dir(),'xlsx'); $z=new ZipArchive(); $z->open($tmp,ZipArchive::OVERWRITE);
  $z->addFromString('[Content_Types].xml',$ct); $z->addFromString('_rels/.rels',$rels);
  $z->addFromString('xl/workbook.xml',$wb); $z->addFromString('xl/_rels/workbook.xml.rels',$wbRels);
  $z->addFromString('xl/styles.xml',$styles); $z->addFromString('xl/worksheets/sheet1.xml',$sheet);
  $z->close(); $data=file_get_contents($tmp); @unlink($tmp); return $data;
}
function read_xlsx($file){
  $z=new ZipArchive(); if($z->open($file)!==true) return null;
  $shared=[]; $ss=$z->getFromName('xl/sharedStrings.xml');
  if($ss){ $x=simplexml_load_string($ss); foreach($x->si as $si){ $t=''; if(isset($si->t))$t=(string)$si->t; else foreach($si->r as $r)$t.=(string)$r->t; $shared[]=$t; } }
  $sheetName='xl/worksheets/sheet1.xml'; $sx=$z->getFromName($sheetName);
  if(!$sx){ for($i=1;$i<9;$i++){ $sx=$z->getFromName("xl/worksheets/sheet$i.xml"); if($sx)break; } }
  $z->close(); if(!$sx)return null;
  $xml=simplexml_load_string($sx); $rows=[];
  foreach($xml->sheetData->row as $row){ $cells=[];
    foreach($row->c as $c){ $ref=(string)$c['r']; preg_match('/^([A-Z]+)/',$ref,$m); $col=0; foreach(str_split($m[1]) as $ch){ $col=$col*26+(ord($ch)-64);} $col--;
      $t=(string)$c['t']; $v='';
      if($t==='inlineStr'){ $v=(string)$c->is->t; }
      elseif($t==='s'){ $v=$shared[(int)$c->v]??''; }
      else{ $v=(string)$c->v; }
      $cells[$col]=$v;
    }
    $rows[]=$cells;
  }
  return $rows;
}

/* ---------- actions ---------- */
if(($_GET['export']??'')==='xlsx'){
  $rows=[]; foreach($pdo->query("SELECT id,sku,name,brand,price,stock,featured FROM products ORDER BY id") as $r){ $rows[]=[(int)$r['id'],$r['sku'],$r['name'],$r['brand'],$r['price'],(int)$r['stock'],(int)$r['featured']]; }
  $xlsx=build_xlsx(array_column($COLS,1),$rows,[0,4,5,6]);
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="productos_larrelectrico.xlsx"');
  header('Content-Length: '.strlen($xlsx)); echo $xlsx; exit;
}

/* ---- motor de precios: vista previa / aplicar (AJAX) ---- */
if(isset($_GET['bulkajax'])){
  header('Content-Type: application/json; charset=utf-8');
  if(($_POST['csrf']??'')!==($_SESSION['csrf']??'x')){ echo json_encode(['error'=>'csrf']); exit; }
  $mode=$_GET['bulkajax'];
  $type=$_POST['scope_type']??'all'; $sval=$_POST['scope_val']??'';
  $op=$_POST['op']??'incpct'; $v=$_POST['val']??''; $round=$_POST['round']??'none';
  if($v===''||!is_numeric($v)){ echo json_encode(['error'=>'val']); exit; }
  [$from,$where,$params,$slabel]=bulk_scope($pdo,$type,$sval);
  $expr=bulk_expr($op,$v,$round);
  $label=bulk_op_label($op,$v).bulk_round_label($round).' · '.$slabel;
  $cst=$pdo->prepare("SELECT COUNT(*) c $from WHERE $where"); $cst->execute($params); $count=(int)$cst->fetch()['c'];
  if($mode==='apply'){
    if($count>0){
      $now=audit_now(); $a=admin()?:[];
      $upd="UPDATE products p".($type==='cat'?" JOIN product_categories pc ON pc.product_id=p.id":"")." SET p.price=$expr, p.updated_at=? WHERE $where";
      $pdo->prepare($upd)->execute(array_merge([$now],$params));
      $chg=json_encode([['f'=>'price','label'=>'Precio','old'=>'—','new'=>bulk_op_label($op,$v).bulk_round_label($round)]],JSON_UNESCAPED_UNICODE);
      try{ $pdo->prepare("INSERT INTO product_audit(product_id,action,changes,admin_id,admin_name,note,created_at) SELECT p.id,'masivo',?,?,?,?,? $from WHERE $where")
            ->execute(array_merge([$chg,(int)($a['id']??0),(string)($a['name']??'Sistema'),'Ajuste masivo: '.$label,$now],$params)); }catch(\Throwable $e){}
    }
    echo json_encode(['ok'=>true,'count'=>$count,'label'=>$label]); exit;
  }
  // preview: muestra de hasta 8
  $smp=$pdo->prepare("SELECT p.name, p.price AS oldp, $expr AS newp, COALESCE(p.currency,'UYU') AS cur $from WHERE $where ORDER BY p.id DESC LIMIT 8");
  $smp->execute($params);
  echo json_encode(['count'=>$count,'label'=>$label,'sample'=>$smp->fetchAll(PDO::FETCH_ASSOC)]); exit;
}

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $act=$_POST['act']??'';
  if($act==='saveall'){
    $ids=$_POST['id']??[]; $n=0; $now=audit_now();
    $old=[]; if($ids){ $in=implode(',',array_map('intval',$ids)); foreach($pdo->query("SELECT id,price,stock,featured FROM products WHERE id IN ($in)") as $o){ $old[$o['id']]=$o; } }
    $st=$pdo->prepare("UPDATE products SET price=?, stock=?, featured=?, updated_at=? WHERE id=?");
    foreach($ids as $i=>$id){ $id=(int)$id; $pr=($_POST['price'][$i]===''?null:(float)$_POST['price'][$i]); $stk=(int)$_POST['stock'][$i]; $ft=isset($_POST['featured'][$id])?1:0;
      $st->execute([$pr,$stk,$ft,$now,$id]); $n++;
      $o=$old[$id]??null; if($o){ $ch=[];
        if(abs((float)$o['price']-(float)$pr)>0.0001) $ch[]=['f'=>'price','label'=>'Precio','old'=>(float)$o['price'],'new'=>$pr];
        if((int)$o['stock']!==$stk) $ch[]=['f'=>'stock','label'=>'Stock','old'=>(int)$o['stock'],'new'=>$stk];
        if((int)$o['featured']!==$ft) $ch[]=['f'=>'featured','label'=>'Destacado','old'=>$o['featured']?'Sí':'No','new'=>$ft?'Sí':'No'];
        if($ch) audit_log($pdo,$id,'editar',$ch,'Edición en grilla');
      }
    }
    flash("Se guardaron $n productos."); header('Location: bulk.php?'.$_POST['ret']); exit;
  }
  if($act==='import' && !empty($_FILES['file']['tmp_name'])){
    $name=strtolower($_FILES['file']['name']); $n=0; $now=audit_now();
    $st=$pdo->prepare("UPDATE products SET price=?, stock=?, featured=?, updated_at=? WHERE id=?");
    if(substr($name,-5)==='.xlsx'){
      $rows=read_xlsx($_FILES['file']['tmp_name']); if($rows){ $head=array_map(fn($s)=>strtolower(trim($s)),$rows[0]); 
        $ci=['id'=>array_search('id',$head),'price'=>null,'stock'=>null,'feat'=>null];
        foreach($head as $i=>$h){ if(strpos($h,'precio')!==false)$ci['price']=$i; if(strpos($h,'stock')!==false)$ci['stock']=$i; if(strpos($h,'destac')!==false)$ci['feat']=$i; if($h==='id')$ci['id']=$i; }
        for($i=1;$i<count($rows);$i++){ $r=$rows[$i]; if(!isset($r[$ci['id']])||!is_numeric($r[$ci['id']]))continue; $id=(int)$r[$ci['id']];
          $pr=$ci['price']!==null&&isset($r[$ci['price']])&&$r[$ci['price']]!==''?(float)str_replace(',','.',$r[$ci['price']]):null;
          $stk=$ci['stock']!==null&&isset($r[$ci['stock']])?(int)$r[$ci['stock']]:0;
          $ft=$ci['feat']!==null&&isset($r[$ci['feat']])?(int)!!$r[$ci['feat']]:0;
          $st->execute([$pr,$stk,$ft,$now,$id]); $n++; }
      }
    } else {
      $f=fopen($_FILES['file']['tmp_name'],'r'); $head=fgetcsv($f); $col=array_flip(array_map('trim',(array)$head)); $stc=$pdo->prepare("UPDATE products SET price=?, stock=?, updated_at=? WHERE id=?");
      while($row=fgetcsv($f)){ if(!isset($row[$col['id']]))continue; $id=(int)$row[$col['id']]; $pr=isset($col['price'])&&$row[$col['price']]!==''?(float)$row[$col['price']]:null; $stk=isset($col['stock'])?(int)$row[$col['stock']]:0; $stc->execute([$pr,$stk,$now,$id]); $n++; }
      fclose($f);
    }
    flash("Importados/actualizados $n productos."); header('Location: bulk.php'); exit;
  }
}
$cats=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$brands=array_column($pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand<>'' ORDER BY brand")->fetchAll(),'brand');
$fcat=(int)($_GET['cat']??0); $q=trim($_GET['q']??'');
$rows=[]; $ret=http_build_query(['cat'=>$fcat?:null,'q'=>$q?:null]);
if($fcat||$q){
  if($fcat){ $st=$pdo->prepare("SELECT p.id,p.sku,p.name,p.brand,p.price,p.stock,p.featured FROM products p JOIN product_categories pc ON pc.product_id=p.id WHERE pc.category_id=? ORDER BY p.name LIMIT 300"); $st->execute([$fcat]); }
  else{ $st=$pdo->prepare("SELECT id,sku,name,brand,price,stock,featured FROM products WHERE name LIKE ? OR sku LIKE ? OR brand LIKE ? ORDER BY name LIMIT 300"); $st->execute(["%$q%","%$q%","%$q%"]); }
  $rows=$st->fetchAll();
}
ah('Edición masiva');
?>
<input type="hidden" id="blkCsrf" value="<?=csrf()?>">
<div class="panel blkeng">
 <div class="ph"><h2><?=ico('bolt',16)?> Motor de precios</h2><span class="be-sub">Filtrá, elegí la operación y el redondeo. Previsualizá antes de aplicar.</span></div>
 <div class="be-body">
  <div class="be-step">
   <div class="be-lbl"><span class="be-n">1</span>Alcance</div>
   <div class="seg" id="segScope">
    <button type="button" data-v="all" class="on"><?=ico('box',15)?>Todos</button>
    <button type="button" data-v="cat"><?=ico('grid',15)?>Categoría</button>
    <button type="button" data-v="brand"><?=ico('tag',15)?>Marca</button>
    <button type="button" data-v="currency"><?=ico('card',15)?>Moneda</button>
   </div>
   <select class="inp sel2 be-sc" id="scopeCat" style="display:none"><?php foreach($cats as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select>
   <select class="inp sel2 be-sc" id="scopeBrand" style="display:none"><?php foreach($brands as $b):?><option value="<?=e($b)?>"><?=e($b)?></option><?php endforeach;?></select>
   <select class="inp sel2 be-sc" id="scopeCur" style="display:none"><option value="UYU">Pesos (UYU)</option><option value="USD">Dólares (USD)</option></select>
  </div>
  <div class="be-step">
   <div class="be-lbl"><span class="be-n">2</span>Operación</div>
   <div class="seg wrap" id="segOp">
    <button type="button" data-v="incpct" data-pfx="%" class="on">Aumentar&nbsp;%</button>
    <button type="button" data-v="decpct" data-pfx="%">Bajar&nbsp;%</button>
    <button type="button" data-v="add" data-pfx="$">Sumar&nbsp;$</button>
    <button type="button" data-v="sub" data-pfx="$">Restar&nbsp;$</button>
    <button type="button" data-v="factor" data-pfx="×">Multiplicar</button>
    <button type="button" data-v="set" data-pfx="$">Fijar precio</button>
   </div>
   <div class="be-valwrap"><span class="be-pfx" id="bePfx">%</span><input class="inp" id="beVal" type="number" step="0.01" placeholder="ej. 10" autocomplete="off"></div>
  </div>
  <div class="be-step">
   <div class="be-lbl"><span class="be-n">3</span>Redondeo</div>
   <div class="chips" id="segRound">
    <button type="button" data-v="none" class="on">Sin redondeo</button>
    <button type="button" data-v="int">Entero</button>
    <button type="button" data-v="d90">Termina en ,90</button>
    <button type="button" data-v="d99">Termina en ,99</button>
    <button type="button" data-v="tens">A la decena</button>
   </div>
  </div>
  <div class="be-summary" id="beSummary"><?=ico('eye',15)?><span>Configurá una operación para ver el resumen…</span></div>
  <div class="be-prev" id="bePrev" style="display:none"></div>
  <div class="be-actions">
   <button type="button" class="btn btn-g" id="bePreview"><?=ico('eye',16)?>Vista previa</button>
   <button type="button" class="btn btn-pri" id="beApply"><?=ico('bolt',16)?>Aplicar cambios</button>
  </div>
 </div>
</div>

<div class="panel" style="margin:16px 0 20px"><div class="ph"><h2><?=ico('box',16)?> Planilla Excel</h2><span class="be-sub">Exportá, editá en Excel y reimportá por la columna ID</span></div><div style="padding:16px 20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
  <a class="btn btn-pri" href="bulk.php?export=xlsx"><?=ico('save',16)?>Exportar a Excel (.xlsx)</a>
  <form method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="import"><input class="inp" type="file" name="file" accept=".xlsx,.csv" required style="padding:7px"><button class="btn btn-g"><?=ico('box',16)?>Importar planilla</button></form>
</div></div>

<div class="blkov" id="blkov"><div class="blkcard" id="blkcard">
 <div class="blk-scene">
  <div class="blk-ring"></div>
  <div class="blk-tag"><?=ico('tag',26)?></div>
 </div>
 <div class="blk-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
 <div class="blk-title" id="blkTitle">Aplicando cambios</div>
 <div class="blk-count"><span id="blkNum">0</span></div>
 <div class="blk-sub" id="blkSub">productos actualizándose…</div>
 <div class="blk-lbl" id="blkLbl"></div>
 <div class="blk-bar"><i></i></div>
 <button type="button" class="blk-btn" id="blkDone">Listo</button>
</div></div>

<style>
.blkeng .be-sub{font-size:12px;color:var(--mut2)}
.blkeng .be-body{padding:20px}
.be-step{display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:14px 0;border-bottom:1px solid var(--line)}
.be-step:first-child{padding-top:2px}
.be-lbl{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600;color:var(--ink);width:120px;flex-shrink:0}
.be-n{width:22px;height:22px;border-radius:50%;background:var(--navy,#0b1b3a);color:#fff;display:grid;place-items:center;font-size:12px;font-weight:700}
.seg{display:inline-flex;gap:3px;background:var(--bg,#f1f2f4);border-radius:12px;padding:4px;flex-wrap:wrap}
.seg.wrap{flex-wrap:wrap}
.seg button{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:0;background:transparent;border-radius:9px;font-size:13px;font-weight:600;color:var(--mut);cursor:pointer;transition:.14s}
.seg button svg{width:15px;height:15px}
.seg button:hover{color:var(--ink)}
.seg button.on{background:#fff;color:var(--navy,#0b1b3a);box-shadow:0 1px 3px rgba(0,0,0,.09)}
.be-sc{max-width:260px}
.be-valwrap{display:inline-flex;align-items:center;border:1px solid var(--line2,#d2d2d7);border-radius:11px;overflow:hidden;background:#fff}
.be-valwrap:focus-within{border-color:var(--blue);box-shadow:0 0 0 3px rgba(10,110,230,.12)}
.be-pfx{padding:0 4px 0 13px;color:var(--mut);font-weight:700;font-size:15px}
.be-valwrap input{border:0;outline:none;width:120px;height:42px;padding:0 12px;font-size:15px;font-weight:600;-moz-appearance:textfield}
.be-valwrap input::-webkit-outer-spin-button,.be-valwrap input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.chips{display:flex;gap:7px;flex-wrap:wrap}
.chips button{padding:8px 14px;border-radius:10px;border:1px solid var(--line);background:#fff;font-size:12.5px;font-weight:600;color:var(--mut);cursor:pointer;transition:.14s}
.chips button:hover{border-color:var(--blue);color:var(--blue)}
.chips button.on{background:var(--blue);border-color:var(--blue);color:#fff}
.be-summary{display:flex;align-items:center;gap:9px;margin-top:16px;padding:13px 16px;background:#eef4ff;border:1px solid #d4e4fb;border-radius:12px;font-size:14px;color:#1b3a6b;font-weight:600}
.be-summary svg{width:16px;height:16px;flex-shrink:0;opacity:.7}
.be-prev{margin-top:14px;border:1px solid var(--line);border-radius:14px;overflow:hidden}
.be-prev .bp-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 16px;background:var(--bg,#f8fafc);border-bottom:1px solid var(--line);flex-wrap:wrap}
.be-prev .bp-cnt{font-size:14px;font-weight:700}.be-prev .bp-cnt b{color:var(--blue);font-size:18px}
.be-prev table{width:100%;border-collapse:collapse}
.be-prev td,.be-prev th{padding:8px 16px;font-size:13px;text-align:left;border-bottom:1px solid var(--line)}
.be-prev th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--mut2)}
.be-prev tr:last-child td{border-bottom:none}
.be-prev .old{color:var(--mut);text-decoration:line-through;text-decoration-color:#e3a0b4;font-variant-numeric:tabular-nums}
.be-prev .arr{color:var(--mut2);padding:0 6px}
.be-prev .new{color:var(--ink);font-weight:700;font-variant-numeric:tabular-nums}
.be-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:18px}
/* overlay */
.blkov{position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;padding:22px;background:rgba(9,16,32,.62);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);opacity:0;visibility:hidden;transition:opacity .22s}
.blkov.on{opacity:1;visibility:visible}
.blkcard{width:360px;max-width:100%;background:#fff;border-radius:22px;padding:30px 28px 24px;box-shadow:0 30px 80px -20px rgba(0,0,0,.5);text-align:center;transform:translateY(10px) scale(.98);transition:transform .26s cubic-bezier(.2,1,.3,1)}
.blkov.on .blkcard{transform:none}
.blk-scene{position:relative;width:84px;height:84px;margin:4px auto 16px}
.blk-tag{position:absolute;inset:0;display:grid;place-items:center;color:var(--blue)}
.blk-tag svg{width:34px;height:34px;animation:blkbob 1.1s ease-in-out infinite}
@keyframes blkbob{0%,100%{transform:translateY(-3px) rotate(-6deg)}50%{transform:translateY(3px) rotate(6deg)}}
.blk-ring{position:absolute;inset:0;border-radius:50%;border:3px solid var(--line);border-top-color:var(--blue);animation:blkspin .8s linear infinite}
@keyframes blkspin{to{transform:rotate(360deg)}}
.blk-title{font-size:18px;font-weight:800;letter-spacing:-.3px;color:var(--ink)}
.blk-count{font-size:46px;font-weight:800;letter-spacing:-.02em;color:var(--blue);margin:8px 0 0;font-variant-numeric:tabular-nums;line-height:1}
.blk-sub{font-size:13px;color:var(--mut2);margin-top:2px}
.blk-lbl{font-size:12.5px;color:var(--mut);margin-top:8px;display:none}
.blk-bar{height:6px;border-radius:999px;background:var(--line);overflow:hidden;margin:18px 2px 0}
.blk-bar i{display:block;height:100%;width:40%;border-radius:999px;background:linear-gradient(90deg,var(--blue),#4aa3ff);animation:blkload 1.1s cubic-bezier(.65,0,.35,1) infinite}
@keyframes blkload{0%{transform:translateX(-120%)}60%,100%{transform:translateX(320%)}}
.blk-check{width:60px;height:60px;border-radius:50%;background:#eafaf0;color:var(--ok);display:none;place-items:center;margin:0 auto 12px;animation:blkpop .4s cubic-bezier(.2,1.3,.4,1) both}
.blk-check svg{width:30px;height:30px}
.blk-btn{display:none;width:100%;justify-content:center;background:var(--blue);color:#fff;border:0;padding:13px;border-radius:12px;font-size:14.5px;font-weight:600;cursor:pointer;margin-top:16px}
.blk-btn:hover{background:#0077ed}
.blkcard.ok .blk-scene,.blkcard.ok .blk-bar{display:none}
.blkcard.ok .blk-check{display:grid}
.blkcard.ok .blk-lbl{display:block}
.blkcard.ok .blk-btn{display:block}
@media(max-width:820px){.be-lbl{width:100%}.be-actions{flex-direction:column}.be-actions .btn{width:100%;justify-content:center}}
</style>
<script>
(function(){
 var CSRF=document.getElementById('blkCsrf').value;
 var E={type:'all',op:'incpct',round:'none'};
 var pfxEl=document.getElementById('bePfx'), valEl=document.getElementById('beVal'),
     sumEl=document.getElementById('beSummary').querySelector('span'), prev=document.getElementById('bePrev');
 var scCat=document.getElementById('scopeCat'), scBrand=document.getElementById('scopeBrand'), scCur=document.getElementById('scopeCur');
 function seg(id,key,after){ var box=document.getElementById(id);
   box.querySelectorAll('button').forEach(function(b){ b.addEventListener('click',function(){
     box.querySelectorAll('button').forEach(function(x){x.classList.remove('on');}); b.classList.add('on'); E[key]=b.dataset.v; if(after)after(b); summary(); }); }); }
 seg('segScope','type',function(){ scCat.style.display=E.type==='cat'?'':'none'; scBrand.style.display=E.type==='brand'?'':'none'; scCur.style.display=E.type==='currency'?'':'none'; });
 seg('segOp','op',function(b){ pfxEl.textContent=b.dataset.pfx; });
 seg('segRound','round');
 valEl.addEventListener('input',summary);
 [scCat,scBrand,scCur].forEach(function(s){ s.addEventListener('change',summary); });
 function scopeVal(){ return E.type==='cat'?scCat.value:E.type==='brand'?scBrand.value:E.type==='currency'?scCur.value:''; }
 function scopeTxt(){ if(E.type==='all')return'todos los productos'; if(E.type==='cat')return'categoría '+(scCat.options[scCat.selectedIndex]||{}).text; if(E.type==='brand')return'marca '+scBrand.value; return'moneda '+scCur.value; }
 var OPL={incpct:function(v){return'Aumentar '+v+'%';},decpct:function(v){return'Bajar '+v+'%';},add:function(v){return'Sumar $'+v;},sub:function(v){return'Restar $'+v;},factor:function(v){return'Multiplicar ×'+v;},set:function(v){return'Fijar en $'+v;}};
 var RL={none:'',int:' · entero',d90:' · ,90',d99:' · ,99',tens:' · decena'};
 function summary(){ var v=valEl.value; if(v===''){ sumEl.textContent='Configurá una operación para ver el resumen…'; return; }
   sumEl.textContent=OPL[E.op](v)+RL[E.round]+' · '+scopeTxt(); }
 function fd(){ var f=new FormData(); f.append('csrf',CSRF); f.append('scope_type',E.type); f.append('scope_val',scopeVal()); f.append('op',E.op); f.append('val',valEl.value); f.append('round',E.round); return f; }
 function money(v,cur){ var n=new Intl.NumberFormat('es-UY',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v); return (cur==='USD'?'US$ ':'$')+n; }
 document.getElementById('bePreview').addEventListener('click',function(){
   if(valEl.value===''){ valEl.focus(); return; }
   prev.style.display='block'; prev.innerHTML='<div style="padding:20px;text-align:center;color:var(--mut2)">Calculando…</div>';
   fetch('bulk.php?bulkajax=preview',{method:'POST',body:fd()}).then(function(r){return r.json();}).then(function(d){
     if(d.error){ prev.innerHTML='<div style="padding:18px;text-align:center;color:var(--red)">Revisá el valor ingresado.</div>'; return; }
     var rows=(d.sample||[]).map(function(s){ return '<tr><td>'+s.name+'</td><td style="text-align:right"><span class="old">'+money(s.oldp,s.cur)+'</span><span class="arr">→</span><span class="new">'+money(s.newp,s.cur)+'</span></td></tr>'; }).join('');
     prev.innerHTML='<div class="bp-head"><span class="bp-cnt"><b>'+d.count+'</b> productos afectados</span><span style="color:var(--mut2);font-size:12.5px">'+d.label+'</span></div>'+(rows?'<table><thead><tr><th>Producto</th><th style="text-align:right">Precio</th></tr></thead><tbody>'+rows+'</tbody></table>':'<div style="padding:16px;text-align:center;color:var(--mut2)">Sin coincidencias.</div>');
   }).catch(function(){ prev.innerHTML='<div style="padding:18px;text-align:center;color:var(--red)">Error al previsualizar.</div>'; });
 });
 var ov=document.getElementById('blkov'), card=document.getElementById('blkcard'),
     numEl=document.getElementById('blkNum'), subEl=document.getElementById('blkSub'),
     lblEl=document.getElementById('blkLbl'), titleEl=document.getElementById('blkTitle');
 document.getElementById('beApply').addEventListener('click',function(){
   if(valEl.value===''){ valEl.focus(); return; }
   if(!confirm('¿Aplicar este ajuste de precios? Modifica la base de datos.')) return;
   card.classList.remove('ok'); titleEl.textContent='Aplicando cambios'; numEl.textContent='0'; subEl.textContent='productos actualizándose…';
   ov.classList.add('on'); var t0=Date.now();
   fetch('bulk.php?bulkajax=apply',{method:'POST',body:fd()}).then(function(r){return r.json();}).then(function(d){
     var wait=Math.max(0,1200-(Date.now()-t0));
     setTimeout(function(){ done(d); },wait);
   }).catch(function(){ titleEl.textContent='Ocurrió un error'; subEl.textContent='Probá de nuevo.'; });
 });
 function done(d){ var N=(d&&d.count)||0; titleEl.textContent='¡Listo!'; subEl.textContent='productos actualizados';
   lblEl.textContent=(d&&d.label)||''; card.classList.add('ok');
   var s0=Date.now(), dur=800; (function tick(){ var p=Math.min(1,(Date.now()-s0)/dur); numEl.textContent=Math.round(N*(1-Math.pow(1-p,3))); if(p<1)requestAnimationFrame(tick); else numEl.textContent=N; })();
 }
 document.getElementById('blkDone').addEventListener('click',function(){ location.reload(); });
})();
</script>
<div class="panel"><div class="ph"><h2>Edición en línea</h2></div><div style="padding:14px 20px">
<form class="toolbar" method="get"><div class="srch"><?=ico('search')?><input name="q" value="<?=e($q)?>" placeholder="Buscar productos para editar…"></div>
<select class="inp sel2" name="cat" style="max-width:240px" onchange="this.form.submit()"><option value="">— Elegí categoría —</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$fcat==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select>
<button class="btn btn-g">Filtrar</button></form>
<?php if(!$rows):?><div style="padding:30px;text-align:center;color:var(--mut2)">Elegí una categoría o buscá para editar varios productos a la vez.</div>
<?php else:?>
<form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="saveall"><input type="hidden" name="ret" value="<?=e($ret)?>">
<table><thead><tr><th>Producto</th><th>Código</th><th>Precio (sin IVA)</th><th>Stock</th><th>Destacado</th></tr></thead><tbody>
<?php foreach($rows as $i=>$r):?><tr><td style="font-weight:500"><?=e($r['name'])?></td><td style="color:var(--mut2)"><?=e($r['sku'])?></td>
<td><input type="hidden" name="id[]" value="<?=$r['id']?>"><input class="inp" name="price[]" type="number" step="0.01" value="<?=e($r['price'])?>" style="width:120px;padding:7px 10px"></td>
<td><input class="inp" name="stock[]" type="number" value="<?=(int)$r['stock']?>" style="width:90px;padding:7px 10px"></td>
<td><input type="checkbox" name="featured[<?=$r['id']?>]" <?=$r['featured']?'checked':''?>></td></tr><?php endforeach;?>
</tbody></table>
<div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center"><span style="color:var(--mut2);font-size:13px"><?=count($rows)?> productos (máx 300)</span><button class="btn btn-pri"><?=ico('save',16)?>Guardar todos los cambios</button></div>
</form><?php endif;?>
</div></div>
<?php af();
