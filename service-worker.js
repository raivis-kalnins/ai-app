const CACHE='aih63-static-v073-private-owner-ready';
const STATIC=['./assets/app.css','./assets/app.js','./assets/login.js','./assets/locale.js','./assets/pwa.js','./assets/browser-ai.js','./assets/icons/favicon.svg', './assets/flags/en.svg', './assets/flags/lv.svg', './assets/flags/lt.svg', './assets/flags/et.svg', './assets/flags/sv.svg', './assets/flags/no.svg', './assets/flags/da.svg', './assets/flags/fi.svg', './assets/flags/is.svg', './assets/flags/pl.svg', './assets/flags/ru.svg', './assets/flags/uk.svg', './assets/flags/es.svg', './assets/flags/fr.svg', './assets/flags/de.svg','./assets/icons/icon-192.png','./assets/icons/icon-512.png','./assets/icons/apple-touch-icon.png'];
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(STATIC)).catch(()=>{}));self.skipWaiting();});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))));self.clients.claim();});
self.addEventListener('fetch',e=>{
  const u=new URL(e.request.url);
  if(e.request.method!=='GET'||u.origin!==location.origin)return;
  if(e.request.mode==='navigate')return;
  const base=new URL('./',self.registration.scope).pathname;
  const isPublicAsset=u.pathname.startsWith(base+'assets/')||u.pathname===base+'manifest.webmanifest';
  if(isPublicAsset&&['style','script','image','manifest'].includes(e.request.destination)){
    e.respondWith(caches.match(e.request).then(r=>r||fetch(e.request).then(resp=>{if(!resp.ok||/no-store|private/i.test(resp.headers.get('Cache-Control')||''))return resp;const copy=resp.clone();caches.open(CACHE).then(c=>c.put(e.request,copy)).catch(()=>{});return resp;})));
  }
});
