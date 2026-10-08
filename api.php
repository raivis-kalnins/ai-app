<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';require __DIR__.'/includes/http.php';require __DIR__.'/includes/providers.php';require __DIR__.'/includes/search.php';require __DIR__.'/includes/generators.php';require __DIR__.'/includes/i18n.php';
aih_headers(false);aih_require_login();
$action=(string)($_GET['action']??$_POST['action']??'');
if($_SERVER['REQUEST_METHOD']==='POST')aih_check_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['csrf']??null);
try {
 if($action==='providers'){aih_json(['ok'=>true,'providers'=>aih_provider_public_status(),'model_presets'=>aih_model_presets()]);}
 if($action==='provider_test'){$in=aih_json_input();$id=(string)($in['provider']??'');$cat=aih_provider_catalog();if(!isset($cat[$id]))aih_json(['ok'=>false,'error'=>'Unknown provider'],404);if(!empty($cat[$id]['client_side']))aih_json(['ok'=>false,'error'=>'This provider is tested in the browser.'],422);$s=aih_settings();if(!empty($cat[$id]['needs_key'])&&trim((string)($s['providers']['keys'][$id]??''))==='')aih_json(['ok'=>false,'error'=>'Add the optional/free account key first.'],422);$res=aih_provider_call($id,[['role'=>'user','content'=>'Reply with exactly: OK']],$s);aih_json($res,!empty($res['ok'])?200:502);}
 if($action==='chat'){$in=aih_json_input();$msg=trim((string)($in['message']??''));$hist=$in['history']??[];if($msg==='')aih_json(['ok'=>false,'error'=>'Message is empty'],422);$s=aih_settings();$lang=(string)($in['lang']??'en');if(!array_key_exists($lang,aih_languages()))$lang='en';$task=preg_replace('/[^a-z_-]/','',(string)($in['task']??'chat'));$taskHint=match($task){'code'=>' Prioritize production-ready code, WordPress/app engineering, security and complete files.','research'=>'Be evidence-oriented; distinguish sourced facts from assumptions.','compare'=>'Compare alternatives fairly and state uncertainty.',default=>''};$messages=[['role'=>'system','content'=>(string)($s['system_prompt']??'You are helpful.').$taskHint.' Reply in interface language '.$lang.' unless the user clearly asks for another language.']];if(is_array($hist))foreach(array_slice($hist,-20)as$h)if(is_array($h)&&isset($h['role'],$h['content']))$messages[]=['role'=>(string)$h['role'],'content'=>substr((string)$h['content'],0,12000)];$messages[]=['role'=>'user','content'=>substr($msg,0,16000)];$res=aih_ai_chat($messages,$s);aih_json($res,$res['ok']?200:503);}
 if($action==='chat_compare'){$in=aih_json_input();$msg=trim((string)($in['message']??''));$hist=$in['history']??[];if($msg==='')aih_json(['ok'=>false,'error'=>'Message is empty'],422);$s=aih_settings();$lang=(string)($in['lang']??'en');if(!array_key_exists($lang,aih_languages()))$lang='en';$messages=[['role'=>'system','content'=>(string)($s['system_prompt']??'You are helpful.').' Give an independent answer for comparison. Reply in interface language '.$lang.'.']];if(is_array($hist))foreach(array_slice($hist,-12)as$h)if(is_array($h)&&isset($h['role'],$h['content']))$messages[]=['role'=>(string)$h['role'],'content'=>substr((string)$h['content'],0,9000)];$messages[]=['role'=>'user','content'=>substr($msg,0,16000)];$res=aih_ai_compare($messages,$s,3);aih_json($res,200);}
 if($action==='search'){$in=aih_json_input();$res=aih_run_search($in);aih_json($res,$res['ok']?200:422);}
 if($action==='generate_project'){$in=aih_json_input();$res=aih_generate_project($in);aih_json($res,$res['ok']?200:500);}
 if($action==='package_project'){$in=aih_json_input();$res=aih_package_project_text($in);aih_json($res,$res['ok']?200:422);}
 if($action==='report_client_summary'){$in=aih_json_input();$id=preg_replace('/[^A-Za-z0-9_-]/','',(string)($in['report_id']??''));$text=trim((string)($in['text']??''));$provider=trim((string)($in['provider']??'Browser AI'));if($id===''||$text==='')aih_json(['ok'=>false,'error'=>'Missing report summary'],422);if(strlen($text)>24000)$text=substr($text,0,24000);$path=AIH_REPORTS.'/'.$id.'.json';if(!is_file($path))aih_json(['ok'=>false,'error'=>'Report not found'],404);aih_update_json($path,function($r)use($text,$provider){$r=is_array($r)?$r:[];$r['ai_summary']=['text'=>$text,'provider'=>$provider];return $r;},[]);aih_json(['ok'=>true]);}
 if($action==='image'){
   $in=aih_json_input();$prompt=trim((string)($in['prompt']??''));if($prompt==='')aih_json(['ok'=>false,'error'=>'Prompt is empty'],422);$s=aih_settings();$provider=(string)($in['provider']??($s['images']['provider']??'pollinations'));
   if($provider==='pollinations'){$key=trim((string)($s['providers']['keys']['pollinations']??''));if($key==='')aih_json(['ok'=>false,'error'=>'Pollinations now requires an API key/credits. Add the key in Settings, or use local Stable Diffusion WebUI.'],422);$model=(string)($s['images']['pollinations_model']??'flux');$url='https://gen.pollinations.ai/image/'.rawurlencode($prompt).'?model='.rawurlencode($model);$r=aih_http('GET',$url,null,['Authorization: Bearer '.$key,'Accept: image/*'],150);if($r['code']<200||$r['code']>=300||strlen($r['body'])<1000)aih_json(['ok'=>false,'error'=>'Image provider failed: HTTP '.$r['code']],502);$ext=str_contains(strtolower($r['content_type']),'png')?'png':'jpg';$fn=aih_new_id('image').'.'.$ext;file_put_contents(AIH_GENERATED.'/'.$fn,$r['body']);aih_json(['ok'=>true,'file'=>$fn,'provider'=>'Pollinations']);}
   if($provider==='sdwebui'){$base=rtrim((string)($s['images']['sdwebui_url']??''),'/');if($base==='')aih_json(['ok'=>false,'error'=>'Stable Diffusion WebUI URL missing'],422);$r=aih_http_json('POST',$base.'/sdapi/v1/txt2img',['prompt'=>$prompt,'steps'=>20,'width'=>1024,'height'=>1024],[],180);$j=json_decode($r['body'],true);$b64=(string)($j['images'][0]??'');if($r['code']<200||$r['code']>=300||$b64==='')aih_json(['ok'=>false,'error'=>'Local image endpoint failed'],502);$bin=base64_decode(preg_replace('#^data:image/\w+;base64,#','',$b64),true);if($bin===false)aih_json(['ok'=>false,'error'=>'Bad image response'],502);$fn=aih_new_id('image').'.png';file_put_contents(AIH_GENERATED.'/'.$fn,$bin);aih_json(['ok'=>true,'file'=>$fn,'provider'=>'Stable Diffusion WebUI']);}
   aih_json(['ok'=>false,'error'=>'Unknown image provider'],422);
 }
 if($action==='upload'){
   if(empty($_FILES['file'])||!is_array($_FILES['file']))aih_json(['ok'=>false,'error'=>'No file'],422);$f=$_FILES['file'];if((int)$f['error']!==UPLOAD_ERR_OK)aih_json(['ok'=>false,'error'=>'Upload error '.$f['error'].'. Check server PHP/Nginx 1 GB limits.'],422);$max=(int)(aih_config()['app']['max_upload_bytes']??1073741824);$size=(int)$f['size'];if($size>$max)aih_json(['ok'=>false,'error'=>'Maximum upload size is 1 GB'],413);$name=aih_safe_name((string)$f['name']);$fn=aih_new_id('upload').'_'.$name;if(!move_uploaded_file((string)$f['tmp_name'],AIH_UPLOADS.'/'.$fn))aih_json(['ok'=>false,'error'=>'Could not store upload'],500);aih_audit('file_uploaded',['file'=>$fn,'bytes'=>$size]);aih_json(['ok'=>true,'file'=>$fn,'bytes'=>$size]);
 }
 if($action==='delete'){$in=aih_json_input();$bucket=(string)($in['bucket']??'');$file=aih_safe_name((string)($in['file']??''));if(!in_array($bucket,['uploads','generated','reports'],true))aih_json(['ok'=>false,'error'=>'Bad bucket'],422);$path=aih_path($bucket,$file);$ok=is_file($path)?@unlink($path):false;aih_json(['ok'=>$ok]);}
 if($action==='files'){
   $rows=[];foreach(['uploads'=>AIH_UPLOADS,'generated'=>AIH_GENERATED,'reports'=>AIH_REPORTS]as$bucket=>$dir)foreach(glob($dir.'/*')?:[]as$f)if(is_file($f))$rows[]=['bucket'=>$bucket,'file'=>basename($f),'bytes'=>(int)(filesize($f)?:0),'modified'=>(int)(filemtime($f)?:0),'expires'=>(int)(filemtime($f)?:0)+(int)(aih_config()['app']['retention_seconds']??172800)];usort($rows,fn($a,$b)=>$b['modified']<=>$a['modified']);aih_json(['ok'=>true,'files'=>$rows,'stats'=>aih_storage_stats()]);
 }
 if($action==='cleanup'){$r=aih_cleanup_expired(true);aih_json(['ok'=>true]+$r);}
 if($action==='settings'){
   $in=aih_json_input();$s=aih_settings();$p=&$s['providers'];$catalog=aih_provider_catalog();
   foreach($catalog as $id=>$meta){
     if(array_key_exists('enabled_'.$id,$in))$p['enabled'][$id]=(bool)$in['enabled_'.$id];
     if(array_key_exists('model_'.$id,$in))$p['models'][$id]=trim((string)$in['model_'.$id]);
     if(!empty($meta['needs_key'])&&array_key_exists('key_'.$id,$in)&&trim((string)$in['key_'.$id])!=='')$p['keys'][$id]=trim((string)$in['key_'.$id]);
   }
   if(isset($in['provider_order'])&&is_array($in['provider_order']))$p['order']=array_values(array_filter($in['provider_order'],fn($x)=>isset($catalog[$x])));
   foreach(['ollama_local_url','lmstudio_local_url','llamacpp_local_url','localai_local_url','custom_openai_url']as$k)if(array_key_exists($k,$in))$p[$k]=trim((string)$in[$k]);
   if(array_key_exists('searxng_url',$in))$s['search']['searxng_url']=trim((string)$in['searxng_url']);
   if(array_key_exists('ollama_web_key',$in)&&trim((string)$in['ollama_web_key'])!=='')$s['search']['ollama_web_key']=trim((string)$in['ollama_web_key']);
   if(array_key_exists('ai_enrich',$in))$s['search']['ai_enrich']=(bool)$in['ai_enrich'];
   if(array_key_exists('image_provider',$in))$s['images']['provider']=(string)$in['image_provider'];
   if(array_key_exists('puter_image_model',$in))$s['images']['puter_image_model']=trim((string)$in['puter_image_model'])?:'auto';
   if(array_key_exists('puter_video_model',$in))$s['images']['puter_video_model']=trim((string)$in['puter_video_model'])?:'auto';
   if(array_key_exists('sdwebui_url',$in))$s['images']['sdwebui_url']=trim((string)$in['sdwebui_url']);
   if(array_key_exists('pollinations_model',$in))$s['images']['pollinations_model']=trim((string)$in['pollinations_model']);
   if(array_key_exists('system_prompt',$in))$s['system_prompt']=trim((string)$in['system_prompt']);
   $s['security']=$s['security']??[];
   if(array_key_exists('hcaptcha_enabled',$in))$s['security']['hcaptcha_enabled']=(bool)$in['hcaptcha_enabled'];
   if(array_key_exists('hcaptcha_sitekey',$in))$s['security']['hcaptcha_sitekey']=trim((string)$in['hcaptcha_sitekey']);
   if(array_key_exists('hcaptcha_secret',$in)&&trim((string)$in['hcaptcha_secret'])!=='')$s['security']['hcaptcha_secret']=trim((string)$in['hcaptcha_secret']);
   $s['ui']=$s['ui']??[];
   if(array_key_exists('ui_language',$in)&&array_key_exists((string)$in['ui_language'],aih_languages()))$s['ui']['language']=(string)$in['ui_language'];
   if(array_key_exists('ui_theme',$in)&&in_array((string)$in['ui_theme'],['light','dark'],true))$s['ui']['theme']=(string)$in['ui_theme'];
   $new=(string)($in['new_password']??'');if($new!==''){if(strlen($new)<10)aih_json(['ok'=>false,'error'=>'New password must be at least 10 characters'],422);$s['password_hash']=password_hash($new,PASSWORD_DEFAULT);}
   aih_save_settings($s);aih_audit('settings_saved');aih_json(['ok'=>true]);
 }
 aih_json(['ok'=>false,'error'=>'Unknown action'],404);
} catch(Throwable $e){aih_json(['ok'=>false,'error'=>'Server error: '.$e->getMessage()],500);}
