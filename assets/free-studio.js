/* No network, libraries or uploaded media: deterministic browser composition. */
(function(root){
'use strict';
function compose({mood='calm',tempo=96,seed=63,seconds=12}={}){
  tempo=Math.max(50,Math.min(160,Number(tempo)||96));
  seconds=Math.max(1,Math.min(16,Number(seconds)||12));
  let state=(Number(seed)||63)>>>0;
  const random=()=>{state=(Math.imul(state,1664525)+1013904223)>>>0;return state/4294967296};
  const rate=22050, samples=new Float32Array(Math.round(seconds*rate));
  const scale=mood==='night'?[0,3,5,7,10]:[0,2,4,7,9], base=mood==='arcade'?60:48, beat=60/tempo;
  function note(start,duration,midi,gain){
    const freq=440*Math.pow(2,(midi-69)/12),offset=Math.round(start*rate),count=Math.round(duration*rate);
    for(let i=0;i<count&&offset+i<samples.length;i++){
      const t=i/rate,env=Math.min(1,t/.015)*Math.min(1,(count-i)/(rate*.08));
      const sine=Math.sin(2*Math.PI*freq*t);
      const tone=mood==='arcade'?(sine+.22*Math.sin(6*Math.PI*freq*t))/1.22:sine;
      samples[offset+i]+=tone*gain*env*Math.exp(-t*1.6);
    }
  }
  for(let t=0,step=0;t<seconds;t+=beat/2,step++){
    note(t,beat*.8,base+12+scale[Math.floor(random()*scale.length)],.14);
    if(step%4===0){const chord=[0,5,7,0][Math.floor(step/8)%4];note(t,beat*1.9,base+chord,.18);note(t,beat*1.9,base+chord+7,.07)}
  }
  for(let i=0;i<samples.length;i++)samples[i]=Math.max(-.95,Math.min(.95,samples[i]))*Math.min(1,i/(rate*.03),(samples.length-1-i)/(rate*.12));
  return {samples,rate};
}
function wav({samples,rate}){
  const bytes=new ArrayBuffer(44+samples.length*2),v=new DataView(bytes);
  function str(at,s){for(let i=0;i<s.length;i++)v.setUint8(at+i,s.charCodeAt(i))}
  str(0,'RIFF');v.setUint32(4,36+samples.length*2,true);str(8,'WAVE');str(12,'fmt ');v.setUint32(16,16,true);v.setUint16(20,1,true);v.setUint16(22,1,true);v.setUint32(24,rate,true);v.setUint32(28,rate*2,true);v.setUint16(32,2,true);v.setUint16(34,16,true);str(36,'data');v.setUint32(40,samples.length*2,true);
  samples.forEach((x,i)=>v.setInt16(44+i*2,Math.round(Math.max(-1,Math.min(1,x))*32767),true));
  return bytes;
}
root.AIHFreeMusic={compose,wav};
if(typeof document==='undefined')return;
const $=id=>document.getElementById(id);if(!$('freeStudio'))return;
const lv=document.body.dataset.lang==='lv',status=$('fsStatus');
const say=(en,lat)=>status.textContent=lv?lat:en;
let audioURL='',videoURL='',cancel=null;
const options=()=>({mood:$('fsMood').value,tempo:$('fsTempo').value,seed:$('fsSeed').value,seconds:$('fsSeconds').value});
function audioFile(data){
  $('fsAudio').pause();if(audioURL)URL.revokeObjectURL(audioURL);
  audioURL=URL.createObjectURL(new Blob([wav(data)],{type:'audio/wav'}));
  $('fsAudio').src=audioURL;$('fsAudio').hidden=false;$('fsWav').href=audioURL;$('fsWav').hidden=false;
}
$('fsMusic').onclick=()=>{try{audioFile(compose(options()));say('Music ready. Press play or download WAV.','Mūzika gatava. Spied atskaņot vai lejupielādē WAV.')}catch(e){status.textContent=e.message}};
$('fsStop').onclick=()=>cancel?.();
$('fsVideo').onclick=async()=>{
  if(cancel)return;
  let context,source,stream,recorder,frame,timer,imageURL='',cancelled=false;
  const chunks=[];
  function cleanup(){clearTimeout(timer);cancelAnimationFrame(frame);try{source?.stop()}catch(_){}stream?.getTracks().forEach(t=>t.stop());context?.close().catch(()=>{});if(imageURL)URL.revokeObjectURL(imageURL);$('fsVideo').disabled=false;$('fsMusic').disabled=false;$('fsStop').hidden=true;cancel=null}
  try{
    const AC=root.AudioContext||root.webkitAudioContext;
    if(!AC||!root.MediaRecorder||!$('fsCanvas').captureStream)throw new Error(lv?'Šis pārlūks neatbalsta klipu eksportu. Izmanto WAV vai jaunāku pārlūku.':'This browser cannot export clips. Use WAV or a newer browser.');
    const file=$('fsImage').files[0];if(!file)throw new Error(lv?'Izvēlies attēlu klipam.':'Choose an image for the clip.');
    if(!['image/png','image/jpeg','image/webp'].includes(file.type)||file.size>10*1024*1024)throw new Error(lv?'Izvēlies PNG/JPEG/WebP attēlu līdz 10 MB.':'Choose a PNG/JPEG/WebP image under 10 MB.');
    $('fsVideo').disabled=true;$('fsMusic').disabled=true;$('fsStop').hidden=false;$('fsDownload').hidden=true;$('fsPreview').pause();$('fsPreview').hidden=true;$('fsAudio').pause();
    cancel=()=>{cancelled=true;cancelAnimationFrame(frame);if(recorder?.state==='recording')recorder.stop();else cleanup();say('Cancelled.','Atcelts.')};
    // Resume inside the click gesture, before image decoding.
    context=new AC();await context.resume();if(cancelled)return;
    const img=new Image();imageURL=URL.createObjectURL(file);img.src=imageURL;await img.decode();if(cancelled)return;
    const data=compose(options()),duration=data.samples.length/data.rate;
    const buffer=context.createBuffer(1,data.samples.length,data.rate);buffer.copyToChannel(data.samples,0);
    source=context.createBufferSource();source.buffer=buffer;
    const dest=context.createMediaStreamDestination();source.connect(dest);
    const canvas=$('fsCanvas'),ctx=canvas.getContext('2d');canvas.hidden=false;
    stream=canvas.captureStream(24);dest.stream.getAudioTracks().forEach(t=>stream.addTrack(t));
    const mime=['video/webm;codecs=vp8,opus','video/webm','video/mp4'].find(t=>MediaRecorder.isTypeSupported(t));
    if(!mime)throw new Error(lv?'Nav atbalstīta video formāta.':'No supported video recording format.');
    recorder=new MediaRecorder(stream,{mimeType:mime,videoBitsPerSecond:1800000});
    recorder.ondataavailable=e=>{if(e.data.size)chunks.push(e.data)};
    recorder.onerror=()=>{cancelled=true;cleanup();say('Export failed. Try a different browser.','Eksports neizdevās. Izmēģini citu pārlūku.')};
    recorder.onstop=()=>{
      if(!cancelled&&chunks.length){if(videoURL)URL.revokeObjectURL(videoURL);const blob=new Blob(chunks,{type:recorder.mimeType});videoURL=URL.createObjectURL(blob);$('fsDownload').href=videoURL;$('fsDownload').download='clip.'+(recorder.mimeType.includes('mp4')?'mp4':'webm');$('fsDownload').hidden=false;$('fsPreview').src=videoURL;$('fsPreview').hidden=false;$('fsCanvas').hidden=true;audioFile(data);say('Clip ready. Download it to keep it.','Klips gatavs. Lejupielādē, lai saglabātu.')}cleanup();
    };
    const title=$('fsTitle').value.trim(),started=context.currentTime;
    function draw(){
      if(cancelled)return;
      const elapsed=context.currentTime-started,p=Math.min(1,elapsed/duration),scale=Math.max(960/img.naturalWidth,540/img.naturalHeight)*(1+.08*p),w=img.naturalWidth*scale,h=img.naturalHeight*scale;
      ctx.fillStyle='#111';ctx.fillRect(0,0,960,540);ctx.drawImage(img,(960-w)/2,(540-h)/2,w,h);
      if(title){ctx.fillStyle='rgba(0,0,0,.65)';ctx.fillRect(0,400,960,140);ctx.fillStyle='white';ctx.textAlign='center';ctx.font='bold 34px sans-serif';let lines=[],line='';for(const word of title.split(/\s+/)){if(ctx.measureText(line+' '+word).width>850&&line){lines.push(line);line=word}else line+=(line?' ':'')+word}if(line)lines.push(line);lines.slice(0,2).forEach((text,i)=>ctx.fillText(text,480,455+i*42,880))}
      say('Recording '+Math.ceil(p*100)+'% — keep this tab active.','Ieraksts '+Math.ceil(p*100)+'% — turi cilni aktīvu.');frame=requestAnimationFrame(draw);
    }
    draw();recorder.start(500);source.start();timer=setTimeout(()=>{if(recorder.state==='recording')recorder.stop()},duration*1000);
  }catch(e){cleanup();status.textContent=e.message}
};
document.addEventListener('visibilitychange',()=>{if(document.hidden&&cancel)cancel()});
root.addEventListener('pagehide',()=>{cancel?.();if(audioURL)URL.revokeObjectURL(audioURL);if(videoURL)URL.revokeObjectURL(videoURL)});
})(globalThis);
