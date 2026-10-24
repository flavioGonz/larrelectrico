<?php require(__DIR__.'/inc.php'); require(__DIR__.'/layout.php'); require_admin();
$pdo=db();
$COLS=[['id','ID',0],['sku','Código / SKU',0],['name','Nombre',0],['brand','Marca',0],['price','Precio (sin IVA)',1],['stock','Stock',1],['featured','Destacado',1]];

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
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $act=$_POST['act']??'';
  if($act==='saveall'){
    $ids=$_POST['id']??[]; $n=0; $st=$pdo->prepare("UPDATE products SET price=?, stock=?, featured=? WHERE id=?");
    foreach($ids as $i=>$id){ $pr=($_POST['price'][$i]===''?null:(float)$_POST['price'][$i]); $stk=(int)$_POST['stock'][$i]; $ft=isset($_POST['featured'][$id])?1:0; $st->execute([$pr,$stk,$ft,(int)$id]); $n++; }
    flash("Se guardaron $n productos."); header('Location: bulk.php?'.$_POST['ret']); exit;
  }
  if($act==='pct'){
    $pct=(float)$_POST['pct']; $scope=$_POST['scope']; $factor=1+$pct/100;
    if($scope==='all'){ $pdo->prepare("UPDATE products SET price=ROUND(price*?,2) WHERE price IS NOT NULL")->execute([$factor]); }
    else{ $cid=(int)$scope; $pdo->prepare("UPDATE products p JOIN product_categories pc ON pc.product_id=p.id SET p.price=ROUND(p.price*?,2) WHERE pc.category_id=? AND p.price IS NOT NULL")->execute([$factor,$cid]); }
    flash("Precios ajustados ".($pct>0?'+':'').$pct."%."); header('Location: bulk.php'); exit;
  }
  if($act==='import' && !empty($_FILES['file']['tmp_name'])){
    $name=strtolower($_FILES['file']['name']); $n=0;
    $st=$pdo->prepare("UPDATE products SET price=?, stock=?, featured=? WHERE id=?");
    if(substr($name,-5)==='.xlsx'){
      $rows=read_xlsx($_FILES['file']['tmp_name']); if($rows){ $head=array_map(fn($s)=>strtolower(trim($s)),$rows[0]); 
        $ci=['id'=>array_search('id',$head),'price'=>null,'stock'=>null,'feat'=>null];
        foreach($head as $i=>$h){ if(strpos($h,'precio')!==false)$ci['price']=$i; if(strpos($h,'stock')!==false)$ci['stock']=$i; if(strpos($h,'destac')!==false)$ci['feat']=$i; if($h==='id')$ci['id']=$i; }
        for($i=1;$i<count($rows);$i++){ $r=$rows[$i]; if(!isset($r[$ci['id']])||!is_numeric($r[$ci['id']]))continue; $id=(int)$r[$ci['id']];
          $pr=$ci['price']!==null&&isset($r[$ci['price']])&&$r[$ci['price']]!==''?(float)str_replace(',','.',$r[$ci['price']]):null;
          $stk=$ci['stock']!==null&&isset($r[$ci['stock']])?(int)$r[$ci['stock']]:0;
          $ft=$ci['feat']!==null&&isset($r[$ci['feat']])?(int)!!$r[$ci['feat']]:0;
          $st->execute([$pr,$stk,$ft,$id]); $n++; }
      }
    } else {
      $f=fopen($_FILES['file']['tmp_name'],'r'); $head=fgetcsv($f); $col=array_flip(array_map('trim',(array)$head)); $stc=$pdo->prepare("UPDATE products SET price=?, stock=? WHERE id=?");
      while($row=fgetcsv($f)){ if(!isset($row[$col['id']]))continue; $id=(int)$row[$col['id']]; $pr=isset($col['price'])&&$row[$col['price']]!==''?(float)$row[$col['price']]:null; $stk=isset($col['stock'])?(int)$row[$col['stock']]:0; $stc->execute([$pr,$stk,$id]); $n++; }
      fclose($f);
    }
    flash("Importados/actualizados $n productos."); header('Location: bulk.php'); exit;
  }
}
$cats=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$fcat=(int)($_GET['cat']??0); $q=trim($_GET['q']??'');
$rows=[]; $ret=http_build_query(['cat'=>$fcat?:null,'q'=>$q?:null]);
if($fcat||$q){
  if($fcat){ $st=$pdo->prepare("SELECT p.id,p.sku,p.name,p.brand,p.price,p.stock,p.featured FROM products p JOIN product_categories pc ON pc.product_id=p.id WHERE pc.category_id=? ORDER BY p.name LIMIT 300"); $st->execute([$fcat]); }
  else{ $st=$pdo->prepare("SELECT id,sku,name,brand,price,stock,featured FROM products WHERE name LIKE ? OR sku LIKE ? OR brand LIKE ? ORDER BY name LIMIT 300"); $st->execute(["%$q%","%$q%","%$q%"]); }
  $rows=$st->fetchAll();
}
ah('Edición masiva');
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
 <div class="panel"><div class="ph"><h2>Ajuste de precios por %</h2></div><form method="post" style="padding:16px 20px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="pct">
  <div><label>% de ajuste</label><input class="inp" name="pct" type="number" step="0.1" placeholder="ej. 10 o -5" style="width:120px" required></div>
  <div style="flex:1"><label>Aplicar a</label><select class="inp" name="scope"><option value="all">Todos los productos</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select></div>
  <button class="btn btn-pri" onclick="return confirm('¿Ajustar precios? Modifica la base.')"><?=ico('save',16)?>Aplicar</button></form></div>
 <div class="panel"><div class="ph"><h2>Planilla Excel</h2></div><div style="padding:16px 20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
  <a class="btn btn-pri" href="bulk.php?export=xlsx"><?=ico('save',16)?>Exportar a Excel (.xlsx)</a>
  <form method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="act" value="import"><input class="inp" type="file" name="file" accept=".xlsx,.csv" required style="padding:7px"><button class="btn btn-g"><?=ico('box',16)?>Importar planilla</button></form>
 </div><p style="padding:0 20px 14px;color:var(--mut2);font-size:12px;margin:0">Exportá, editá precios/stock en Excel y volvé a importar el mismo archivo. Se actualiza por la columna <b>ID</b>.</p></div>
</div>
<div class="panel"><div class="ph"><h2>Edición en línea</h2></div><div style="padding:14px 20px">
<form class="toolbar" method="get"><div class="srch"><?=ico('search')?><input name="q" value="<?=e($q)?>" placeholder="Buscar productos para editar…"></div>
<select class="inp" name="cat" style="max-width:240px" onchange="this.form.submit()"><option value="">— Elegí categoría —</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$fcat==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select>
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
