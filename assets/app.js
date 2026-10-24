const WA='59894726950', IMGB='https://www.larrelectrico.uy/', API='/final/api/';
const IC={
 cart:'<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2l2.5 13h12l2-9H6"/>',
 plus:'<path d="M12 5v14M5 12h14"/>',minus:'<path d="M5 12h14"/>',x:'<path d="M6 6l12 12M18 6 6 18"/>',
 check:'<path d="M20 6 9 17l-5-5"/>',search:'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
 chev:'<path d="m6 9 6 6 6-6"/>',pkg:'<path d="m12 2 8 4.5v9L12 20l-8-4.5v-9z"/><path d="m4 7 8 4.5L20 7M12 20v-8.5"/>',
 bolt:'<path d="M13 2 3 14h7l-1 8 10-12h-7z"/>',wa:'<path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.5A10 10 0 1 0 12 2z"/>',
 truck:'<path d="M3 6h11v9H3zM14 9h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
 shield:'<path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
 star:'<path d="m12 3 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.8-5.2 2.8 1-5.8L3.6 9.1l5.8-.8z"/>',
 heart:'<path d="M12 21C5 15.5 3 11.5 3 8.5A4.5 4.5 0 0 1 12 6a4.5 4.5 0 0 1 9 2.5c0 3-2 7-9 12.5z"/>',
 scale:'<path d="M12 3v18M7 7l-4 7h8zM17 7l-4 7h8zM5 21h14"/>',fire:'<path d="M12 3c1 3 4 4 4 8a4 4 0 0 1-8 0c0-1 .5-2 1-3 .5 2 2 2 2 0 0-2-1-3 1-5z"/>',
 cable:'<path d="M4 4v6a4 4 0 0 0 4 4h8a4 4 0 0 1 4 4v2"/><circle cx="4" cy="3" r="1.6"/><circle cx="20" cy="21" r="1.6"/>',
 grid:'<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 bulb:'<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.3 1 2.5h6c0-1.2.3-1.8 1-2.5A6 6 0 0 0 12 3z"/>',
 pipe:'<path d="M4 8h10a4 4 0 0 1 4 4v8"/><path d="M4 5v6M14 17h8"/>',plug:'<path d="M9 2v6M15 2v6M7 8h10v3a5 5 0 0 1-10 0z"/><path d="M12 16v6"/>',
 chip:'<rect x="7" y="7" width="10" height="10" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>',
 tool:'<path d="M14.5 5.5a3.5 3.5 0 0 0-4.8 4.5l-6 6 2.3 2.3 6-6a3.5 3.5 0 0 0 4.5-4.8l-2 2-2-2z"/>',
 fire:'<path d="M12 3c1 3 4 4 4 8a4 4 0 0 1-8 0c0-1 .5-2 1-3 .5 2 2 2 2 0 0-2-1-3 1-5z"/>',
 scale:'<path d="M12 3v18M7 7l-4 7h8zM17 7l-4 7h8zM5 21h14"/>',
 trash:'<path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14"/>',
 user:'<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
 menu:'<path d="M3 6h18M3 12h18M3 18h18"/>',
 arrow:'<path d="M5 12h14M13 6l6 6-6 6"/>',
 file:'<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4M9 13h6M9 17h6"/>',
 play:'<path d="M8 5v14l11-7z"/>',info:'<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
 doc:'<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v4h4M9 13h6M9 17h6"/>',
 mail:'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>'
};
const svg=(n,w=20)=>`<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="${w}" height="${w}">${IC[n]||IC.pkg}</svg>`;
const catIcon=name=>{const n=(name||'').toUpperCase();
 if(/CABLE|GOMA|SUPER|CONDUCT|FLEX|UNIPOLAR|MULTIFIL/.test(n))return'cable';
 if(/DIFERENC/.test(n))return'shield';if(/TERMIC|T.RMIC|LLAVE|BREAKER|MAGNET/.test(n))return'bolt';
 if(/TABLERO|GABINET|CAJA|MODUL/.test(n))return'grid';if(/LED|ILUMIN|LAMPAR|REFLECT|PANEL|TUBO|FOCO|LUZ|BAMB/.test(n))return'bulb';
 if(/DUCTO|CANAL|CA.O|CORRUG/.test(n))return'pipe';if(/TOMA|ENCHUFE|PRESTA|COMBINAC|PUNTO|SE.ALIZ|MANDO/.test(n))return'plug';
 if(/CONTACT|RELE|REL.|AUTOMAT|CONTROL|TEMPOR/.test(n))return'chip';if(/FERRET|TORNILL|GRAMPA|PRECINT|ACCESOR|HERRAM|ABRAZAD|GALVANIZ/.test(n))return'tool';
 return'pkg';};
