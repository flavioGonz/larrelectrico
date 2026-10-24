<?php
/* Puerta PWA: en móvil (navegador, no instalada) bloquea el admin y obliga a instalar.
   Se oculta sola si corre en modo standalone (instalada) o en escritorio. */
function pwa_gate(){ ?>
<div id="pwaGate" style="display:none">
 <div class="pg-aura"><b></b><b></b></div>
 <div class="pg-card">
  <div class="pg-logo"><span class="pg-b"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7z"/></svg></span>Larr <i>Admin</i></div>
  <h2 class="pg-h">Instalá la app para continuar</h2>
  <p class="pg-p">El panel está pensado como aplicación. Instalalo en tu teléfono para una experiencia más rápida, a pantalla completa y con acceso directo.</p>

  <div class="pg-pane" id="pgAndroid">
   <button type="button" class="pg-go" id="pgInstall">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M8 11l4 4 4-4M4 21h16"/></svg>
    Instalar la app
   </button>
   <div class="pg-manual" id="pgAndroidManual" style="display:none">
    <div class="pg-step"><span class="pg-n">1</span>Abrí el menú <b>⋮</b> del navegador</div>
    <div class="pg-step"><span class="pg-n">2</span>Elegí <b>“Instalar app”</b> o <b>“Agregar a pantalla de inicio”</b></div>
   </div>
  </div>

  <div class="pg-pane" id="pgIos" style="display:none">
   <div class="pg-step"><span class="pg-n">1</span>Tocá el botón <b>Compartir</b>
    <svg class="pg-sh" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3M8 7l4-4 4 4M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"/></svg>
    en la barra de Safari</div>
   <div class="pg-step"><span class="pg-n">2</span>Deslizá y tocá <b>“Agregar a pantalla de inicio”</b></div>
   <div class="pg-step"><span class="pg-n">3</span>Abrí <b>Larr Admin</b> desde tu pantalla de inicio</div>
   <p class="pg-note" id="pgIosChrome" style="display:none">En iPhone, abrí este sitio en <b>Safari</b> para poder instalarlo.</p>
  </div>

  <a href="#" class="pg-skip" id="pgSkip">Seguir en el navegador por ahora</a>
 </div>
</div>
<style>
#pwaGate{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:22px;
 background:#0a1730;font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif;color:#13151a;overflow:hidden}
html.pwa-lock,body.pwa-lock{overflow:hidden!important}
#pwaGate .pg-aura{position:absolute;inset:0;overflow:hidden;pointer-events:none}
#pwaGate .pg-aura b{position:absolute;border-radius:50%;filter:blur(70px);opacity:.5}
#pwaGate .pg-aura b:nth-child(1){width:420px;height:420px;background:#0a84ff;top:-140px;left:-110px}
#pwaGate .pg-aura b:nth-child(2){width:360px;height:360px;background:#0f7a44;bottom:-150px;right:-90px;opacity:.32}
#pwaGate .pg-card{position:relative;z-index:2;width:420px;max-width:100%;background:#fff;border-radius:24px;padding:34px 28px;
 box-shadow:0 40px 110px -30px rgba(0,0,0,.6);animation:pgRise .5s cubic-bezier(.16,1,.3,1) both;text-align:center}
@keyframes pgRise{from{opacity:0;transform:translateY(20px) scale(.98)}to{opacity:1;transform:none}}
#pwaGate .pg-logo{display:inline-flex;align-items:center;gap:10px;font-weight:800;font-size:19px;letter-spacing:-.02em}
#pwaGate .pg-logo i{font-style:normal;color:#0071e3}
#pwaGate .pg-b{width:36px;height:36px;border-radius:11px;background:linear-gradient(135deg,#27a0ff,#0071e3);display:grid;place-items:center;box-shadow:0 10px 24px -6px rgba(10,132,255,.7)}
#pwaGate .pg-b svg{width:20px;height:20px}
#pwaGate .pg-h{font-size:21px;font-weight:800;letter-spacing:-.02em;margin:22px 0 8px}
#pwaGate .pg-p{font-size:14px;color:#5f6470;line-height:1.55;margin-bottom:22px}
#pwaGate .pg-go{width:100%;height:54px;border:none;border-radius:14px;cursor:pointer;background:linear-gradient(135deg,#0a84ff,#0071e3);
 color:#fff;font-size:15.5px;font-weight:700;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:10px;
 box-shadow:0 14px 30px -10px rgba(0,113,227,.6);transition:.2s}
