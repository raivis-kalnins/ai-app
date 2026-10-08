(()=>{'use strict';
const cache={chatModels:null,imageModels:null};
const clean=s=>String(s||'').toLowerCase();
const textFrom=r=>{
  if(!r)return'';
  if(typeof r==='string')return r;
  const c=r?.message?.content??r?.text??r?.content??'';
  if(typeof c==='string')return c;
  if(Array.isArray(c))return c.map(x=>typeof x==='string'?x:(x?.text||'')).join('\n');
  return String(c||'');
};
async function ensure(){
  if(!window.puter?.auth)throw new Error('Puter.js could not load.');
  if(!window.puter.auth.isSignedIn())await window.puter.auth.signIn({attempt_temp_user_creation:true});
  return window.puter.auth.getUser?.();
}
async function listChatModels(force=false){
  await ensure();
  if(cache.chatModels&&!force)return cache.chatModels;
  let rows=[];
  try{rows=await window.puter.ai.listModels();}catch(_){rows=[];}
  cache.chatModels=Array.isArray(rows)?rows:[];
  return cache.chatModels;
}
async function listImageModels(force=false){
  await ensure();
  if(cache.imageModels&&!force)return cache.imageModels;
  let rows=[];
  try{const r=await window.puter.drivers.call('puter-image-generation','models',{});rows=r?.result||r||[];}catch(_){rows=[];}
  if(!Array.isArray(rows)||!rows.length)rows=[
    {id:'gemini-3.1-flash-image',provider:'gemini',name:'Gemini 3.1 Flash Image'},
    {id:'gemini-3.1-flash-lite-image',provider:'gemini',name:'Gemini 3.1 Flash Lite Image'},
    {id:'gemini-3-pro-image',provider:'gemini',name:'Gemini 3 Pro Image'},
    {id:'grok-imagine-image-2.0',provider:'xai',name:'Grok Imagine Image 2.0'},
    {id:'grok-imagine-image',provider:'xai',name:'Grok Imagine Image'}
  ];
  cache.imageModels=rows;
  return cache.imageModels;
}
function detectIntent(input){
  const s=clean(input);
  const image=/(generate|create|make|draw|render|design|produce|show|izveido|uzģenerē|uzzīmē|attēl|bilde|fotogr|imagen|image|picture|photo|illustration|bild|grafik|obraz|zdję|rysuj|нарис|изображ|зображ|paveiksl|pilt|kuva).{0,45}(image|picture|photo|illustration|logo|banner|attēl|bild|foto|imagen|obraz|zdję|изображ|зображ|paveiksl|kuva)|\b(image|picture|photo|illustration|logo|banner)\b.{0,35}(generate|create|draw|render)/i.test(s);
  if(image)return'image';
  const video=/(generate|create|make|render|izveido|uzģenerē|video|film|clip).{0,40}(video|film|clip|animāc|animation)|\bvideo\b/i.test(s);
  if(video)return'video';
  const code=/(wordpress|plugin|theme|php|javascript|typescript|react|next\.?js|svelte|alpine|css|html|sql|python|node\.?js|supabase|api|debug|refactor|code|kods|spraudn|spraudni|spraudņa|programm|kood|kod|код|код)/i.test(s);
  if(code)return'code';
  const compare=/(compare|comparison|vs\.?|versus|salīdzin|salidzin|palygin|võrdle|jämför|sammenlign|porówn|сравн|порівн|comparar|comparer|vergleichen)/i.test(s);
  if(compare)return'compare';
  const research=/(research|latest|current|today|find|search|sources|evidence|prices|market|izpēt|meklē|atrast|pētīj|recherche|buscar|suche|искать|найти|дослід|šaltin|allik|lähte)/i.test(s);
  if(research)return'research';
  return'chat';
}
function modelId(m){return String(m?.id||m?.model||m?.name||'');}
function providerId(m){return clean(m?.provider||'');}
function costPenalty(m){
  const c=m?.cost||{};let x=0;
  for(const k of ['input','output']){const v=Number(c?.tokens?.[k]??c?.[k]??0);if(Number.isFinite(v)&&v>0)x+=Math.min(25,v*4);}
  return x;
}
function scoreModel(m,task){
  const id=clean(modelId(m)),p=providerId(m);let s=0;
  if(!id)return-9999;
  if(/image|video|embed|tts|speech|ocr/.test(id))return-500;
  const rules={
    chat:[[/gpt-5\.6/,120],[/claude-(opus|sonnet)/,116],[/gemini-3\.(1|2|3|8).*(pro|flash)/,108],[/grok/,92],[/deepseek/,88],[/qwen/,84],[/mistral/,80]],
    code:[[/claude-(opus|sonnet)/,126],[/gpt-5\.6/,122],[/deepseek.*(coder|v3|r1)/,116],[/qwen.*(coder|3)/,112],[/gemini-3\..*(pro|flash)/,108],[/codestral/,108],[/mistral/,88]],
    research:[[/gpt-5\.6/,122],[/claude-(opus|sonnet)/,120],[/gemini-3\..*(pro|flash)/,118],[/grok/,108],[/deepseek/,100],[/qwen/,92]],
    compare:[[/gpt-5\.6/,120],[/claude-(opus|sonnet)/,120],[/gemini-3\..*(pro|flash)/,116],[/grok/,106],[/deepseek/,100]],
  };
  for(const [re,v] of (rules[task]||rules.chat))if(re.test(id)){s=Math.max(s,v);break;}
  if(!s)s=55;
  if(/nano|mini|small|1b|2b|3b/.test(id))s-=18;
  if(/latest/.test(id))s+=3;
  if(['openai','claude','anthropic','google','gemini','xai','mistral','openrouter','infron'].includes(p))s+=3;
  const ctx=Number(m?.context||0);if(ctx>=100000)s+=5;else if(ctx>=32000)s+=2;
  s-=costPenalty(m);
  return s;
}
async function pickModel(task='chat',requested='auto',exclude=[]){
  if(requested&&requested!=='auto'&&requested!=='server')return requested;
  const models=await listChatModels();
  const ex=new Set(exclude.map(clean));
  const ranked=models.filter(m=>!ex.has(clean(modelId(m)))).map(m=>({m,score:scoreModel(m,task)})).sort((a,b)=>b.score-a.score);
  if(ranked[0]?.score>-100)return modelId(ranked[0].m);
  return 'gpt-5.6-luna';
}
async function pickCompareModels(task='compare',count=3){
  const models=await listChatModels();
  const ranked=models.map(m=>({m,score:scoreModel(m,task)})).sort((a,b)=>b.score-a.score);
  const out=[],providers=new Set();
  for(const x of ranked){
    if(x.score<-100)continue;const id=modelId(x.m),p=providerId(x.m)||id.split(':')[0];
    if(providers.has(p))continue;out.push(id);providers.add(p);if(out.length>=count)break;
  }
  return out.length?out:['gpt-5.6-luna'];
}
async function chat(messages,{task='chat',model='auto'}={}){
  await ensure();
  const chosen=await pickModel(task,model);
  const opts={model:chosen,normalize:true};
  // Puter exposes web_search for compatible chat models. Use it for research when possible,
  // then retry normally if the selected model/provider does not support the tool.
  if(task==='research'&&/(?:^|[/:])(gpt-5\.?6|gpt-5|o3|o4)|openai/i.test(chosen))opts.tools=[{type:'web_search'}];
  let r;
  try{r=await window.puter.ai.chat(messages,opts)}catch(err){if(opts.tools){delete opts.tools;r=await window.puter.ai.chat(messages,opts)}else throw err}
  const text=textFrom(r).trim();if(!text)throw new Error('Puter AI returned an empty response.');
  return{text,model:chosen,provider:'Puter AI'};
}
async function compare(messages,{count=3}={}){
  await ensure();
  const models=await pickCompareModels('compare',count);
  const settled=await Promise.allSettled(models.map(async model=>{const r=await window.puter.ai.chat(messages,{model,normalize:true});const text=textFrom(r).trim();if(!text)throw new Error('empty');return{model,text};}));
  return settled.filter(x=>x.status==='fulfilled').map(x=>x.value);
}
async function synthesize(question,answers,lang='en'){
  if(!answers.length)throw new Error('No comparison answers.');
  const model=await pickModel('research','auto',answers.map(x=>x.model));
  const prompt=[
    'You are a careful comparison editor. Produce one concise best answer in language code '+lang+'.',
    'Use only the candidate answers below. Point out important disagreements or uncertainty instead of inventing facts.',
    'Question: '+question,
    'Candidates:\n'+answers.map((a,i)=>'['+(i+1)+'] '+a.model+'\n'+a.text).join('\n\n')
  ].join('\n\n');
  const r=await window.puter.ai.chat(prompt,{model,normalize:true});
  const text=textFrom(r).trim();return{text:text||answers[0].text,model};
}
async function image(prompt,{model='auto',provider=''}={}){
  await ensure();if(!window.puter?.ai?.txt2img)throw new Error('Puter image generation is not available.');
  const opts={};if(model&&model!=='auto')opts.model=model;if(provider)opts.provider=provider;
  return window.puter.ai.txt2img(prompt,opts);
}
async function video(prompt,{model='auto'}={}){
  await ensure();if(!window.puter?.ai?.txt2vid)throw new Error('Puter video generation is not available.');
  const opts={};if(model&&model!=='auto')opts.model=model;
  return window.puter.ai.txt2vid(prompt,opts);
}
function fillSelect(select,models,placeholder='Auto best available'){
  if(!select)return;const current=select.value;select.innerHTML='';const a=document.createElement('option');a.value='auto';a.textContent=placeholder;select.appendChild(a);
  for(const m of models){const id=modelId(m);if(!id)continue;const o=document.createElement('option');o.value=id;o.textContent=(m?.provider?m.provider+' · ':'')+(m?.name||id);select.appendChild(o);}
  if([...select.options].some(o=>o.value===current))select.value=current;
}
window.AIHPuterRouter={ensure,listChatModels,listImageModels,detectIntent,pickModel,pickCompareModels,chat,compare,synthesize,image,video,fillSelect,textFrom};
})();
