<?php
/* Registro del service worker con actualización forzada:
   cada deploy cambia el sello del SW -> el nuevo SW se activa (skipWaiting) ->
   controllerchange -> la PWA/tab se recarga sola con la versión nueva. */
function sw_reg(){ ?>
<script>
(function(){
 if(!('serviceWorker' in navigator)) return;
 var refreshing=false;
 var hadController=!!navigator.serviceWorker.controller;
 navigator.serviceWorker.addEventListener('controllerchange',function(){
   if(refreshing || !hadController) return; refreshing=true; location.reload();
 });
 navigator.serviceWorker.register('sw.js').then(function(reg){
   reg.update().catch(function(){});
   function check(){ reg.update().catch(function(){}); }
   setInterval(check, 60000);
   document.addEventListener('visibilitychange',function(){ if(document.visibilityState==='visible') check(); });
   reg.addEventListener('updatefound',function(){
     var nw=reg.installing; if(!nw) return;
     nw.addEventListener('statechange',function(){
       if(nw.state==='installed' && navigator.serviceWorker.controller){ try{ nw.postMessage({type:'SKIP_WAITING'}); }catch(e){} }
     });
   });
 }).catch(function(){});
})();
</script>
<?php }