#pwaGate .pg-go:hover{transform:translateY(-1px)}#pwaGate .pg-go svg{width:19px;height:19px}
#pwaGate .pg-manual,#pwaGate .pg-pane .pg-step{text-align:left}
#pwaGate .pg-step{display:flex;align-items:center;gap:11px;font-size:13.5px;color:#3a3f4a;line-height:1.4;background:#f6f7fb;border:1px solid #e7e8ee;border-radius:13px;padding:12px 14px;margin-bottom:10px}
#pwaGate .pg-step b{font-weight:700;color:#13151a}
#pwaGate .pg-n{flex-shrink:0;width:24px;height:24px;border-radius:50%;background:#0071e3;color:#fff;font-size:12.5px;font-weight:700;display:grid;place-items:center}
#pwaGate .pg-sh{width:17px;height:17px;color:#0071e3;flex-shrink:0;display:inline-block;vertical-align:middle}
#pwaGate .pg-note{font-size:12.5px;color:#8a909c;margin-top:4px;line-height:1.5}
#pwaGate .pg-skip{display:inline-block;margin-top:20px;font-size:12.5px;color:#8a909c;text-decoration:none}
#pwaGate .pg-skip:hover{color:#5f6470;text-decoration:underline}
</style>
<script>
(function(){
 var gate=document.getElementById('pwaGate'); if(!gate) return;
 var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone===true || document.referrer.indexOf('android-app://')===0;
 var ua=navigator.userAgent||'';
 var coarse = window.matchMedia && window.matchMedia('(pointer:coarse)').matches;
 var small = window.matchMedia && window.matchMedia('(max-width:900px)').matches;
 var mobile = /Android|iPhone|iPad|iPod|Mobile|Silk/i.test(ua) || (coarse && small);
 var ios=/iPhone|iPad|iPod/i.test(ua) || (navigator.platform==='MacIntel' && navigator.maxTouchPoints>1);
 var iosSafari = ios && /Safari/i.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS/i.test(ua);
 try{ if(sessionStorage.getItem('pwa_skip')==='1'){ gate.remove(); return; } }catch(e){}
 if(standalone || !mobile){ gate.remove(); return; }
 // mostrar puerta
 document.documentElement.classList.add('pwa-lock'); document.body.classList.add('pwa-lock');
 gate.style.display='flex';
 var android=document.getElementById('pgAndroid'), iosP=document.getElementById('pgIos');
 if(ios){ android.style.display='none'; iosP.style.display='block'; if(!iosSafari){ var c=document.getElementById('pgIosChrome'); if(c)c.style.display='block'; } }
 // Android / Chrome: prompt de instalación nativo
 var deferred=null, btn=document.getElementById('pgInstall'), man=document.getElementById('pgAndroidManual');
 window.addEventListener('beforeinstallprompt',function(e){ e.preventDefault(); deferred=e; if(btn)btn.style.display='flex'; if(man)man.style.display='none'; });
 if(btn){ btn.addEventListener('click',function(){
   if(deferred){ deferred.prompt(); deferred.userChoice.then(function(c){ if(c&&c.outcome==='accepted'){ /* appinstalled cerrará */ } }); }
   else { if(man)man.style.display='block'; btn.style.display='none'; }
 }); }
 // si no hay prompt tras 1.4s (navegador sin soporte), mostrar pasos manuales
 if(!ios){ setTimeout(function(){ if(!deferred && man){ man.style.display='block'; } },1400); }
 window.addEventListener('appinstalled',function(){ try{ gate.remove(); }catch(e){} document.documentElement.classList.remove('pwa-lock'); document.body.classList.remove('pwa-lock'); });
 var skip=document.getElementById('pgSkip');
 if(skip){ skip.addEventListener('click',function(e){ e.preventDefault(); try{ sessionStorage.setItem('pwa_skip','1'); }catch(x){} gate.remove(); document.documentElement.classList.remove('pwa-lock'); document.body.classList.remove('pwa-lock'); }); }
})();
</script>
<?php }
