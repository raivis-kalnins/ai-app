(()=>{'use strict';
const csrf=document.querySelector('meta[name="csrf"]')?.content||'';
const page=document.body?.dataset.page||'';
const lang=document.body?.dataset.lang||'en';
const qs=(s,p=document)=>p.querySelector(s),qsa=(s,p=document)=>[...p.querySelectorAll(s)];
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
const safeUrl=u=>{try{const x=new URL(String(u),location.href);return /^https?:$/.test(x.protocol)?x.href:'#'}catch{return '#'}};
const fmtBytes=n=>{n=Number(n)||0;if(n<1024)return n+' B';if(n<1048576)return(n/1024).toFixed(1)+' KB';if(n<1073741824)return(n/1048576).toFixed(1)+' MB';return(n/1073741824).toFixed(2)+' GB'};
const t=key=>document.body?.dataset['t'+key.split('_').map(x=>x.charAt(0).toUpperCase()+x.slice(1)).join('')]||key.replaceAll('_',' ');
const cookie=(name,value)=>{document.cookie=name+'='+encodeURIComponent(value)+'; path=/; max-age=31536000; SameSite=Lax'};
async function api(action,data=null,method='POST'){
  const opts={method,headers:{'X-CSRF-Token':csrf}};
  if(data!==null){opts.headers['Content-Type']='application/json';opts.body=JSON.stringify(data)}
  const r=await fetch('api.php?action='+encodeURIComponent(action),opts);let j;try{j=await r.json()}catch{throw new Error('Bad server response ('+r.status+')')}
  if(!r.ok||j.ok===false)throw Object.assign(new Error(j.error||'Request failed'),{data:j,status:r.status});return j;
}
function setBtn(btn,busy,text){if(!btn)return;btn.disabled=busy;if(text!==undefined){const s=btn.querySelector('span');if(s)s.textContent=text;else btn.textContent=text}}
async function ensurePuter(){if(window.AIHPuterRouter?.ensure)return window.AIHPuterRouter.ensure();if(!window.puter?.auth)throw new Error('Puter.js could not load.');if(!window.puter.auth.isSignedIn())await window.puter.auth.signIn({attempt_temp_user_creation:true});return window.puter.auth.getUser?.()}
function mediaSrc(result){if(!result)return'';if(typeof result==='string')return result;return result?.src||result?.url||result?.data?.url||''}
function appendMedia(target,result,kind='image',alt=''){
  if(!target)return;target.innerHTML='';
  if(result instanceof HTMLElement){target.appendChild(result);return}
  const src=mediaSrc(result);if(!src)throw new Error('No media returned.');
  if(kind==='video'){const v=document.createElement('video');v.controls=true;v.playsInline=true;v.src=src;target.appendChild(v)}
  else{const img=document.createElement('img');img.src=src;img.alt=alt;target.appendChild(img)}
}
async function fillPuterChatSelect(select,includeServer=false){
  if(!select||!window.AIHPuterRouter)return;const models=await window.AIHPuterRouter.listChatModels();
  const current=select.value||'auto';select.innerHTML='';
  const a=document.createElement('option');a.value='auto';a.textContent=t('auto_best_model');select.appendChild(a);
  if(includeServer){const srv=document.createElement('option');srv.value='server';srv.textContent=t('server_router');select.appendChild(srv)}
  for(const m of models){const id=String(m?.id||'');if(!id)continue;const o=document.createElement('option');o.value=id;o.textContent=(m?.provider?m.provider+' · ':'')+(m?.name||id);select.appendChild(o)}
  if([...select.options].some(o=>o.value===current))select.value=current;
}


// Global UI: light by default, dark optional; language controls hCaptcha on the next login render too.
qs('#menuBtn')?.addEventListener('click',()=>qs('.sidebar')?.classList.toggle('open'));
qs('#themeToggle')?.addEventListener('click',()=>{const next=document.documentElement.dataset.theme==='dark'?'light':'dark';document.documentElement.dataset.theme=next;cookie('aih_theme',next)});
qs('#languageSwitch')?.addEventListener('change',e=>{const next=e.target.value||'en';cookie('aih_lang',next);const u=new URL(location.href);u.searchParams.set('lang',next);location.href=u.toString()});

if(page==='chat'){
  const form=qs('#chatForm'),input=qs('#chatInput'),log=qs('#chatLog'),route=qs('#chatRoute'),send=form?.querySelector('button[type="submit"]'),modeSel=qs('#chatMode'),modelSel=qs('#chatModel');let hist=[];
  const puterEnabled=document.body.dataset.puterEnabled==='1',puterConfigured=document.body.dataset.puterModel||'auto';
  const webllmEnabled=document.body.dataset.webllmEnabled==='1',browserStatus=qs('#browserAiStatus');
  const providerOrder=String(document.body.dataset.providerOrder||'').split(',').filter(Boolean);
  const add=(role,text,meta='')=>{const d=document.createElement('div');d.className='bubble '+role;const p=document.createElement('p');p.textContent=String(text??'');d.appendChild(p);if(meta){const sm=document.createElement('small');sm.textContent=meta;d.appendChild(sm)}log.appendChild(d);log.scrollTop=log.scrollHeight;return d};
  const addMediaBubble=(result,kind,prompt,meta)=>{const d=document.createElement('div');d.className='bubble assistant media-bubble';const wrap=document.createElement('div');wrap.className='chat-media';d.appendChild(wrap);appendMedia(wrap,result,kind,prompt);if(meta){const sm=document.createElement('small');sm.textContent=meta;d.appendChild(sm)}log.appendChild(d);log.scrollTop=log.scrollHeight};
  const historyMessages=(msg,task='chat')=>[{role:'system',content:'Reply in language code '+lang+' unless the user clearly asks for another language. Task: '+task+'. For code, provide complete production-ready output. For research, distinguish facts from assumptions.'},...hist,{role:'user',content:msg}].slice(-22);
  async function askPuter(msg,task,requested){if(!puterEnabled||!window.AIHPuterRouter)throw new Error('Puter AI is not available.');route.textContent='Puter AI · '+task+'…';return window.AIHPuterRouter.chat(historyMessages(msg,task),{task,model:requested||puterConfigured||'auto'})}
  async function askBrowser(msg){if(!webllmEnabled||!window.AIHWebLLM?.isLoaded?.())throw new Error('Browser AI is not loaded.');route.textContent='Browser AI / WebLLM…';const text=await window.AIHWebLLM.chat(historyMessages(msg,'chat'));return{text,provider:'Browser AI',model:window.AIHWebLLM.model||'WebLLM'}}
  async function loadModels(){try{if(window.puter?.auth?.isSignedIn?.())await fillPuterChatSelect(modelSel,true)}catch(_){}}
  loadModels();
  qs('#puterConnect')?.addEventListener('click',async e=>{const b=e.currentTarget;b.disabled=true;try{if(!puterEnabled)throw new Error('Puter AI is disabled in Settings.');const u=await ensurePuter();await fillPuterChatSelect(modelSel,true);route.textContent=(t('connected')||'Connected')+': Puter '+(u?.username||'')+' · '+(modelSel.options.length-2)+' models'}catch(err){route.textContent=err?.message||String(err)}finally{b.disabled=false}});
  window.addEventListener('aih-webllm-status',e=>{if(browserStatus)browserStatus.textContent=e.detail||''});
  qs('#webllmLoad')?.addEventListener('click',async e=>{const b=e.currentTarget;b.disabled=true;try{if(!webllmEnabled)throw new Error('Browser AI is disabled in Settings.');if(!window.AIHWebLLM)throw new Error('Browser AI module did not load.');await window.AIHWebLLM.load();if(browserStatus)browserStatus.textContent=t('ready')+': Browser AI'}catch(err){if(browserStatus)browserStatus.textContent=(t('error')||'Error')+': '+(err?.message||String(err))}finally{b.disabled=false}});
  form?.addEventListener('submit',async e=>{e.preventDefault();const msg=input.value.trim();if(!msg)return;add('user',msg);input.value='';setBtn(send,true,t('sending'));route.textContent='AI router 2.0…';let answer=null,lastErr=null;
    const selectedMode=modeSel?.value||'auto',requestedModel=modelSel?.value||'auto';const task=selectedMode==='auto'?(window.AIHPuterRouter?.detectIntent?.(msg)||'chat'):selectedMode;
    try{
      if(task==='image'){
        route.textContent=t('generating')+' · image';
        if(puterEnabled&&requestedModel!=='server'&&window.AIHPuterRouter){try{const m=document.body.dataset.puterImageModel||'auto',r=await window.AIHPuterRouter.image(msg,{model:m});addMediaBubble(r,'image',msg,'Puter image · '+(m==='auto'?'Auto best available':m));hist.push({role:'user',content:msg},{role:'assistant',content:'[Image generated]'});route.textContent=t('ready')+' · Puter image';setBtn(send,false,t('send'));input.focus();return}catch(err){lastErr=err}}
        for(const prov of ['sdwebui','pollinations']){try{const j=await api('image',{provider:prov,prompt:msg});const d=document.createElement('div');d.className='bubble assistant media-bubble';const img=document.createElement('img');img.src='download.php?bucket=generated&inline=1&file='+encodeURIComponent(j.file);img.alt=msg;d.appendChild(img);const sm=document.createElement('small');sm.textContent=j.provider;d.appendChild(sm);log.appendChild(d);log.scrollTop=log.scrollHeight;route.textContent=t('ready')+' · '+j.provider;setBtn(send,false,t('send'));input.focus();return}catch(err){lastErr=err}}
        throw lastErr||new Error('No image provider answered.');
      }
      if(task==='video'){
        if(!puterEnabled||!window.AIHPuterRouter)throw new Error('Video generation requires Puter or another configured video provider.');const m=document.body.dataset.puterVideoModel||'auto';const r=await window.AIHPuterRouter.video(msg,{model:m});addMediaBubble(r,'video',msg,'Puter video · '+(m==='auto'?'Auto':m));hist.push({role:'user',content:msg},{role:'assistant',content:'[Video generated]'});route.textContent=t('ready')+' · Puter video';setBtn(send,false,t('send'));input.focus();return;
      }
      if(task==='compare'){
        route.textContent=t('comparing_ais')+'…';let candidates=[];
        if(puterEnabled&&requestedModel!=='server'&&window.AIHPuterRouter){try{const rows=await window.AIHPuterRouter.compare(historyMessages(msg,'compare'),{count:2});candidates.push(...rows.map(x=>({model:'Puter · '+x.model,text:x.text})))}catch(err){lastErr=err}}
        try{const j=await api('chat_compare',{message:msg,history:hist,lang});candidates.push(...(j.answers||[]).map(x=>({model:(x.provider_name||x.provider)+(x.model?' · '+x.model:''),text:x.text})))}catch(err){lastErr=err}
        const seen=new Set();candidates=candidates.filter(x=>{const k=x.model+'|'+x.text;if(seen.has(k))return false;seen.add(k);return true}).slice(0,5);
        if(!candidates.length)throw lastErr||new Error('No AI comparison provider answered.');let text='';let meta='';
        if(puterEnabled&&window.AIHPuterRouter){try{const syn=await window.AIHPuterRouter.synthesize(msg,candidates,lang);text=syn.text;meta=t('compared')+': '+candidates.map(x=>x.model).join(' · ')+' → '+syn.model}catch(_){}}
        if(!text){text=candidates.map((x,i)=>'['+(i+1)+'] '+x.model+'\n'+x.text).join('\n\n');meta=t('compared')+': '+candidates.map(x=>x.model).join(' · ')}
        answer={text,provider:meta};
      }else{
        if(requestedModel==='server'){try{const j=await api('chat',{message:msg,history:hist,lang,task});answer={text:j.text,provider:j.provider_name||j.provider||'Server AI',model:j.model||''};if(j.tried?.length)route.textContent=j.tried.map(x=>x.id+': '+x.status).join(' → ')}catch(err){lastErr=err}}
        else if(puterEnabled&&window.AIHPuterRouter){try{answer=await askPuter(msg,task,requestedModel)}catch(err){lastErr=err}}
        if(!answer){try{const j=await api('chat',{message:msg,history:hist,lang,task});answer={text:j.text,provider:j.provider_name||j.provider||'Server AI',model:j.model||''};if(j.tried?.length)route.textContent=j.tried.map(x=>x.id+': '+x.status).join(' → ')}catch(err){lastErr=err}}
        if(!answer&&webllmEnabled&&window.AIHWebLLM?.isLoaded?.()){try{answer=await askBrowser(msg)}catch(err){lastErr=err}}
      }
    }catch(err){lastErr=err}
    if(answer){const meta=answer.provider+(answer.model?' · '+answer.model:'');add('assistant',answer.text,meta);hist.push({role:'user',content:msg},{role:'assistant',content:answer.text});hist=hist.slice(-20);route.textContent=(t('ready')||'Ready')+' · '+meta}else add('assistant',(t('error')||'Error')+': '+(lastErr?.message||'No AI provider answered.'));
    setBtn(send,false,t('send'));input.focus();
  });
  input?.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();form?.requestSubmit()}});
}
if(page==='search'){
  const country=qs('#countrySelect'),sector=qs('#sectorSelect');
  try{const dn=new Intl.DisplayNames([lang],{type:'region'});qsa('option',country).forEach(o=>{if(o.value!=='ALL'){const code=o.value==='UK'?'GB':o.value;o.textContent=dn.of(code)||o.textContent}})}catch(_){ }
  const singleAll=(select)=>{select?.addEventListener('change',()=>{const selected=[...select.selectedOptions].map(o=>o.value);if(selected.includes('ALL')&&selected.length>1){[...select.options].forEach(o=>o.selected=o.value==='ALL')}})};
  singleAll(country);singleAll(sector);
  const detailsFor=r=>{
    const items=[];
    [['brand',r.brand||r.make],['model',r.model],['year',r.year],['mileage',r.mileage],['area',r.area],['location',r.location],['company',r.company],['salary',r.salary],['condition',r.condition]].forEach(([k,v])=>{if(v)items.push(String(v))});
    return items.join(' · ');
  };
  const sectorLabel=code=>{const opt=[...sector?.options||[]].find(o=>o.value===code);return opt?.textContent||code||''};
  qs('#searchForm')?.addEventListener('submit',async e=>{e.preventDefault();const f=new FormData(e.currentTarget),btn=e.currentTarget.querySelector('button[type="submit"]');let countries=f.getAll('countries'),sectors=f.getAll('sectors');if(!countries.length||countries.includes('ALL'))countries=['ALL'];if(!sectors.length||sectors.includes('ALL'))sectors=['ALL'];const data={q:f.get('q')||'',brand:f.get('brand')||'',model:f.get('model')||'',keywords:f.get('keywords')||'',location:f.get('location')||'',condition:f.get('condition')||'',price_min:f.get('price_min')||'',price_max:f.get('price_max')||'',year_min:f.get('year_min')||'',countries,sectors,lang};setBtn(btn,true,'⌕ '+t('searching'));
    try{const j=await api('search',data),out=qs('#searchOutput'),body=qs('#searchRows');out?.classList.remove('hidden');const engines=(j.engines||[]).join(', '),sectorText=sectors.includes('ALL')?t('all_sectors'):sectors.map(sectorLabel).join(', ');qs('#searchSummary').textContent=(j.count||0)+' '+t('results').toLowerCase()+' · '+sectorText+' · '+(j.queries?.length||0)+' queries'+(engines?' · '+engines:'')+(j.cached?' · cache':'')+(j.warning?' · '+j.warning:'');body.innerHTML='';
      (j.results||[]).forEach((r,i)=>{const tr=document.createElement('tr'),href=safeUrl(r.url),detail=detailsFor(r),sectorName=sectorLabel(r.sector||'ALL');tr.innerHTML='<td>'+(i+1)+'</td><td><strong>'+esc(r.title||'')+'</strong><br><small>'+esc(r.snippet||'')+'</small></td><td>'+esc(((r.price||'')+' '+(r.currency||'')).trim())+'</td><td><small>'+esc(detail)+'</small></td><td>'+esc(r.country||'')+'</td><td>'+esc(sectorName)+'</td><td>'+(href==='#'?'':('<a href="'+esc(href)+'" target="_blank" rel="noopener noreferrer">'+esc(t('source'))+' ↗</a>'))+'</td>';body.appendChild(tr)});
      const ai=qs('#aiSearchSummary'),notes=(j.research_notes||[]);let summaryText=j.ai_summary?.text||'',summaryProvider=j.ai_summary?.provider||'';
      if(!summaryText&&document.body.dataset.puterEnabled==='1'&&window.puter?.ai?.chat&&(j.results||[]).length){try{await ensurePuter();const src=(j.results||[]).slice(0,24).map((r,i)=>({i:i+1,title:r.title,snippet:r.snippet,url:r.url,country:r.country,sector:r.sector,price:r.price,currency:r.currency,year:r.year,mileage:r.mileage,area:r.area,location:r.location,company:r.company,salary:r.salary}));const prompt='Summarize and compare ONLY these sourced search results. Do not invent facts. Mention uncertainty and useful differences. Reply in language code '+lang+'.\nQuery: '+String(data.q||'')+'\nSectors: '+JSON.stringify(data.sectors)+'\nSources: '+JSON.stringify(src);const pr=window.AIHPuterRouter?await window.AIHPuterRouter.chat([{role:'user',content:prompt}],{task:'research',model:document.body.dataset.puterModel||'auto'}):{text:''};summaryText=String(pr?.text||'').trim();summaryProvider=pr?.model?'Puter AI · '+pr.model:'Puter AI';if(summaryText)api('report_client_summary',{report_id:j.report_id,text:summaryText,provider:summaryProvider}).catch(()=>{})}catch(_){} }
      const blocks=[];if(summaryText)blocks.push('<div><b>AI summary'+(summaryProvider?' · '+esc(summaryProvider):'')+'</b><p>'+esc(summaryText)+'</p></div>');notes.forEach(n=>{if(n?.text)blocks.push('<div class="research-note"><b>'+esc(n.provider||'AI research')+'</b><p>'+esc(n.text)+'</p></div>')});if(blocks.length){ai.classList.remove('hidden');ai.innerHTML=blocks.join('')}else ai.classList.add('hidden');
      qs('#reportLinks').innerHTML='<a class="button ghost" href="report.php?id='+encodeURIComponent(j.report_id)+'&format=html" target="_blank" rel="noopener">'+esc(t('report_html'))+'</a><a class="button ghost" href="report.php?id='+encodeURIComponent(j.report_id)+'&format=pdf">'+esc(t('report_pdf'))+'</a><a class="button ghost" href="report.php?id='+encodeURIComponent(j.report_id)+'&format=json">'+esc(t('report_json'))+'</a>';out?.scrollIntoView({behavior:'smooth',block:'start'});
    }catch(err){alert((t('error')||'Error')+': '+err.message)}finally{setBtn(btn,false,'⌕ '+t('search_compare'))}
  });
}
if(page==='images'){
  const type=qs('#mediaType'),provider=qs('#mediaProvider'),model=qs('#mediaModel'),form=qs('#mediaForm'),out=qs('#mediaOutput');
  async function loadImageModels(){if(!window.AIHPuterRouter||provider?.value!=='puter'||type?.value!=='image')return;try{const rows=await window.AIHPuterRouter.listImageModels();const current=document.body.dataset.puterImageModel||model.value||'auto';window.AIHPuterRouter.fillSelect(model,rows,t('auto_best_model'));if([...model.options].some(o=>o.value===current))model.value=current}catch(_){}}
  const sync=()=>{const video=type?.value==='video';qsa('option',provider).forEach(o=>{if(o.value!=='puter')o.disabled=video});if(video)provider.value='puter';if(provider?.value!=='puter'){model.disabled=true;model.innerHTML='<option value="auto">'+esc(t('provider_default'))+'</option>'}else{model.disabled=false;if(video){const v=document.body.dataset.puterVideoModel||'auto';model.innerHTML='<option value="auto">'+esc(t('auto_best_model'))+'</option>';if(v!=='auto'){const o=document.createElement('option');o.value=v;o.textContent=v;model.appendChild(o);model.value=v}}else loadImageModels()}};
  sync();type?.addEventListener('change',sync);provider?.addEventListener('change',sync);
  qs('#mediaPuterConnect')?.addEventListener('click',async e=>{const b=e.currentTarget;b.disabled=true;try{const u=await ensurePuter();await loadImageModels();out.innerHTML='<div class="alert success">'+esc(t('connected'))+': Puter '+esc(u?.username||'')+(type?.value==='image'?' · '+Math.max(0,model.options.length-1)+' image models':'')+'</div>'}catch(err){out.innerHTML='<div class="alert error">'+esc(err.message)+'</div>'}finally{b.disabled=false}});
  form?.addEventListener('submit',async e=>{e.preventDefault();const f=new FormData(form),mediaType=f.get('media_type'),prov=f.get('provider'),selectedModel=String(f.get('media_model')||'auto'),prompt=String(f.get('prompt')||'').trim(),btn=form.querySelector('button[type="submit"]');if(!prompt)return;setBtn(btn,true,t('generating'));out.innerHTML='<p class="muted">'+esc(t('generating'))+'…</p>';
    try{if(prov==='puter'){if(!window.AIHPuterRouter)throw new Error('Puter media router is unavailable.');const result=mediaType==='video'?await window.AIHPuterRouter.video(prompt,{model:selectedModel}):await window.AIHPuterRouter.image(prompt,{model:selectedModel});appendMedia(out,result,mediaType,prompt);const cap=document.createElement('p');cap.className='media-caption';cap.textContent='Puter AI · '+(selectedModel==='auto'?t('auto_best_model'):selectedModel);out.appendChild(cap)}else{if(mediaType==='video')throw new Error('Video generation currently uses Puter AI.');const j=await api('image',{provider:prov,prompt});out.innerHTML='<p><b>'+esc(j.provider)+'</b></p><img src="download.php?bucket=generated&inline=1&file='+encodeURIComponent(j.file)+'" alt=""><p><a class="button ghost" href="download.php?bucket=generated&file='+encodeURIComponent(j.file)+'">Download</a></p>'}}
    catch(err){out.innerHTML='<div class="alert error">'+esc((t('error')||'Error')+': '+err.message)+'</div>'}finally{setBtn(btn,false,t('generate'))}
  });
}
if(page==='generators'){
  const modelSel=qs('#builderModel');
  (async()=>{try{if(window.puter?.auth?.isSignedIn?.())await fillPuterChatSelect(modelSel,true)}catch(_){}})();
  qs('#generatorForm')?.addEventListener('submit',async e=>{e.preventDefault();const form=e.currentTarget,f=new FormData(form),data={type:f.get('type'),name:f.get('name'),description:f.get('description'),use_ai:f.get('use_ai')==='on'},selectedModel=String(f.get('ai_model')||'auto'),btn=form.querySelector('button[type="submit"]'),out=qs('#generatorOutput');setBtn(btn,true,t('building'));out.innerHTML='<p class="muted">'+esc(t('building'))+'…</p>';
    try{let j=null;
      if(data.use_ai&&selectedModel!=='server'&&document.body.dataset.puterEnabled==='1'&&window.AIHPuterRouter){try{await ensurePuter();if(modelSel.options.length<=2)await fillPuterChatSelect(modelSel,true);const sys='You are a senior full-stack and WordPress developer. Output ONLY project files in this exact format: ### FILE: relative/path\\nfile content. No markdown fences or commentary. Create a secure, production-quality '+data.type+' project. Include complete files, validation, escaping, accessibility and performance best practices.';const usr='Project: '+data.name+'\\nRequirements: '+(data.description||'Create a polished fast starter application.');const r=await window.AIHPuterRouter.chat([{role:'system',content:sys},{role:'user',content:usr}],{task:'code',model:selectedModel});j=await api('package_project',{type:data.type,name:data.name,description:data.description,ai_text:r.text,provider:'Puter AI · '+r.model})}catch(_){j=null}}
      if(!j)j=await api('generate_project',data);
      out.innerHTML='<div class="alert success">'+esc(t('ready'))+' · '+esc(j.provider)+': '+esc(j.file)+'</div><p><a class="button ghost" href="download.php?bucket=generated&file='+encodeURIComponent(j.file)+'">Download ZIP</a></p><small>'+esc((j.files||[]).join(', '))+'</small>';
    }catch(err){out.innerHTML='<div class="alert error">'+esc((t('error')||'Error')+': '+err.message)+'</div>'}finally{setBtn(btn,false,t('build_zip'))}
  });
}
if(page==='files'){
  async function load(){try{const j=await api('files',null,'GET');qs('#storageSummary').textContent=fmtBytes(j.stats.bytes)+' · '+j.stats.files+' files · 48 h';const b=qs('#fileRows');b.innerHTML='';if(!j.files.length){b.innerHTML='<tr><td colspan="5" class="empty">'+esc(t('no_results'))+'</td></tr>';return}(j.files||[]).forEach(x=>{const tr=document.createElement('tr'),dl='download.php?bucket='+encodeURIComponent(x.bucket)+'&file='+encodeURIComponent(x.file);tr.innerHTML='<td>'+esc(x.file)+'</td><td>'+esc(x.bucket)+'</td><td>'+esc(fmtBytes(x.bytes))+'</td><td>'+esc(new Date(x.expires*1000).toLocaleString())+'</td><td><a class="button ghost compact" href="'+dl+'">↓</a> <button class="button danger compact file-delete" type="button" data-bucket="'+esc(x.bucket)+'" data-file="'+esc(x.file)+'">×</button></td>';b.appendChild(tr)});qsa('.file-delete',b).forEach(btn=>btn.addEventListener('click',async()=>{if(!confirm('Delete '+btn.dataset.file+'?'))return;await api('delete',{bucket:btn.dataset.bucket,file:btn.dataset.file});load()}))}catch(err){qs('#storageSummary').textContent=err.message}}
  qs('#refreshFiles')?.addEventListener('click',load);qs('#cleanupBtn')?.addEventListener('click',async()=>{try{await api('cleanup',{});load()}catch(err){alert(err.message)}});
  qs('#uploadForm')?.addEventListener('submit',e=>{e.preventDefault();const file=qs('#uploadFile')?.files?.[0];if(!file)return;if(file.size>1073741824){qs('#uploadStatus').textContent='Maximum 1 GB';return}const xhr=new XMLHttpRequest();xhr.open('POST','api.php?action=upload');xhr.setRequestHeader('X-CSRF-Token',csrf);xhr.upload.onprogress=x=>{if(x.lengthComputable)qs('#uploadProgress').style.width=Math.round(x.loaded/x.total*100)+'%'};xhr.onload=()=>{try{const j=JSON.parse(xhr.responseText);qs('#uploadStatus').textContent=j.ok?t('ready'):(j.error||t('error'));if(j.ok){e.currentTarget.reset();load()}}catch{qs('#uploadStatus').textContent='Upload error'}};xhr.onerror=()=>qs('#uploadStatus').textContent='Upload error';const fd=new FormData();fd.append('file',file);xhr.send(fd)});load();
}