const IVA=0.22;
const money=n=>n==null?null:'$'+new Intl.NumberFormat('es-UY',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n);
const withIva=n=>n==null?null:n*(1+IVA);
const el=s=>document.querySelector(s), els=s=>[...document.querySelectorAll(s)];
const imgu=u=>!u?null:(u.startsWith('http')?u:IMGB+u.replace(/^\/+/,''));
const T={add:'Agregado al carrito',empty:'Tu carrito está vacío.',proc:'Procesando…',brand:' — Larr Eléctrico',consult:'Consultar',nostock:'Bajo pedido',instock:'En stock',sold:'vendidos',last:'Últimas unidades'};
window.pkgBox='<div class="noimg">'+svg('pkg',46)+'</div>';
let DATA=null;
async function getData(){ if(DATA)return DATA; try{const r=await fetch('/final/api/catalog.php');DATA=await r.json();}catch(e){const r=await fetch('/final/catalog.json');DATA=await r.json();} return DATA; }
/* CART */
let CART=JSON.parse(localStorage.getItem('larr_cart')||'[]');
const saveCart=()=>{localStorage.setItem('larr_cart',JSON.stringify(CART));renderCart();syncCart();};
window.LARR_USER=null; let _cartSyncT;
function syncCart(){ if(!window.LARR_USER)return; clearTimeout(_cartSyncT);
  _cartSyncT=setTimeout(()=>{ fetch(API+'cart_save.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items:CART.map(c=>({id:c.id,qty:c.qty,name:c.name,price:c.price,img:c.img}))})}).catch(()=>{}); },1500); }
async function recoverCart(){ const p=new URLSearchParams(location.search); const id=p.get('recover'), t=p.get('t'); if(!id||!t)return;
  try{ const r=await fetch(API+'cart_get.php?id='+encodeURIComponent(id)+'&t='+encodeURIComponent(t)).then(r=>r.json());
    if(r.ok&&r.items&&r.items.length){ r.items.forEach(it=>{ if(!CART.find(c=>c.id===it.id)) CART.push({id:it.id,qty:it.qty,name:it.name,price:it.price,img:it.img,sku:it.sku||''}); }); saveCart(); setTimeout(openCart,500); toast('Recuperamos tu carrito'); } }catch(e){}
  history.replaceState({},'',location.pathname); }
function addCart(p,q=1){const it=CART.find(c=>c.id===p.id);if(it)it.qty+=q;else CART.push({id:p.id,qty:q,name:p.name,price:p.price,img:(p.images&&p.images[0])||p.img,sku:p.sku});saveCart();toast(T.add);}
function setQty(id,q){const it=CART.find(c=>c.id===id);if(!it)return;it.qty=q;if(it.qty<1)CART=CART.filter(c=>c.id!==id);saveCart();}
function renderCart(){
  const n=CART.reduce((s,c)=>s+c.qty,0), b=el('#cbadge'); if(b){b.textContent=n;b.classList.toggle('on',n>0);}
  const body=el('#drawerB'),foot=el('#drawerF'); if(!body)return;
  if(!CART.length){body.innerHTML=`<div class="cempty">${svg('cart',46)}<div>${T.empty}</div></div>`;if(foot)foot.style.display='none';return;}
  let tot=0;
  body.innerHTML=CART.map(c=>{const price=c.price||0;tot+=price*c.qty;const im=imgu(c.img);
   return `<div class="ci">${im?`<img class="cim" src="${im}" onerror="this.style.visibility='hidden'">`:`<div class="cim"></div>`}
   <div style="flex:1;min-width:0"><h4>${c.name}</h4><div class="cp">${c.price?money(c.price):T.consult}</div>
   <div class="qty"><button data-q="${c.id}:-" title="Quitar uno">${svg('minus',14)}</button><span>${c.qty}</span><button data-q="${c.id}:+" title="Agregar uno">${svg('plus',14)}</button></div></div>
   <button class="rm" data-rm="${c.id}">Quitar</button></div>`;}).join('');
  foot.style.display='block';const iva=tot*IVA;
  if(el('#cartSub'))el('#cartSub').textContent=money(tot);
  if(el('#cartIva'))el('#cartIva').textContent=money(iva);
  el('#cartTot').textContent=money(tot+iva);
}
function openCart(){el('#drawer').classList.add('on');el('#ov').classList.add('on');}
function closeCart(){el('#drawer').classList.remove('on');el('#ov').classList.remove('on');}
function toast(t){let e=el('#toast');if(!e){e=document.createElement('div');e.id='toast';e.className='toast';document.body.appendChild(e);}e.innerHTML=svg('check',18)+'<span>'+t+'</span>';e.classList.add('on');clearTimeout(e._t);e._t=setTimeout(()=>e.classList.remove('on'),1800);}
function clearCart(){CART=[];localStorage.setItem('larr_cart','[]');renderCart();}
let PAYINFO=null;
async function getPayInfo(){ if(PAYINFO)return PAYINFO; try{PAYINFO=await (await fetch(API+'payinfo.php')).json();}catch(e){PAYINFO={methods:{},surcharge:0,discount:0.10,mp_enabled:false,wa:WA};} return PAYINFO; }
function waMessage(o,pay,info,name){
  const m=(info.methods||{})[pay]||{}; const L=[];
  L.push('Hola Larr Eléctrico! Soy '+name+'. Quiero confirmar este pedido:','');
  (o.lines||[]).forEach(l=>L.push('• '+l.qty+'x '+l.name+(l.sku?' ('+l.sku+')':'')));
  L.push('','Subtotal: '+money(o.subtotal),'IVA (22%): '+money(o.iva),'Total con IVA: '+money(o.total));
  if(o.discount_amt>0) L.push('Descuento transferencia (10%): -'+money(o.discount_amt));
  L.push('Total a pagar: '+money(o.total_pagar!=null?o.total_pagar:o.total));
  L.push('','Medio de pago: '+(m.label||pay));
  if(pay==='transfer'&&m.accounts) m.accounts.forEach(a=>L.push(a.bank+': '+a.num));
  if(pay==='giros') L.push('Giro a la C.I. 3.977.308-8');
  L.push('Pedido #'+o.order);
  return 'https://wa.me/'+WA+'?text='+encodeURIComponent(L.join('\n'));
}
async function checkout(){
  if(!CART.length)return;
  const info=await getPayInfo();
  const sub=CART.reduce((s,c)=>s+(c.price||0)*c.qty,0), iva=sub*IVA, tot=sub+iva;
  const methods=info.methods||{}; const keys=Object.keys(methods);
  let ov=el('#coOv'); if(!ov){ov=document.createElement('div');ov.id='coOv';ov.className='co-ov';document.body.appendChild(ov);}
  const shortD={transfer:'Transferencia o giro bancario.',giros:'Giros Redpagos / Abitab.',cod:'Abonás al recibir el pedido.',pickup:'Retirás en el local (48 hs).',mp:'Tarjeta, débito o cuotas, online.'};
  ov.innerHTML=`<div class="co-modal" role="dialog" aria-modal="true">
    <button class="co-x" id="coX" aria-label="Cerrar">${svg('x',20)}</button>
    <div class="co-head"><h3 class="co-h">Finalizar compra</h3><p class="co-subt">Completá tus datos, elegí cómo pagar y confirmá tu pedido.</p></div>
    <div class="co-body">
      <div class="co-lb">${svg('user',15)} Tus datos</div>
      <div class="co-fields">
        <input class="co-inp" id="coName" placeholder="Nombre y apellido *">
        <input class="co-inp" id="coPhone" placeholder="Teléfono / WhatsApp *">
        <input class="co-inp" id="coEmail" placeholder="Email (opcional)">
      </div>
      <input class="co-inp" id="coNote" placeholder="Comentario o nota del pedido (opcional)">
      <div class="co-lb" style="margin-top:6px">${svg('cart',15)} Medio de pago</div>
      <div class="co-paygrid">${keys.map((k,i)=>{const m=methods[k];const dis=(k==='mp'&&!info.mp_enabled);
        return `<label class="co-pay${dis?' off':''}"><input type="radio" name="copay" value="${k}" ${i===0?'checked':''} ${dis?'disabled':''}>
        <span class="co-radio"></span>
        <span class="co-paybody"><span class="co-payt">${m.label}${k==='transfer'&&info.discount>0?` <span class="co-disc">−${Math.round(info.discount*100)}%</span>`:''}</span>
        <span class="co-payd">${shortD[k]||m.desc||''}${dis?' · no disponible aún':''}</span></span></label>`;}).join('')}</div>
      <div class="co-detail" id="coDetail"></div>
    </div>
    <div class="co-foot">
      <div class="co-tot">
        <div class="co-trow"><span>Subtotal</span><span>${money(sub)}</span></div>
        <div class="co-trow"><span>IVA (22%)</span><span>${money(iva)}</span></div>
        <div class="co-trow co-disc-row" id="coDiscRow" style="display:none"><span>Descuento transferencia (10%)</span><span id="coDisc"></span></div>
        <div class="co-trow co-grand"><span>Total</span><span id="coTot">${money(tot)}</span></div>
      </div>
      <button class="btn btn-pri btn-lg co-go" id="coGo">${svg('wa',18)}<span>Confirmar por WhatsApp</span></button>
      <div class="co-msg" id="coMsg"></div>
    </div></div>`;
  requestAnimationFrame(()=>ov.classList.add('on'));
  const close=()=>ov.classList.remove('on');
  el('#coX').onclick=close; ov.onclick=e=>{if(e.target===ov)close();};
  try{const me=await fetch(API+'auth.php?action=me').then(r=>r.json());if(me&&me.user){if(me.user.name)el('#coName').value=me.user.name;if(me.user.email)el('#coEmail').value=me.user.email;if(me.user.phone)el('#coPhone').value=me.user.phone;}}catch(e){}
  const detailFor=k=>{const m=methods[k]||{};
    if(k==='transfer'&&m.accounts) return '<b>Transferí a:</b> '+m.accounts.map(a=>a.bank+' '+a.num).join(' &nbsp;·&nbsp; ')+' — y nos mandás el comprobante por WhatsApp.';
    if(k==='giros') return 'Giro a la <b>C.I. 3.977.308-8</b> (Redpagos o Abitab). Nos mandás el comprobante por WhatsApp.';
    if(k==='cod') return 'Abonás en efectivo cuando recibís el pedido.';
    if(k==='pickup') return 'Retirás tu pedido en nuestro local dentro de las 48 hs.';
    if(k==='mp') return info.mp_enabled?'Pagás online con Mercado Pago: tarjeta de crédito/débito o cuotas.':'Mercado Pago se habilita próximamente; por ahora coordinás por WhatsApp.';
    return '';};
  const updTot=()=>{const k=(ov.querySelector('input[name=copay]:checked')||{}).value;const go=el('#coGo');
    const det=el('#coDetail'), html=detailFor(k); det.innerHTML=html; det.style.display=html?'block':'none';
    if(k==='transfer'&&info.discount>0){const d=tot*info.discount;el('#coDiscRow').style.display='flex';el('#coDisc').textContent='− '+money(d);el('#coTot').textContent=money(tot-d);}
    else{el('#coDiscRow').style.display='none';el('#coTot').textContent=money(tot);}
    if(k==='mp')go.innerHTML=svg('arrow',18)+'<span>Pagar con Mercado Pago</span>';
    else go.innerHTML=svg('wa',18)+'<span>Confirmar por WhatsApp</span>';
    go.onclick=()=>confirmCheckout(info,close);};
  ov.querySelectorAll('input[name=copay]').forEach(r=>r.onchange=updTot); updTot();
}
async function confirmCheckout(info,close){
  const name=el('#coName').value.trim(),phone=el('#coPhone').value.trim(),email=el('#coEmail').value.trim(),note=el('#coNote').value.trim();
  const pay=(document.querySelector('input[name=copay]:checked')||{}).value||'transfer';
  const msgEl=el('#coMsg'),go=el('#coGo');
  if(!name||!phone){msgEl.textContent='Completá tu nombre y teléfono.';msgEl.className='co-msg err';return;}
  const lbl=go.innerHTML; go.disabled=true; go.innerHTML='<span>'+T.proc+'</span>';
  let o=null;
  try{o=await fetch(API+'order.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items:CART.map(c=>({id:c.id,qty:c.qty})),customer:name,phone,email,note,payment:pay})}).then(r=>r.json());}catch(e){}
  if(!o||!o.ok){msgEl.textContent='No pudimos registrar el pedido. Probá de nuevo.';msgEl.className='co-msg err';go.disabled=false;go.innerHTML=lbl;return;}
  if(pay==='mp'){
    if(info.mp_enabled){
      try{const mp=await fetch(API+'mp.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({order:o.order})}).then(r=>r.json());
        if(mp&&mp.ok&&mp.init_point){clearCart();location.href=mp.init_point;return;}}catch(e){}
      msgEl.textContent='No se pudo iniciar Mercado Pago. Te derivamos a WhatsApp.';msgEl.className='co-msg err';
    }else{msgEl.textContent='Mercado Pago aún no está activo. Te derivamos a WhatsApp.';msgEl.className='co-msg';}
  }
  const wa=waMessage(o,pay,info,name);
  clearCart(); go.disabled=false; go.innerHTML=lbl; close(); closeCart(); toast('Pedido registrado · #'+o.order);
  window.open(wa,'_blank');
}
/* WISHLIST */
let WL=JSON.parse(localStorage.getItem('larr_wl')||'[]');
const inWL=id=>WL.includes(id);
function toggleWL(id,btn){const i=WL.indexOf(id);if(i<0){WL.push(id);toast('Agregado a favoritos');}else{WL.splice(i,1);}localStorage.setItem('larr_wl',JSON.stringify(WL));if(btn)btn.classList.toggle('on',inWL(id));updateWLbadge();}
function updateWLbadge(){const b=el('#wlbadge');if(b){b.textContent=WL.length;b.classList.toggle('on',WL.length>0);}}
/* COMPARE */
let CMP=JSON.parse(localStorage.getItem('larr_cmp')||'[]');
const inCmp=id=>CMP.includes(id);
function toggleCmp(id,btn){const i=CMP.indexOf(id);if(i<0){if(CMP.length>=4){toast('Podés comparar hasta 4');return;}CMP.push(id);}else CMP.splice(i,1);localStorage.setItem('larr_cmp',JSON.stringify(CMP));if(btn)btn.classList.toggle('on',inCmp(id));document.querySelectorAll('[data-cmp="'+id+'"]').forEach(b=>b.classList.toggle('on',inCmp(id)));renderCmpBar();}
function renderCmpBar(){let bar=el('#cmpBar');if(!CMP.length){if(bar)bar.classList.remove('on');return;}
  if(!bar){bar=document.createElement('div');bar.id='cmpBar';bar.className='cmpbar';document.body.appendChild(bar);}
  bar.innerHTML=`<div class="cmpbar-in"><span class="cmpbar-t">${svg('scale',18)} <span class="cmpbar-lbl">Comparar</span> <b>${CMP.length}</b></span><div class="cmpbar-sp"></div><button class="btn btn-g btn-sm" onclick="clearCmp()">Limpiar</button><a class="btn btn-pri btn-sm" href="comparador.html">Ver comparación</a></div>`;
  requestAnimationFrame(()=>bar.classList.add('on'));}
function clearCmp(){CMP=[];localStorage.setItem('larr_cmp','[]');document.querySelectorAll('[data-cmp]').forEach(b=>b.classList.remove('on'));renderCmpBar();}
async function initCompare(){const d=await getData();const items=CMP.map(id=>d.products.find(p=>p.id===id)).filter(Boolean);const root=el('#cmpRoot');
  if(!items.length){root.innerHTML='<div class="cempty" style="padding:70px 20px">'+svg('scale',46)+'<div style="margin:12px 0 4px">No hay productos para comparar.</div><a class="btn btn-pri" style="margin-top:14px" href="catalogo.html">Ir al catálogo</a></div>';return;}
  const keys=[];const seen={};items.forEach(p=>(p.specs||[]).forEach(s=>{if(!seen[s.k]){seen[s.k]=1;keys.push(s.k);}}));
  const row=(label,fn)=>`<tr><td class="cmp-k">${label}</td>${items.map(p=>`<td>${fn(p)}</td>`).join('')}</tr>`;
  const specVal=(p,k)=>{const s=(p.specs||[]).find(x=>x.k===k);return s?s.v:'—';};
  root.innerHTML=`<div class="cmp-wrap"><table class="cmptable"><thead><tr><td></td>${items.map(p=>`<td class="cmp-head"><a href="producto.html?id=${p.id}"><div class="cmp-img">${(p.images&&p.images[0])?`<img src="${imgu(p.images[0])}" onerror="this.style.visibility='hidden'">`:svg('pkg',40)}</div><div class="cmp-name">${p.name}</div></a><button class="btn btn-d btn-sm" style="margin-top:8px" onclick="rmCmp(${p.id})">${svg('trash',14)} Quitar</button></td>`).join('')}</tr></thead><tbody>
  ${row('Precio',p=>p.price?`<b>${money(p.price)}</b> <span class="muted">+ IVA</span>`:'Consultar')}
  ${row('Con IVA',p=>p.price?money(withIva(p.price)):'—')}
  ${row('Marca',p=>p.brand||'—')}
  ${row('Stock',p=>p.stock>0?'En stock':'Bajo pedido')}
  ${row('Vendidos',p=>p.sales||0)}
  ${row('Código',p=>p.sku||'—')}
  ${keys.map(k=>row(k,p=>specVal(p,k))).join('')}
  <tr><td></td>${items.map(p=>`<td><button class="btn btn-pri btn-sm" onclick='cmpAdd(${p.id})'>${svg('cart',15)} Agregar</button></td>`).join('')}</tr>
  </tbody></table></div>`;
}
function rmCmp(id){CMP=CMP.filter(x=>x!==id);localStorage.setItem('larr_cmp',JSON.stringify(CMP));initCompare();renderCmpBar();}
function cmpAdd(id){const p=(DATA?.products||[]).find(x=>x.id===id);if(p){addCart(p);}}
/* CARD */
function stockLine(p){ if(p.stock>5) return `<span class="stk"><span class="d"></span>${T.instock}</span>`;
 if(p.stock>0) return `<span class="stk low"><span class="d"></span>${T.last} (${p.stock})</span>`;
 return `<span class="stk no"><span class="d"></span>${T.nostock}</span>`; }
function soldLine(p){ return p.sales>20?`<span class="sold">${svg('fire',12)}${p.sales} ${T.sold}</span>`:''; }
function pcard(p){
  const im=(p.images&&p.images[0])||p.img;
  const imgt=im?`<img loading="lazy" src="${imgu(im)}" onerror="this.closest('.pim').innerHTML=window.pkgBox">`:window.pkgBox;
  const price=p.price?`<div class="price">${money(p.price)}<span class="iva">+ IVA</span></div>`:`<div class="noprice">${T.consult}</div>`;
  const tag=p.sales>20?`<span class="tag sale">${svg('fire',12)}Top</span>`:(p.featured?`<span class="tag">${svg('star',12)}Destacado</span>`:'');
  return `<article class="pc2"><a class="pim" href="producto.html?id=${p.id}">${tag}${imgt}
   <button class="wl ${inWL(p.id)?'on':''}" data-wl="${p.id}" title="Agregar a favoritos">${svg('heart',17)}</button>
   <button class="cmp ${inCmp(p.id)?'on':''}" data-cmp="${p.id}" title="Comparar">${svg('scale',16)}</button></a>
   <div class="pb"><div class="br">${p.brand||''}</div>
   <h3><a href="producto.html?id=${p.id}">${p.name}</a></h3>
   <div class="metaline">${stockLine(p)}${soldLine(p)}</div>
   <div class="prow">${price}<button class="add" data-add="${p.id}" title="Agregar al carrito">${svg('plus',19)}</button></div></div></article>`;
}
function wireCards(root){
  root.addEventListener('click',e=>{
    const a=e.target.closest('[data-add]');if(a){e.preventDefault();const p=(DATA?.products||[]).find(x=>x.id===+a.dataset.add);if(p){addCart(p);a.classList.add('done');a.innerHTML=svg('check',19);setTimeout(()=>{a.classList.remove('done');a.innerHTML=svg('plus',19);},900);}return;}
    const w=e.target.closest('[data-wl]');if(w){e.preventDefault();toggleWL(+w.dataset.wl,w);return;}
    const c=e.target.closest('[data-cmp]');if(c){e.preventDefault();toggleCmp(+c.dataset.cmp,c);}
  });
}
/* CATALOG */
let VIEW=[],shown=0,BATCH=24,fCat='',fTerm='',fSort='rel';
async function initCatalog(){
  const d=await getData(); const params=new URLSearchParams(location.search);
  fCat=params.get('cat')||''; fTerm=params.get('q')||''; if(fTerm)el('#q').value=fTerm;
  buildSide(d);
  el('#q').addEventListener('input',e=>{clearTimeout(window._dt);window._dt=setTimeout(()=>{fTerm=e.target.value.trim();applyCat();},160);});
  el('#sort').addEventListener('change',e=>{fSort=e.target.value;applyCat();});
  wireCards(el('#grid'));
  applyCat();
  new IntersectionObserver(es=>{if(es[0].isIntersecting&&shown<VIEW.length)moreCat();},{rootMargin:'700px'}).observe(el('#sentinel'));
}
function buildSide(d){
  const box=el('#catlist');if(!box)return;
  const cats=d.categories.filter(c=>c.n>0).sort((a,b)=>b.n-a.n);
  box.innerHTML=`<button data-c="" class="${fCat?'':'on'}"><span class="ci">${svg('grid',17)}</span><span class="nm">Todas</span><span class="c">${d.count}</span></button>`+
    cats.map(c=>`<button data-c="${c.name.replace(/"/g,'&quot;')}" class="${fCat===c.name?'on':''}"><span class="ci">${svg(catIcon(c.name),17)}</span><span class="nm">${c.name}</span><span class="c">${c.n}</span></button>`).join('');
  box.onclick=e=>{const b=e.target.closest('button');if(!b)return;fCat=b.dataset.c;[...box.children].forEach(x=>x.classList.toggle('on',x===b));applyCat();window.scrollTo({top:el('.toolbar').offsetTop-80,behavior:'smooth'});};
}
function applyCat(){
  let v=DATA.products.filter(p=>{
    if(fCat&&!(p.categories||[]).includes(fCat))return false;
    if(fTerm){const t=fTerm.toLowerCase();if(!((p.name+' '+p.brand+' '+p.sku+' '+(p.categories||[]).join(' ')).toLowerCase().includes(t)))return false;}
    return true;});
  if(fSort==='pa')v.sort((a,b)=>(a.price??1e12)-(b.price??1e12));
  else if(fSort==='pd')v.sort((a,b)=>(b.price??-1)-(a.price??-1));
  else if(fSort==='az')v.sort((a,b)=>a.name.localeCompare(b.name,'es'));
  else if(fSort==='sold')v.sort((a,b)=>(b.sales||0)-(a.sales||0));
  else v.sort((a,b)=>a._o-b._o||(b.sales||0)-(a.sales||0));
  VIEW=v;shown=0;el('#grid').innerHTML='';
  el('#count').textContent=v.length+' producto'+(v.length!==1?'s':'')+(fCat?' en “'+fCat+'”':'')+(fTerm?' · “'+fTerm+'”':'');
  moreCat();
}
function moreCat(){const s=VIEW.slice(shown,shown+BATCH);el('#grid').insertAdjacentHTML('beforeend',s.map(pcard).join(''));shown+=s.length;el('#sentinel').innerHTML=shown<VIEW.length?'<div class=spin></div>':(VIEW.length?'':'Sin resultados para tu búsqueda.');}
function videoEmbed(url){ if(!url)return''; url=url.trim();
  const yt=url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([\w-]{11})/);
  if(yt)return `<div class="vidbox"><iframe src="https://www.youtube.com/embed/${yt[1]}" title="Unboxing" frameborder="0" allowfullscreen loading="lazy"></iframe></div>`;
  if(/\.(mp4|webm|ogg)(\?|$)/i.test(url))return `<div class="vidbox"><video controls preload="metadata" src="${url}"></video></div>`;
  return `<a class="btn btn-g" href="${url}" target="_blank" rel="noopener">${svg('fire',16)} Ver video</a>`;
}
/* PRODUCT */
async function initProduct(){
  const d=await getData();const id=+new URLSearchParams(location.search).get('id');
  const p=d.products.find(x=>x.id===id);const root=el('#ppRoot');
  if(!p){root.innerHTML='<div style="padding:80px 0;text-align:center" class="muted">Producto no encontrado. <a class="blue" href="catalogo.html">Volver al catálogo</a></div>';return;}
  document.title=p.name+T.brand;
  const imgs=(p.images&&p.images.length?p.images:(p.img?[p.img]:[]));
  const main=imgs.length?`<img id="ppMain" src="${imgu(imgs[0])}" onerror="this.parentNode.innerHTML=window.pkgBox">`:window.pkgBox;
  const thumbs=imgs.length>1?`<div class="thumbs">${imgs.map((u,i)=>`<button class="${i?'':'on'}" data-t="${imgu(u)}"><img src="${imgu(u)}"></button>`).join('')}</div>`:'';
  const cats=(p.categories||[]).map(c=>`<a href="catalogo.html?cat=${encodeURIComponent(c)}">${c}</a>`).join('');
  const customSpecs=(p.specs||[]).map(s=>[s.k,s.v]).filter(r=>r[0]&&r[1]);
  const baseSpecs=[['Marca',p.brand],['Código',p.sku],['Unidad',p.unit],['Categoría',(p.categories||[])[0]]].filter(r=>r[1]);
  const specs=customSpecs.concat(baseSpecs);
  const vlogo=p.vendor&&p.vendor.logo?imgu(p.vendor.logo):null;
  const videoHtml=videoEmbed(p.video);
  const stockBig = p.stock>5?`<div class="pp-stock"><span class="d"></span>${T.instock} · listo para despacho</div>`
    : p.stock>0?`<div class="pp-stock low"><span class="d"></span>¡${T.last}! Quedan ${p.stock}</div>`
    : `<div class="pp-stock no"><span class="d"></span>${T.nostock} · consultá disponibilidad</div>`;
  const sold = p.sales>20?`<span class="pp-sold">${svg('fire',15)} ${p.sales} ${T.sold}</span>`:'';
  const docsHtml=(p.docs&&p.docs.length)?`<div class="pp-docs">${p.docs.map(dc=>`<a class="pp-doc" href="${imgu(dc.url)}" target="_blank" rel="noopener"><span class="di">${svg('file',19)}</span><span class="dn">${dc.name||'Documento'}</span><span class="dl">${svg('arrow',16)}</span></a>`).join('')}</div>`:'';
  const specsHtml=specs.length?`<table class="spectable">${specs.map((s,i)=>`<tr class="${i%2?'alt':''}"><td class="k"><span class="sdot"></span>${s[0]}</td><td class="v">${s[1]}</td></tr>`).join('')}</table>`:'';
  const descHtml=(p.desc||p.s_desc)?`<div class="pp-desc">${p.desc||p.s_desc}</div>`:'<div class="pp-desc" style="color:var(--mut2)">Este producto no tiene descripción adicional. Consultanos por WhatsApp para más información.</div>';
  const _tabs=[];
  if(specsHtml)_tabs.push(['specs','chip','Especificaciones',specsHtml]);
  _tabs.push(['desc','info','Descripción',descHtml]);
  if(videoHtml)_tabs.push(['video','play','Unboxing',videoHtml]);
  if(docsHtml)_tabs.push(['docs','file','Documentos',docsHtml]);
  const tabsHtml=`<div class="pp-tabs"><div class="pt-nav">${_tabs.map((t,i)=>`<button class="${i===0?'on':''}" data-p="${t[0]}">${svg(t[1],17)}<span>${t[2]}</span></button>`).join('')}</div><div class="pt-body">${_tabs.map((t,i)=>`<div class="pt-pane${i===0?' on':''}" data-pane="${t[0]}">${t[3]}</div>`).join('')}</div></div>`;
  root.innerHTML=`<div class="pp">
    <div class="pp-gal"><div class="main">${vlogo?`<img class="pp-vlogo" src="${vlogo}" onerror="this.remove()">`:''}${main}<button class="wl big ${inWL(p.id)?'on':''}" id="ppWl" title="Agregar a favoritos">${svg('heart',20)}</button></div>${thumbs}</div>
    <div class="pp-info">
      <div class="crumb"><a href="index.html">Inicio</a> / <a href="catalogo.html">Catálogo</a> / <span>${p.name}</span></div>
      <div class="br-row"><span class="br">${p.brand||'Larr Eléctrico'}</span></div><h1>${p.name}</h1>
      <div class="sku-row">${p.sku?'<span class="sku">Código: '+p.sku+'</span>':''}${sold}</div>
      <div class="pp-price">${p.price?money(p.price)+' <span class="ivatag">+ IVA</span>':'<span class=noprice>Consultar precio</span>'}</div>
      ${p.price?`<div class="pp-iva">Precio final con IVA (22%): <b>${money(withIva(p.price))}</b></div>`:''}
      ${stockBig}
      <div class="pp-buy"><div class="stepper"><button id="qm">${svg('minus',17)}</button><span id="qv">1</span><button id="qp">${svg('plus',17)}</button></div>
      <button class="btn btn-pri btn-lg" id="ppAdd" style="flex:1">${svg('cart',18)} Agregar al carrito</button></div>
      <div class="pp-consult">
        <a class="btn btn-wa" id="ppWa">${svg('wa',18)} Por WhatsApp</a>
        <a class="btn btn-g" href="mailto:ventas@larrelectrico.uy?subject=${encodeURIComponent('Consulta: '+p.name)}&body=${encodeURIComponent('Hola, quiero consultar por el producto '+p.name+(p.sku?' (código '+p.sku+')':'')+'. ')}">${svg('mail',18)} Por correo</a>
      </div>
      <div class="pp-feat"><div class="f">${svg('truck',18)} Envío a todo el país</div><div class="f">${svg('shield',18)} Productos garantizados</div><div class="f">${svg('bolt',18)} Stock real actualizado</div><div class="f">${svg('check',18)} Asesoría técnica</div></div>
      ${cats?`<div class="chipcat">${cats}</div>`:''}
    </div></div>
    ${tabsHtml}
    <section class="sec" style="padding:44px 0 0"><h2 style="font-size:22px;font-weight:700">Productos relacionados</h2><div class="pgrid" id="rel" style="margin-top:20px"></div></section>`;
  let q=1;el('#qm').onclick=()=>{q=Math.max(1,q-1);el('#qv').textContent=q;};el('#qp').onclick=()=>{q++;el('#qv').textContent=q;};
  el('#ppAdd').onclick=()=>{addCart(p,q);openCart();};
  el('#ppWl').onclick=()=>toggleWL(p.id,el('#ppWl'));
  el('#ppWa').onclick=()=>{window.open(`https://wa.me/${WA}?text=${encodeURIComponent('Hola! Consulto por: '+p.name+(p.sku?' ('+p.sku+')':''))}`,'_blank');};
  els('.thumbs button').forEach(b=>b.onclick=()=>{el('#ppMain').src=b.dataset.t;els('.thumbs button').forEach(x=>x.classList.remove('on'));b.classList.add('on');});
  els('.pt-nav button').forEach(b=>b.onclick=()=>{els('.pt-nav button').forEach(x=>x.classList.toggle('on',x===b));els('.pt-pane').forEach(pp=>pp.classList.toggle('on',pp.dataset.pane===b.dataset.p));});
  const rel=d.products.filter(x=>x.id!==p.id&&(x.categories||[]).some(c=>(p.categories||[]).includes(c))).slice(0,4);
  const relbox=el('#rel');relbox.innerHTML=(rel.length?rel:d.products.filter(x=>x.id!==p.id).slice(0,4)).map(pcard).join('');wireCards(relbox);
}
/* AUTH */
function pwScore(p){let s=0;if(!p)return 0;if(p.length>=6)s++;if(p.length>=10)s++;if(/[A-Z]/.test(p)&&/[a-z]/.test(p))s++;if(/\d/.test(p))s++;if(/[^A-Za-z0-9]/.test(p))s++;return Math.min(s,4);}
async function initAuth(){
  const r=await fetch(API+'auth.php?action=me').then(r=>r.json()).catch(()=>({}));
  if(r.user){ window.LARR_USER=r.user; renderAccountLanding(r.user); return; }
  const tab=w=>{els('.tabs button').forEach(b=>b.classList.toggle('on',b.dataset.t===w));el('#formLogin').style.display=w==='login'?'block':'none';el('#formReg').style.display=w==='reg'?'block':'none';el('#authMsg').className='msg';};
  els('.tabs button').forEach(b=>b.onclick=()=>tab(b.dataset.t));
  if(new URLSearchParams(location.search).get('reg')==='1')tab('reg');
  // mostrar / ocultar contraseña
  els('.pwtog').forEach(b=>b.onclick=()=>{const i=el('#'+b.dataset.tog);if(!i)return;i.type=i.type==='password'?'text':'password';b.classList.toggle('on',i.type==='text');});
  // medidor de fuerza
  const rp=el('#rgPass');
  if(rp)rp.addEventListener('input',()=>{const s=pwScore(rp.value);const m=el('#pwMeter'),lb=el('#pwLabel');if(m)m.dataset.s=s;const L=['','Débil','Aceptable','Buena','Fuerte'];if(lb){lb.textContent=rp.value?L[s]:'';lb.dataset.s=s;}});
  // validación de email en vivo
  const re=el('#rgEmail'),rh=el('#rgEmailHint');
  if(re)re.addEventListener('input',()=>{if(!rh)return;const v=re.value.trim();if(!v){rh.textContent='';rh.className='field-hint';return;}const ok=/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);rh.textContent=ok?'✓ Email válido':'Revisá el formato del email';rh.className='field-hint '+(ok?'ok':'err');});
  el('#formLogin').onsubmit=async ev=>{ev.preventDefault();const m=el('#authMsg');m.className='msg';const btn=ev.target.querySelector('button');const t=btn.innerHTML;btn.disabled=true;btn.innerHTML='Ingresando…';
    const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'login',email:el('#liEmail').value,pass:el('#liPass').value})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));
    if(res.error){m.className='msg err';m.textContent=res.error;btn.disabled=false;btn.innerHTML=t;}else location.reload();};
  el('#formReg').onsubmit=async ev=>{ev.preventDefault();const m=el('#authMsg');m.className='msg';
    const name=el('#rgName').value.trim(),email=el('#rgEmail').value.trim(),phone=el('#rgPhone').value.trim(),pass=el('#rgPass').value;
    if(!name){m.className='msg err';m.textContent='Ingresá tu nombre y apellido.';return;}
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){m.className='msg err';m.textContent='Ingresá un email válido.';return;}
    if(pass.length<6){m.className='msg err';m.textContent='La contraseña debe tener al menos 6 caracteres.';return;}
    if(el('#rgTerms')&&!el('#rgTerms').checked){m.className='msg err';m.textContent='Tenés que aceptar que te contactemos para continuar.';return;}
    const btn=el('#rgBtn');const t=btn.innerHTML;btn.disabled=true;btn.innerHTML='Creando tu cuenta…';
    const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'register',name,email,phone,pass})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));
    if(res.error){m.className='msg err';m.textContent=res.error;btn.disabled=false;btn.innerHTML=t;return;}
    window.LARR_USER=res.user;
    el('#authRoot').innerHTML=`<div class="auth-success"><div class="as-ic">${svg('check',36)}</div><h2>¡Cuenta creada!</h2><p>Te damos la bienvenida, ${esc2((name||'').split(' ')[0])}. Te enviamos un correo de confirmación a <b>${esc2(email)}</b>.</p><a class="btn btn-pri btn-block btn-lg" href="index.html">${svg('cart',18)} Empezar a comprar</a><button class="btn btn-ghost btn-block" id="asAcct">Ver mi cuenta</button></div>`;
    const ab=el('#asAcct');if(ab)ab.onclick=()=>openAcct();};
}
const PAYLBL={transfer:'Transferencia / Giro',giros:'Giros Redpagos / Abitab',cod:'Pago contra entrega',pickup:'Retira en local',mp:'Mercado Pago'};
const ORD_STEPS=['nuevo','pagado','en proceso','enviado','entregado'];
function ordStepper(status){
  if(status==='cancelado') return '<div class="ord-cancel">'+svg('x',15)+' Pedido cancelado</div>';
  let i=ORD_STEPS.indexOf(status); if(i<0)i=0;
  return '<div class="ord-steps">'+ORD_STEPS.map((s,x)=>`<div class="ord-step${x<=i?' done':''}${x===i?' cur':''}"><span class="od-dot">${x<i?svg('check',12):''}</span><span class="od-lb">${s}</span></div>`).join('')+'</div>';
}
function esc2(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');}
function renderAccount(u){
  const wrap=document.querySelector('.authwrap'); if(!wrap){el('#authRoot').innerHTML='';return;}
  wrap.classList.add('acct');
  const av=u.avatar?imgu(u.avatar):null;
  const ini=((u.name||'?').trim()[0]||'?').toUpperCase();
  wrap.innerHTML=`<div class="acc">
    <div class="acc-top">
      <label class="acc-av" id="avBox" title="Cambiar foto">
        ${av?`<img id="avImg" src="${av}">`:`<span class="acc-ini" id="avImg">${ini}</span>`}
        <span class="acc-cam">${svg('plus',15)}</span>
        <input type="file" id="avFile" accept="image/*" hidden>
      </label>
      <div class="acc-id"><h1>${esc2(u.name)}</h1><p>${esc2(u.email)}${u.phone?' · '+esc2(u.phone):''}</p></div>
      <button class="btn btn-g" id="logout">${svg('arrow',16)} Cerrar sesión</button>
    </div>
    <div class="acc-tabs">
      <button class="on" data-t="orders">${svg('cart',16)} Mis pedidos</button>
      <button data-t="profile">${svg('user',16)} Mi perfil</button>
      <button data-t="sec">${svg('shield',16)} Seguridad</button>
    </div>
    <div class="acc-pane" id="paneOrders"><div class="acc-load">Cargando tus pedidos…</div></div>
    <div class="acc-pane" id="paneProfile" style="display:none">
      <div class="acc-card">
        <div class="acc-ff"><div class="field"><label>Nombre y apellido</label><input class="inp2" id="pfName" value="${esc2(u.name)}"></div>
        <div class="field"><label>Email</label><input class="inp2" id="pfEmail" type="email" value="${esc2(u.email)}"></div></div>
        <div class="field"><label>Teléfono</label><input class="inp2" id="pfPhone" value="${esc2(u.phone||'')}" placeholder="Ej. 099 123 456"></div>
        <div class="acc-row"><button class="btn btn-pri" id="pfSave">${svg('check',16)} Guardar cambios</button><span class="acc-msg" id="pfMsg"></span></div>
      </div>
    </div>
    <div class="acc-pane" id="paneSec" style="display:none">
      <div class="acc-card">
        <div class="field"><label>Contraseña actual</label><input class="inp2" id="scCur" type="password" autocomplete="current-password"></div>
        <div class="acc-ff"><div class="field"><label>Nueva contraseña</label><input class="inp2" id="scNew" type="password" autocomplete="new-password" placeholder="Mínimo 6 caracteres"></div>
        <div class="field"><label>Repetir nueva</label><input class="inp2" id="scNew2" type="password" autocomplete="new-password"></div></div>
        <div class="acc-row"><button class="btn btn-pri" id="scSave">${svg('shield',16)} Cambiar contraseña</button><span class="acc-msg" id="scMsg"></span></div>
      </div>
    </div>
  </div>`;
  const panes={orders:'#paneOrders',profile:'#paneProfile',sec:'#paneSec'};
  wrap.querySelectorAll('.acc-tabs button').forEach(b=>b.onclick=()=>{wrap.querySelectorAll('.acc-tabs button').forEach(x=>x.classList.toggle('on',x===b));Object.entries(panes).forEach(([k,sel])=>el(sel).style.display=(k===b.dataset.t)?'block':'none');});
  el('#logout').onclick=async()=>{await fetch(API+'auth.php?action=logout');location.href='index.html';};
  // avatar
  el('#avBox').onclick=e=>{if(e.target.tagName!=='INPUT')el('#avFile').click();};
  el('#avFile').onchange=async()=>{const f=el('#avFile').files[0];if(!f)return;const fd=new FormData();fd.append('file',f);
    const box=el('#avBox');box.classList.add('up');
    const r=await fetch(API+'avatar.php',{method:'POST',body:fd}).then(r=>r.json()).catch(()=>({error:'Error de red'}));box.classList.remove('up');
    if(r.ok){const url=imgu(r.avatar);let img=el('#avImg');if(img&&img.tagName==='IMG')img.src=url;else img.outerHTML='<img id="avImg" src="'+url+'">';const nv=el('#navUserImg');if(nv)nv.src=url;toast('Foto actualizada');}
    else alert(r.error||'No se pudo subir la foto.');};
  // profile save
  el('#pfSave').onclick=async()=>{const b=el('#pfMsg');b.textContent='';b.className='acc-msg';
    const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'update',name:el('#pfName').value,email:el('#pfEmail').value,phone:el('#pfPhone').value})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));
    if(res.error){b.className='acc-msg err';b.textContent=res.error;}else{b.className='acc-msg ok';b.textContent='✓ Datos guardados';wrap.querySelector('.acc-id h1').textContent=res.user.name;wrap.querySelector('.acc-id p').innerHTML=esc2(res.user.email)+(res.user.phone?' · '+esc2(res.user.phone):'');toast('Perfil actualizado');}};
  // password
  el('#scSave').onclick=async()=>{const b=el('#scMsg');b.textContent='';b.className='acc-msg';
    if(el('#scNew').value!==el('#scNew2').value){b.className='acc-msg err';b.textContent='Las contraseñas nuevas no coinciden.';return;}
    const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'password',current:el('#scCur').value,new:el('#scNew').value})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));
    if(res.error){b.className='acc-msg err';b.textContent=res.error;}else{b.className='acc-msg ok';b.textContent='✓ Contraseña actualizada';el('#scCur').value=el('#scNew').value=el('#scNew2').value='';toast('Contraseña cambiada');}};
  loadAccOrders();
}
async function loadAccOrders(){
  const box=el('#paneOrders');if(!box)return;
  const r=await fetch(API+'auth.php?action=orders').then(r=>r.json()).catch(()=>({}));
  const os=r.orders||[];
  if(!os.length){box.innerHTML='<div class="acc-empty">'+svg('cart',42)+'<p>Todavía no hiciste pedidos.</p><a class="btn btn-pri" href="catalogo.html">Ir al catálogo</a></div>';return;}
  box.innerHTML=os.map(o=>`<div class="ord">
    <div class="ord-h"><div class="ord-hl"><b>Pedido #${o.id}</b><span class="ord-date">${(o.created||'').slice(0,16).replace('T',' ')}</span></div><div class="ord-tot">${money(o.total)}</div></div>
    ${ordStepper(o.status)}
    <div class="ord-items">${o.items.map(it=>`<span class="ord-it">${it.qty}× ${esc2(it.name)}</span>`).join('')}</div>
    ${o.payment?`<div class="ord-pay">${svg('cart',13)} ${PAYLBL[o.payment]||o.payment}</div>`:''}
  </div>`).join('');
}
/* ===== ACCOUNT DRAWER ===== */
function orderCardHtml(o){
  return `<div class="ord">
    <div class="ord-h"><div class="ord-hl"><b>Pedido #${o.id}</b><span class="ord-date">${(o.created||'').slice(0,16).replace('T',' ')}</span></div><div class="ord-tot">${money(o.total)}</div></div>
    ${ordStepper(o.status)}
    <div class="ord-items">${o.items.map(it=>`<span class="ord-it">${it.qty}× ${esc2(it.name)}</span>`).join('')}</div>
    ${o.payment?`<div class="ord-pay">${svg('cart',13)} ${PAYLBL[o.payment]||o.payment}</div>`:''}</div>`;
}
function acctOverlay(){ let ov=el('#acctOv'); if(!ov){ov=document.createElement('div');ov.id='acctOv';ov.className='acd-ov';ov.innerHTML='<aside class="acd" id="acctDrawer"></aside>';document.body.appendChild(ov);ov.addEventListener('click',e=>{if(e.target===ov)closeAcct();});} return ov; }
function closeAcct(){ const ov=el('#acctOv'); if(ov)ov.classList.remove('on'); }
async function openAcct(){
  let u=window.LARR_USER; if(!u){ try{u=(await fetch(API+'auth.php?action=me').then(r=>r.json())).user;}catch(e){} }
  if(!u){ location.href='cuenta.html'; return; }
  window.LARR_USER=u;
  const ov=acctOverlay(); requestAnimationFrame(()=>ov.classList.add('on'));
  acctHome(u);
}
function acdHeader(u){ const av=u.avatar?imgu(u.avatar):null; const ini=((u.name||'?').trim()[0]||'?').toUpperCase();
  return `<div class="acd-head"><span class="acd-av">${av?`<img src="${av}">`:`<span class="acd-ini">${ini}</span>`}</span><div class="acd-id"><b>${esc2(u.name)}</b><span>${esc2(u.email)}</span></div><button class="icbtn" id="acdClose">${svg('x',20)}</button></div>`; }
