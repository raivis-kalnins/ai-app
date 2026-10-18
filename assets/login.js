(()=>{'use strict';
const q=s=>document.querySelector(s);
const cookie=(name,value)=>{document.cookie=name+'='+encodeURIComponent(value)+'; Path=/; SameSite=Lax; Max-Age=31536000'+(location.protocol==='https:'?'; Secure':'')};
q('#themeToggle')?.addEventListener('click',()=>{const next=document.documentElement.dataset.theme==='dark'?'light':'dark';document.documentElement.dataset.theme=next;cookie('aih_theme',next)});
q('#languageSwitch')?.addEventListener('change',e=>{const next=e.target.value||'en';cookie('aih_lang',next);const url=new URL(location.href);url.searchParams.set('lang',next);location.assign(url.toString())});
const password=q('#loginPassword'),toggle=q('#passwordToggle');
toggle?.addEventListener('click',()=>{if(!password)return;const visible=password.type==='text';password.type=visible?'password':'text';toggle.classList.toggle('is-visible',!visible);const label=!visible?(toggle.dataset.hideLabel||'Hide password'):(toggle.dataset.showLabel||'Show password');toggle.setAttribute('aria-label',label);toggle.title=label;password.focus()});
})();