if(page==='settings'){
  const form=qs('#settingsForm'),status=qs('#settingsStatus');
  async function populatePuterLists(){if(!window.AIHPuterRouter)return;const chats=await window.AIHPuterRouter.listChatModels(),images=await window.AIHPuterRouter.listImageModels();const dl=qs('#puterChatModels'),idl=qs('#puterImageModels');if(dl){dl.innerHTML='';chats.forEach(m=>{const id=m?.id||'';if(id){const o=document.createElement('option');o.value=id;o.label=(m?.provider?m.provider+' · ':'')+(m?.name||id);dl.appendChild(o)}})}if(idl){idl.innerHTML='';images.forEach(m=>{const id=m?.id||'';if(id){const o=document.createElement('option');o.value=id;o.label=(m?.provider?m.provider+' · ':'')+(m?.name||id);idl.appendChild(o)}})}return{chats:chats.length,images:images.length}}
  qsa('.provider-test').forEach(btn=>btn.addEventListener('click',async()=>{const row=btn.closest('.setting-row'),out=row?.querySelector('.provider-test-status');btn.disabled=true;if(out)out.textContent=t('testing')+'…';try{const j=await api('provider_test',{provider:btn.dataset.providerTest});if(out)out.textContent=(t('ready')||'Ready')+(j.text?' · '+j.text:'')}catch(err){if(out)out.textContent=(t('error')||'Error')+': '+err.message}finally{btn.disabled=false}}));
  qsa('[data-client-connect="puter"]').forEach(btn=>btn.addEventListener('click',async()=>{const row=btn.closest('.setting-row'),out=row?.querySelector('.provider-test-status');btn.disabled=true;try{const u=await ensurePuter(),counts=await populatePuterLists();if(out)out.textContent=t('connected')+': '+(u?.username||'Puter')+' · '+counts.chats+' chat / '+counts.images+' image models'}catch(err){if(out)out.textContent=t('error')+': '+err.message}finally{btn.disabled=false}}));
  qsa('[data-client-models="puter"]').forEach(btn=>btn.addEventListener('click',async()=>{const row=btn.closest('.setting-row'),out=row?.querySelector('.provider-test-status');btn.disabled=true;try{await ensurePuter();const counts=await populatePuterLists();if(out)out.textContent=counts.chats+' chat · '+counts.images+' image models loaded'}catch(err){if(out)out.textContent=t('error')+': '+err.message}finally{btn.disabled=false}}));
  qsa('[data-client-connect="webllm"]').forEach(btn=>btn.addEventListener('click',async()=>{const row=btn.closest('.setting-row'),out=row?.querySelector('.provider-test-status');btn.disabled=true;try{if(!window.AIHWebLLM)throw new Error('Browser AI module did not load.');await window.AIHWebLLM.load();if(out)out.textContent=t('ready')+': Browser AI'}catch(err){if(out)out.textContent=t('error')+': '+err.message}finally{btn.disabled=false}}));
  qsa('.model-use').forEach(btn=>btn.addEventListener('click',()=>{const provider=btn.dataset.provider||'ollama_local',model=btn.dataset.model||'',input=form?.querySelector('[name="model_'+CSS.escape(provider)+'"]'),enabled=form?.querySelector('[name="enabled_'+CSS.escape(provider)+'"]');if(input)input.value=model;if(enabled)enabled.checked=true;const command=btn.dataset.command||'';status.textContent=model+(command?' · '+command:'')}));
  form?.addEventListener('submit',async e=>{e.preventDefault();const f=new FormData(form),data={};qsa('.setting-row',form).forEach(row=>{const id=row.dataset.provider;data['enabled_'+id]=!!form.querySelector('[name="enabled_'+CSS.escape(id)+'"]')?.checked;data['model_'+id]=form.querySelector('[name="model_'+CSS.escape(id)+'"]')?.value||'';const key=form.querySelector('[name="key_'+CSS.escape(id)+'"]');if(key&&!key.disabled)data['key_'+id]=key.value||''});data.provider_order=String(f.get('provider_order')||'').split(',').map(x=>x.trim()).filter(Boolean);['ollama_local_url','lmstudio_local_url','llamacpp_local_url','localai_local_url','custom_openai_url','searxng_url','ollama_web_key','image_provider','puter_image_model','puter_video_model','pollinations_model','sdwebui_url','system_prompt','ui_language','ui_theme','hcaptcha_sitekey','hcaptcha_secret','new_password'].forEach(k=>data[k]=f.get(k)||'');data.ai_enrich=f.get('ai_enrich')==='on';data.hcaptcha_enabled=f.get('hcaptcha_enabled')==='on';const btn=form.querySelector('button[type="submit"]');setBtn(btn,true,t('saving'));status.textContent=t('saving')+'…';try{await api('settings',data);status.textContent=t('saved');if(data.ui_language)cookie('aih_lang',data.ui_language);if(data.ui_theme){cookie('aih_theme',data.ui_theme);document.documentElement.dataset.theme=data.ui_theme}}catch(err){status.textContent=t('error')+': '+err.message}finally{setBtn(btn,false,t('save_settings'))}});
}
})();