function acctHome(u){
  const d=el('#acctDrawer');
  const items=[['perfil','user','Mi perfil','Nombre, contacto y foto'],['orders','cart','Mis pedidos','Seguimiento de tus compras'],['envios','truck','Datos de envío','Dirección y preferencias'],['pass','shield','Seguridad','Cambiar contraseña']];
  d.innerHTML=acdHeader(u)+`<div class="acd-body"><div class="acd-menu">${items.map(it=>`<button class="acd-mi" data-go="${it[0]}"><span class="acd-mic">${svg(it[1],19)}</span><span class="acd-mt"><b>${it[2]}</b><span>${it[3]}</span></span><span class="acd-ch">${svg('chev',16)}</span></button>`).join('')}<button class="acd-mi logout" id="acdLogout"><span class="acd-mic out">${svg('arrow',19)}</span><span class="acd-mt"><b>Cerrar sesión</b></span></button></div></div>`;
  el('#acdClose').onclick=closeAcct;
  el('#acdLogout').onclick=async()=>{await fetch(API+'auth.php?action=logout');window.LARR_USER=null;location.href='index.html';};
  d.querySelectorAll('[data-go]').forEach(b=>b.onclick=()=>acctView(b.dataset.go,window.LARR_USER||u));
}
function acctView(key,u){
  const d=el('#acctDrawer');
  const titles={perfil:'Mi perfil',envios:'Datos de envío',pass:'Seguridad',orders:'Mis pedidos'};
  let body='';
  if(key==='perfil'){ const av=u.avatar?imgu(u.avatar):null; const ini=((u.name||'?').trim()[0]||'?').toUpperCase();
    body=`<label class="acd-avbox" id="avBox2">${av?`<img id="avImg2" src="${av}">`:`<span class="acd-ini big" id="avImg2">${ini}</span>`}<span class="acd-cam">${svg('plus',14)}</span><input type="file" id="avFile2" accept="image/*" hidden></label>
      <div class="acd-f"><label>Nombre y apellido</label><input class="inp2" id="pf2Name" value="${esc2(u.name)}"></div>
      <div class="acd-f"><label>Email</label><input class="inp2" id="pf2Email" type="email" value="${esc2(u.email)}"></div>
      <div class="acd-f"><label>Teléfono</label><input class="inp2" id="pf2Phone" value="${esc2(u.phone||'')}" placeholder="Ej. 099 123 456"></div>
      <button class="btn btn-pri btn-block" id="pf2Save">${svg('check',16)} Guardar cambios</button><div class="acd-msg" id="pf2Msg"></div>`;
  } else if(key==='envios'){
    body=`<div class="acd-f"><label>Dirección</label><input class="inp2" id="sh2Addr" value="${esc2(u.address||'')}" placeholder="Calle, número, apto"></div>
      <div class="acd-f"><label>Ciudad / Localidad</label><input class="inp2" id="sh2City" value="${esc2(u.city||'')}"></div>
      <div class="acd-f"><label>Notas para la entrega</label><textarea class="inp2" id="sh2Notes" rows="2" placeholder="Referencias, horarios, etc.">${esc2(u.ship_notes||'')}</textarea></div>
      <button class="btn btn-pri btn-block" id="sh2Save">${svg('check',16)} Guardar datos de envío</button><div class="acd-msg" id="sh2Msg"></div>
      <p class="acd-note">${svg('truck',14)} Despachamos a todo el país. Guardamos estos datos para agilizar tu próximo pedido; el envío se coordina por WhatsApp.</p>`;
  } else if(key==='pass'){
    body=`<div class="acd-f"><label>Contraseña actual</label><input class="inp2" id="pw2Cur" type="password" autocomplete="current-password"></div>
      <div class="acd-f"><label>Nueva contraseña</label><input class="inp2" id="pw2New" type="password" autocomplete="new-password" placeholder="Mínimo 6 caracteres"></div>
      <div class="acd-f"><label>Repetir nueva contraseña</label><input class="inp2" id="pw2New2" type="password" autocomplete="new-password"></div>
      <button class="btn btn-pri btn-block" id="pw2Save">${svg('shield',16)} Cambiar contraseña</button><div class="acd-msg" id="pw2Msg"></div>`;
  } else if(key==='orders'){ body=`<div id="acdOrders"><div class="acd-load">Cargando tus pedidos…</div></div>`; }
  d.innerHTML=acdHeader(u)+`<div class="acd-body"><div class="acd-sec-h"><button class="acd-back" id="acdBack">${svg('chev',18)}</button><h3>${titles[key]}</h3></div><div class="acd-sec-b">${body}</div></div>`;
  el('#acdClose').onclick=closeAcct;
  el('#acdBack').onclick=()=>acctHome(window.LARR_USER||u);
  if(key==='perfil'){
    el('#avBox2').onclick=e=>{if(e.target.tagName!=='INPUT')el('#avFile2').click();};
    el('#avFile2').onchange=async()=>{const f=el('#avFile2').files[0];if(!f)return;const fd=new FormData();fd.append('file',f);const r=await fetch(API+'avatar.php',{method:'POST',body:fd}).then(r=>r.json()).catch(()=>({error:'Error de red'}));if(r.ok){const url=imgu(r.avatar);let img=el('#avImg2');if(img&&img.tagName==='IMG')img.src=url;else if(img)img.outerHTML='<img id="avImg2" src="'+url+'">';if(window.LARR_USER)window.LARR_USER.avatar=r.avatar;const nv=el('#navUserImg');if(nv)nv.src=url;toast('Foto actualizada');}else alert(r.error||'No se pudo subir la foto.');};
    el('#pf2Save').onclick=async()=>{const b=el('#pf2Msg');b.className='acd-msg';const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'update',name:el('#pf2Name').value,email:el('#pf2Email').value,phone:el('#pf2Phone').value})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));if(res.error){b.className='acd-msg err';b.textContent=res.error;}else{window.LARR_USER=res.user;b.className='acd-msg ok';b.textContent='✓ Datos guardados';toast('Perfil actualizado');}};
  }
  if(key==='envios'){
    el('#sh2Save').onclick=async()=>{const b=el('#sh2Msg');b.className='acd-msg';const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'shipping',address:el('#sh2Addr').value,city:el('#sh2City').value,ship_notes:el('#sh2Notes').value})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));if(res.error){b.className='acd-msg err';b.textContent=res.error;}else{window.LARR_USER=res.user;b.className='acd-msg ok';b.textContent='✓ Datos de envío guardados';toast('Datos de envío guardados');}};
  }
  if(key==='pass'){
    el('#pw2Save').onclick=async()=>{const b=el('#pw2Msg');b.className='acd-msg';if(el('#pw2New').value!==el('#pw2New2').value){b.className='acd-msg err';b.textContent='Las contraseñas nuevas no coinciden.';return;}const res=await fetch(API+'auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'password',current:el('#pw2Cur').value,new:el('#pw2New').value})}).then(r=>r.json()).catch(()=>({error:'Error de red'}));if(res.error){b.className='acd-msg err';b.textContent=res.error;}else{b.className='acd-msg ok';b.textContent='✓ Contraseña actualizada';el('#pw2Cur').value=el('#pw2New').value=el('#pw2New2').value='';toast('Contraseña cambiada');}};
  }
  if(key==='orders'){ (async()=>{const box=el('#acdOrders');const r=await fetch(API+'auth.php?action=orders').then(r=>r.json()).catch(()=>({}));const os=r.orders||[];box.innerHTML=os.length?os.map(orderCardHtml).join(''):'<div class="acd-empty">'+svg('cart',40)+'<p>Todavía no hiciste pedidos.</p><a class="btn btn-pri btn-block" href="catalogo.html">Ir al catálogo</a></div>';})(); }
}
function renderAccountLanding(u){
  const wrap=document.querySelector('.authwrap'); if(wrap){wrap.classList.add('acct');
    wrap.innerHTML=`<div class="acc-land"><div class="acc-land-av">${u.avatar?`<img src="${imgu(u.avatar)}">`:`<span>${((u.name||'?').trim()[0]||'?').toUpperCase()}</span>`}</div><h1>Hola, ${esc2((u.name||'').split(' ')[0])}</h1><p>Gestioná tu perfil, pedidos, datos de envío y seguridad.</p><button class="btn btn-pri btn-lg" id="openAcctBtn">${svg('user',18)} Abrir mi cuenta</button></div>`;
    el('#openAcctBtn').onclick=()=>openAcct();}
  openAcct();
}
async function initFav(){const d=await getData();const box=el('#favGrid');const items=d.products.filter(p=>WL.includes(p.id));
  if(!items.length){box.innerHTML='';el('#favEmpty').innerHTML='<div class="cempty" style="padding:70px 20px">'+svg('heart',46)+'<div style="margin:12px 0 4px">Todavía no tienes favoritos.</div><a class="btn btn-pri" style="margin-top:14px" href="catalogo.html">Ir al catálogo</a></div>';return;}
  el('#favEmpty').innerHTML='';box.innerHTML=items.map(pcard).join('');wireCards(box);}
async function initHeader(){
  const mm=el('#megamenu'); const q=new URLSearchParams(location.search).get('q'); const si=document.querySelector('.navsearch input'); if(si&&q)si.value=q;
  if(!mm&&!el('#burger'))return;
  const d=await getData(); const cats=d.categories.filter(c=>c.n>0).sort((a,b)=>b.n-a.n).slice(0,11);
  if(mm)mm.innerHTML=cats.map(c=>`<a href="catalogo.html?cat=${encodeURIComponent(c.name)}"><span class="mi">${svg(catIcon(c.name),17)}</span><span>${c.name}</span><span class="mc">${c.n}</span></a>`).join('')+`<a class="mall" href="catalogo.html">Ver todo el catálogo</a>`;
  const b=el('#burger'); if(b)b.onclick=()=>openMobMenu(cats);
  initLiveSearch(d);
  try{const me=await fetch(API+'auth.php?action=me').then(r=>r.json());
    window.LARR_USER=(me&&me.user)?me.user:null;
    const nu=el('#navUser');
    if(window.LARR_USER&&nu){
      if(window.LARR_USER.avatar){nu.classList.add('hasav');nu.innerHTML='<img id="navUserImg" src="'+imgu(window.LARR_USER.avatar)+'">';}
      nu.onclick=e=>{e.preventDefault();e.stopPropagation();openAcct();};
    }
    if(window.LARR_USER&&CART.length)syncCart();
  }catch(e){}
}
function initLiveSearch(d){
  const form=document.querySelector('.navsearch'); const inp=form&&form.querySelector('input'); if(!inp)return;
  let drop=form.querySelector('.searchdrop'); if(!drop){drop=document.createElement('div');drop.className='searchdrop';form.appendChild(drop);}
  const render=t=>{ t=t.trim().toLowerCase();
    if(t.length<2){drop.classList.remove('on');return;}
    const res=d.products.filter(p=>(p.name+' '+p.brand+' '+p.sku).toLowerCase().includes(t)).slice(0,6);
    if(!res.length){drop.innerHTML='<div class="sd-empty">Sin resultados para “'+t+'”</div>';drop.classList.add('on');return;}
    drop.innerHTML=res.map(p=>{const im=(p.images&&p.images[0])||p.img;return `<a href="producto.html?id=${p.id}" class="sd-item"><span class="sd-im">${im?`<img src="${imgu(im)}" onerror="this.style.visibility='hidden'">`:svg('pkg',18)}</span><span class="sd-tx"><span class="sd-name">${p.name}</span><span class="sd-sub">${p.brand||''}</span></span><span class="sd-price">${p.price?money(p.price):''}</span></a>`;}).join('')
      +`<a href="catalogo.html?q=${encodeURIComponent(t)}" class="sd-all">${svg('search',15)} Ver todos los resultados</a>`;
    drop.classList.add('on');
  };
  inp.addEventListener('input',e=>{clearTimeout(window._ls);window._ls=setTimeout(()=>render(e.target.value),120);});
  inp.addEventListener('focus',e=>{if(e.target.value.trim().length>=2)render(e.target.value);});
  document.addEventListener('click',e=>{if(!form.contains(e.target))drop.classList.remove('on');});
}
async function initMarquee(){
  const m=document.querySelector('.marq'); if(!m)return; const d=await getData();
  const vs=(d.vendors||[]).filter(v=>v.name);
  if(!vs.length)return;
  const one=vs.map(v=>v.logo?`<span class="mlogo" title="${(v.name||'').replace(/"/g,'')}"><img src="${imgu(v.logo)}" alt="${(v.name||'').replace(/"/g,'')}" onerror="this.parentNode.innerHTML=this.alt"></span>`:`<span>${v.name}</span>`).join('');
  m.innerHTML=one+one;
}
async function initHeroShow(){
  const stage=el('#heroShow'); if(!stage)return; const d=await getData();
  let pool=d.products.filter(p=>((p.images&&p.images[0])||p.img)&&p.price&&p.featured);
  if(pool.length<3) pool=d.products.filter(p=>((p.images&&p.images[0])||p.img)&&p.price).sort((a,b)=>(b.sales||0)-(a.sales||0)).slice(0,8);
  if(!pool.length)return; let i=0;
  const show=()=>{const p=pool[i%pool.length];const im=imgu((p.images&&p.images[0])||p.img);
    const card=document.createElement('a'); card.className='hs-card'; card.href='producto.html?id='+p.id;
    card.innerHTML=`<div class="hs-badge">${svg('star',13)} Destacado</div><div class="hs-img"><img src="${im}" onerror="this.style.visibility='hidden'"></div><div class="hs-info"><div class="hs-name">${p.name}</div><div class="hs-price">${money(p.price)} <span>+ IVA</span></div></div><div class="hs-cta">Ver producto ${svg('arrow',14)}</div>`;
    const old=stage.firstElementChild; if(old){old.classList.add('out');setTimeout(()=>old.remove(),600);}
    stage.appendChild(card); setTimeout(()=>card.classList.add('in'),30);
    // dots
    let dots=stage.parentNode.querySelector('.hs-dots'); if(!dots){dots=document.createElement('div');dots.className='hs-dots';stage.parentNode.appendChild(dots);dots.innerHTML=pool.slice(0,Math.min(pool.length,6)).map((_,k)=>`<span data-k="${k}"></span>`).join('');}
    [...dots.children].forEach((x,k)=>x.classList.toggle('on',k===i%Math.min(pool.length,6)));
    i++;
  };
  show(); let timer=setInterval(show,3200);
  stage.addEventListener('mouseenter',()=>{clearInterval(timer);}); stage.addEventListener('mouseleave',()=>{timer=setInterval(show,3200);});
}
function openMobMenu(cats){ let m=el('#mobmenu');
  if(!m){m=document.createElement('div');m.id='mobmenu';m.className='mobmenu';document.body.appendChild(m);}
  m.innerHTML=`<div class="mh"><a class="brand" href="index.html"><span class="bk">${svg('bolt',18)}</span><span class="brand-tx">Larr<b>Eléctrico</b></span></a><button class="icbtn" id="mobClose">${svg('x',20)}</button></div>
  <h4>Navegación</h4><a href="catalogo.html">${svg('grid',19)}Catálogo</a><a href="favoritos.html">${svg('heart',19)}Favoritos</a><a href="comparador.html">${svg('scale',19)}Comparador</a><a href="cuenta.html">${svg('user',19)}Mi cuenta</a><a href="index.html#contacto">${svg('wa',19)}Contacto</a>
  <h4>Categorías</h4>${cats.map(c=>`<a href="catalogo.html?cat=${encodeURIComponent(c.name)}">${svg(catIcon(c.name),19)}${c.name}</a>`).join('')}`;
  el('#mobClose').onclick=closeMob; m.classList.add('on'); el('#ov').classList.add('on');
}
function closeMob(){const m=el('#mobmenu');if(m)m.classList.remove('on');if(!el('#drawer')?.classList.contains('on'))el('#ov').classList.remove('on');}
/* BOOT */
document.addEventListener('DOMContentLoaded',()=>{
  renderCart();updateWLbadge();renderCmpBar();initHeader();
  // page transitions
  document.addEventListener('click',e=>{const a=e.target.closest('a');if(!a)return;
    if(e.target.closest('button,[data-wl],[data-cmp],[data-add]'))return;
    const href=a.getAttribute('href');if(!href||a.target==='_blank'||href[0]==='#'||/^(https?:|mailto:|tel:|javascript:)/.test(href))return;
    if(a.hash && a.pathname===location.pathname)return;
    e.preventDefault();const url=a.href;document.body.classList.add('leaving');setTimeout(()=>{location.href=url;},130);});
  window.addEventListener('pageshow',ev=>{if(ev.persisted)document.body.classList.remove('leaving');});
  const oc=el('#openCart'),cc=el('#closeCart'),ov=el('#ov');
  if(oc)oc.onclick=openCart;if(cc)cc.onclick=closeCart;if(ov)ov.onclick=()=>{closeCart();closeMob();};
  const cb=el('#checkout');if(cb)cb.onclick=checkout;
  const db2=el('#drawerB');if(db2)db2.addEventListener('click',e=>{const q=e.target.closest('[data-q]');if(q){const[id,op]=q.dataset.q.split(':');const it=CART.find(c=>c.id===+id);setQty(+id,it.qty+(op==='+'?1:-1));return;}const r=e.target.closest('[data-rm]');if(r)setQty(+r.dataset.rm,0);});
  const io=new IntersectionObserver(es=>es.forEach((x,i)=>{if(x.isIntersecting){x.target.style.transitionDelay=(Math.min(x.intersectionRatio,1)*0)+'ms';x.target.classList.add('in');io.unobserve(x.target);}}),{threshold:.1});
  els('.reveal').forEach(x=>io.observe(x));
  if(el('#heroShow'))initHeroShow();
  if(document.querySelector('.marq'))initMarquee();
  if(el('#homeGrid'))getData().then(()=>wireCards(el('#homeGrid')));
  if(el('#bestGrid'))getData().then(()=>wireCards(el('#bestGrid')));
  if(el('#grid')&&el('#catlist'))initCatalog();
  if(el('#ppRoot'))initProduct();
  if(el('#authRoot'))initAuth();
  if(el('#favGrid'))initFav();
  if(el('#cmpRoot'))initCompare();
  recoverCart();
  document.addEventListener('keydown',e=>{if(e.key==='Escape')closeCart();});
});
