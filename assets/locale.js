(()=>{'use strict';
const qs=(s,p=document)=>p.querySelector(s),qsa=(s,p=document)=>[...p.querySelectorAll(s)];
// Image-backed language flags work consistently on Windows, where emoji flags can show letters.
qsa('[data-locale-picker]').forEach(picker=>{
  const trigger=qs('.locale-trigger',picker),menu=qs('.locale-menu',picker),native=qs('#languageSwitch',picker);
  const options=qsa('[role="option"]',menu);
  const close=(returnFocus=false)=>{menu.hidden=true;trigger.setAttribute('aria-expanded','false');if(returnFocus)trigger.focus()};
  const open=(focus=true)=>{menu.hidden=false;trigger.setAttribute('aria-expanded','true');if(focus)(options.find(x=>x.getAttribute('aria-selected')==='true')||options[0])?.focus()};
  trigger?.addEventListener('click',()=>{if(menu.hidden)open(false);else close()});
  trigger?.addEventListener('keydown',e=>{if(['ArrowDown','ArrowUp','Enter',' '].includes(e.key)){e.preventDefault();open();if(e.key==='ArrowUp')options[options.length-1]?.focus()}});
  menu?.addEventListener('keydown',e=>{
    if(e.key==='Escape'){e.preventDefault();close(true);return}
    if(e.key==='Tab'){close();return}
    if(!['ArrowDown','ArrowUp','Home','End'].includes(e.key))return;
    e.preventDefault();const current=Math.max(0,options.indexOf(document.activeElement));
    const next=e.key==='Home'?0:e.key==='End'?options.length-1:(current+(e.key==='ArrowDown'?1:-1)+options.length)%options.length;
    options[next]?.focus();
  });
  options.forEach(option=>option.addEventListener('click',()=>{const next=option.dataset.languageValue;if(!next||!native)return;native.value=next;native.dispatchEvent(new Event('change',{bubbles:true}));close()}));
  document.addEventListener('pointerdown',e=>{if(!picker.contains(e.target))close()});
});
})();
