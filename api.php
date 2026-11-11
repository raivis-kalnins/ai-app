<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';require __DIR__.'/includes/http.php';require __DIR__.'/includes/figma.php';require __DIR__.'/includes/providers.php';require __DIR__.'/includes/images.php';require __DIR__.'/includes/search.php';require __DIR__.'/includes/generators.php';require __DIR__.'/includes/plugins.php';require __DIR__.'/includes/i18n.php';require __DIR__.'/includes/imagebuilder.php';
aih_headers(false);aih_require_login();
$action=(string)($_GET['action']??$_POST['action']??'');
if($_SERVER['REQUEST_METHOD']==='POST')aih_check_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['csrf']??null);
try {
 // ---- Image Builder ----
 if($action==='ib_generate'){if(session_status()===PHP_SESSION_ACTIVE)session_write_close();aih_json(ib_generate($_POST));}
 if($action==='ib_status'){if(session_status()===PHP_SESSION_ACTIVE)session_write_close();aih_json(ib_horde_status((string)($_POST['job']??'')));}
 if($action==='ib_file'){ib_serve((string)($_GET['f']??''));}
 // ---- /Image Builder ----
 if($action==='providers'){aih_json(['ok'=>true,'providers'=>aih_provider_public_status(),'model_presets'=>aih_model_presets()]);}
 if($action==='chat_history'){ $in=$_SERVER['REQUEST_METHOD']==='POST'?aih_json_input():$_GET;$chatId=aih_chat_id((string)($in['chat_id']??''));aih_json(['ok'=>true,'chat_id'=>$chatId,'messages'=>aih_chat_history_load($chatId!==''?$chatId:null),'retention_days'=>30]);}
 if($action==='chat_threads'){aih_json(['ok'=>true,'threads'=>aih_chat_thread_list(30),'retention_days'=>30]);}
 if($action==='chat_history_save'){$in=aih_json_input();$role=(string)($in['role']??'assistant');$content=trim((string)($in['content']??''));if($content==='')aih_json(['ok'=>false,'error'=>'Empty chat message'],422);$saved=aih_chat_history_add($role,$content,(string)($in['meta']??''),(string)($in['kind']??'text'),(string)($in['chat_id']??''),$in['data']??null);aih_json(['ok'=>true]+$saved);}
 if($action==='chat_history_clear'){$in=aih_json_input();aih_chat_history_clear((string)($in['chat_id']??''));aih_json(['ok'=>true]);}
 if($action==='chat_thread_delete'){$in=aih_json_input();$id=aih_chat_id((string)($in['chat_id']??''));if($id==='')aih_json(['ok'=>false,'error'=>'Invalid chat id'],422);aih_chat_thread_delete($id);aih_json(['ok'=>true]);}
 if($action==='provider_test'){$in=aih_json_input();$id=(string)($in['provider']??'');$cat=aih_provider_catalog();if(!isset($cat[$id]))aih_json(['ok'=>false,'error'=>'Unknown provider'],404);if(!empty($cat[$id]['client_side']))aih_json(['ok'=>false,'error'=>'This provider is tested in the browser.'],422);$s=aih_settings();if(!empty($cat[$id]['needs_key'])&&trim((string)($s['providers']['keys'][$id]??''))==='')aih_json(['ok'=>false,'error'=>'Add the optional/free account key first.'],422);if(!empty($cat[$id]['requires_account_id'])&&trim((string)($s['providers']['cloudflare_account_id']??''))==='')aih_json(['ok'=>false,'error'=>'Add the Cloudflare Account ID in Provider connection details first.'],422);$res=aih_provider_call($id,[['role'=>'user','content'=>'Reply with exactly: OK']],$s);aih_json($res,!empty($res['ok'])?200:502);}
 if($action==='figma_prepare'){$in=aih_json_input();$s=aih_figma_save_credentials($in);$url=aih_figma_authorize_url($s);aih_audit('figma_oauth_prepare');aih_json(['ok'=>true,'authorize_url'=>$url,'status'=>aih_figma_public_status($s)]);}
 if($action==='figma_status'){aih_json(['ok'=>true,'status'=>aih_figma_public_status(aih_settings())]);}
 if($action==='figma_test'){try{$r=aih_figma_test(aih_settings(),true);aih_audit('figma_test_ok');aih_json($r);}catch(Throwable $e){aih_audit('figma_test_failed',['error'=>$e->getMessage()]);aih_json(['ok'=>false,'error'=>$e->getMessage()],502);}}
 if($action==='figma_disconnect'){aih_figma_token_clear();aih_audit('figma_disconnected');aih_json(['ok'=>true,'status'=>aih_figma_public_status(aih_settings())]);}
 if($action==='figma_projects'){$in=aih_json_input();try{$r=aih_figma_list_projects(aih_settings(),(string)($in['team_id']??''));aih_json($r);}catch(Throwable $e){aih_json(['ok'=>false,'error'=>$e->getMessage(),'hint'=>'If your OAuth app cannot use Figma project/folder listing, paste a direct Figma file URL in App & WP Builder.'],403);}}
 if($action==='figma_files'){$in=aih_json_input();try{aih_json(aih_figma_list_project_files(aih_settings(),(string)($in['source_id']??''),(string)($in['source_type']??'folder')));}catch(Throwable $e){aih_json(['ok'=>false,'error'=>$e->getMessage()],403);}}
 if($action==='figma_file_summary'){$in=aih_json_input();try{aih_json(aih_figma_file_summary(aih_settings(),(string)($in['file']??'')));}catch(Throwable $e){aih_json(['ok'=>false,'error'=>$e->getMessage()],422);}}
 if($action==='clear_cache'){ $deleted=0;$bytes=0; foreach(glob(AIH_CACHE.'/*')?:[] as$f){if(is_file($f)){$sz=(int)(@filesize($f)?:0);if(@unlink($f)){$deleted++;$bytes+=$sz;}}elseif(is_dir($f)){ $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as$x){$x->isDir()?@rmdir($x->getPathname()):@unlink($x->getPathname());}@rmdir($f);$deleted++;}} aih_write_json(AIH_STORAGE.'/provider_state.json',[]); aih_audit('cache_cleared',['deleted'=>$deleted,'bytes'=>$bytes]); aih_json(['ok'=>true,'deleted'=>$deleted,'bytes'=>$bytes]); }
 if($action==='chat'){
   $in=aih_json_input();$msg=trim((string)($in['message']??''));$hist=$in['history']??[];if($msg==='')aih_json(['ok'=>false,'error'=>'Message is empty'],422);
   $s=aih_settings();$lang=(string)($in['lang']??'en');if(!array_key_exists($lang,aih_languages()))$lang='en';$task=preg_replace('/[^a-z_-]/','',(string)($in['task']??'chat'));
   $taskHint=match($task){
     'code'=>' Act as a senior software engineer. Identify the likely root cause before changing code; preserve the current architecture unless a rewrite is justified. Prefer complete production-ready patches/files, secure defaults, responsive accessible UI, compatibility notes and a short verification plan. When presenting code, use fenced Markdown blocks with a language identifier.',
     'research'=>' Be evidence-oriented. Separate sourced facts, inference and unknowns; use current sources when supplied and cite only URLs that actually support the claim.',
     'compare'=>' Compare alternatives fairly against explicit criteria, state uncertainty and finish with a practical recommendation.',
     default=>' Understand the real task before replying. Prefer a finished result over generic advice and keep follow-up questions only for truly blocking information.'
   };
   $ground=['rows'=>[],'engines'=>[]];$currentNeed=(bool)preg_match('/\b(?:latest|current|today|news|search|find|look up|lookup|price|opening hours|source|evidence|2026|recent|docs?|documentation|version|release)\b/ui',$msg);$devNeed=$task==='code'&&!empty($s['search']['developer_grounding'])&&aih_search_is_developer_query($msg);
   $needsGround=!empty($s['search']['smart_grounding'])&&($task==='research'||$currentNeed||$devNeed);
   if($needsGround){$ground=aih_search_simple_grounding($msg,$lang,$s,14);if(!empty($ground['rows']))$taskHint.=' Use the supplied live web snippets as grounding. Prefer official documentation and developer sources when available. Cite source URLs only when they support the claim; never invent citations.';}
   $messages=[['role'=>'system','content'=>(string)($s['system_prompt']??'You are helpful.').aih_plugins_system_context().$taskHint.' Reply in interface language '.$lang.' unless the user clearly asks for another language.']];
   if(is_array($hist))foreach(array_slice($hist,-20)as$h)if(is_array($h)&&isset($h['role'],$h['content']))$messages[]=['role'=>(string)$h['role'],'content'=>substr((string)$h['content'],0,12000)];
   $userContent=substr($msg,0,16000);if(!empty($ground['rows'])){$src=[];foreach(array_slice($ground['rows'],0,10)as$i=>$r)$src[]='['.($i+1).'] '.($r['title']??'').' | '.($r['url']??'').' | '.($r['snippet']??'');$userContent.="\n\nLIVE WEB SOURCES (use only when relevant):\n".implode("\n",$src);}
   $messages[]=['role'=>'user','content'=>$userContent];$res=aih_ai_chat($messages,$s,$task);if(!empty($ground['rows'])){$res['sources']=array_map(fn($r)=>['title'=>(string)($r['title']??''),'url'=>(string)($r['url']??''),'engine'=>(string)($r['engine']??'')],array_slice($ground['rows'],0,10));$res['search_engines']=$ground['engines'];}$res['smart_mode']=true;aih_json($res,$res['ok']?200:503);
 }
 if($action==='chat_compare'){$in=aih_json_input();$msg=trim((string)($in['message']??''));$hist=$in['history']??[];if($msg==='')aih_json(['ok'=>false,'error'=>'Message is empty'],422);$s=aih_settings();$lang=(string)($in['lang']??'en');if(!array_key_exists($lang,aih_languages()))$lang='en';$messages=[['role'=>'system','content'=>(string)($s['system_prompt']??'You are helpful.').aih_plugins_system_context().' Give an independent answer for comparison. Reply in interface language '.$lang.'.']];if(is_array($hist))foreach(array_slice($hist,-12)as$h)if(is_array($h)&&isset($h['role'],$h['content']))$messages[]=['role'=>(string)$h['role'],'content'=>substr((string)$h['content'],0,9000)];$messages[]=['role'=>'user','content'=>substr($msg,0,16000)];$res=aih_ai_compare($messages,$s,3);aih_json($res,200);}
 if($action==='search'){$in=aih_json_input();if(session_status()===PHP_SESSION_ACTIVE)session_write_close();$res=aih_run_search($in);aih_json($res,$res['ok']?200:422);}
 if($action==='search_analyze'){$in=aih_json_input();if(session_status()===PHP_SESSION_ACTIVE)session_write_close();$res=aih_analyze_search_report((string)($in['report_id']??''));aih_json($res,!empty($res['ok'])?200:404);}
 if($action==='generate_project'){$in=aih_json_input();$res=aih_generate_project($in);aih_json($res,$res['ok']?200:500);}
 if($action==='package_project'){$in=aih_json_input();$res=aih_package_project_text($in);aih_json($res,$res['ok']?200:422);}
 if($action==='report_client_summary'){$in=aih_json_input();$id=preg_replace('/[^A-Za-z0-9_-]/','',(string)($in['report_id']??''));$text=trim((string)($in['text']??''));$provider=trim((string)($in['provider']??'Browser AI'));if($id===''||$text==='')aih_json(['ok'=>false,'error'=>'Missing report summary'],422);if(strlen($text)>24000)$text=substr($text,0,24000);$path=AIH_REPORTS.'/'.$id.'.json';if(!is_file($path))aih_json(['ok'=>false,'error'=>'Report not found'],404);aih_update_json($path,function($r)use($text,$provider){$r=is_array($r)?$r:[];$r['ai_summary']=['text'=>$text,'provider'=>$provider];return $r;},[]);aih_json(['ok'=>true]);}
 if($action==='image_cache_remote'){$in=aih_json_input();$url=trim((string)($in['url']??''));if($url==='')aih_json(['ok'=>false,'error'=>'Image URL is empty'],422);$res=aih_cache_remote_image($url);aih_json($res,!empty($res['ok'])?200:(int)($res['code']??422));}
 if($action==='image_compose_save'){$in=aih_json_input();$data=trim((string)($in['data_url']??''));if($data==='')aih_json(['ok'=>false,'error'=>'Composed image data is empty'],422);$res=aih_save_composed_data_url($data,(string)($in['prompt']??''));aih_json($res,!empty($res['ok'])?200:(int)($res['code']??422));}
 if($action==='image_start'){
   $in=aih_json_input();$prompt=trim((string)($in['prompt']??''));if($prompt==='')aih_json(['ok'=>false,'error'=>'Prompt is empty'],422);$s=aih_settings();$provider=trim((string)($in['provider']??($s['images']['provider']??'auto')))?:'auto';
   if(session_status()===PHP_SESSION_ACTIVE)session_write_close();$res=aih_image_start($prompt,$provider,$s);aih_json($res,!empty($res['ok'])?200:503);
 }
 if($action==='image_status'){
   $in=aih_json_input();$provider=trim((string)($in['provider_id']??''));$requestId=trim((string)($in['request_id']??''));if($provider!=='aihorde')aih_json(['ok'=>false,'error'=>'Unsupported asynchronous image provider'],422);$s=aih_settings();if(session_status()===PHP_SESSION_ACTIVE)session_write_close();$res=aih_aihorde_status($requestId,$s);$code=!empty($res['ok'])?200:((int)($res['code']??0)===422?422:503);aih_json($res,$code);
 }
 if($action==='image_cancel'){
   $in=aih_json_input();$provider=trim((string)($in['provider_id']??''));$requestId=trim((string)($in['request_id']??''));if($provider!=='aihorde')aih_json(['ok'=>false,'error'=>'Unsupported asynchronous image provider'],422);aih_json(aih_aihorde_cancel($requestId));
 }
 if($action==='image'){
   // Legacy synchronous endpoint retained for cached older browsers. v0.6.7 uses image_start + image_status, smart background generation, free web-image fallback and the Beautiful Greeting Card Studio.
   $in=aih_json_input();$prompt=trim((string)($in['prompt']??''));if($prompt==='')aih_json(['ok'=>false,'error'=>'Prompt is empty'],422);$s=aih_settings();$provider=trim((string)($in['provider']??($s['images']['provider']??'auto')))?:'auto';
   $res=aih_image_generate($prompt,$provider,$s);aih_json($res,!empty($res['ok'])?200:503);
 }
 if($action==='plugins'&&$_SERVER['REQUEST_METHOD']==='GET'){aih_json(['ok'=>true,'plugins'=>array_values(aih_plugins_catalog()),'quick_actions'=>aih_plugins_quick_actions()]);}
 if($action==='plugin_toggle'){$in=aih_json_input();$id=(string)($in['id']??'');$enabled=(bool)($in['enabled']??false);aih_json(aih_plugin_toggle($id,$enabled));}
 if($action==='plugin_remove'){$in=aih_json_input();aih_json(aih_plugin_remove((string)($in['id']??'')));}
 if($action==='plugin_upload'){if(empty($_FILES['file'])||!is_array($_FILES['file']))aih_json(['ok'=>false,'error'=>'No plugin ZIP received'],422);$f=$_FILES['file'];if((int)$f['error']!==UPLOAD_ERR_OK)aih_json(['ok'=>false,'error'=>'Plugin upload error '.(int)$f['error']],422);if((int)$f['size']>26214400)aih_json(['ok'=>false,'error'=>'Plugin ZIP maximum is 25 MB'],413);if(strtolower(pathinfo((string)$f['name'],PATHINFO_EXTENSION))!=='zip')aih_json(['ok'=>false,'error'=>'Upload a .zip plugin package'],422);aih_json(aih_plugin_install_zip((string)$f['tmp_name'],(string)$f['name']));}
 if($action==='upload'){
   if(empty($_FILES['file'])||!is_array($_FILES['file']))aih_json(['ok'=>false,'error'=>'No file'],422);$f=$_FILES['file'];if((int)$f['error']!==UPLOAD_ERR_OK)aih_json(['ok'=>false,'error'=>'Upload error '.$f['error'].'. Check server PHP/Nginx 1 GB limits.'],422);$max=(int)(aih_config()['app']['max_upload_bytes']??1073741824);$size=(int)$f['size'];if($size>$max)aih_json(['ok'=>false,'error'=>'Maximum upload size is 1 GB'],413);$name=aih_safe_name((string)$f['name']);$fn=aih_new_id('upload').'_'.$name;if(!move_uploaded_file((string)$f['tmp_name'],AIH_UPLOADS.'/'.$fn))aih_json(['ok'=>false,'error'=>'Could not store upload'],500);aih_audit('file_uploaded',['file'=>$fn,'bytes'=>$size]);aih_json(['ok'=>true,'file'=>$fn,'bytes'=>$size]);
 }
 if($action==='delete'){$in=aih_json_input();$bucket=(string)($in['bucket']??'');$file=aih_safe_name((string)($in['file']??''));if(!in_array($bucket,['uploads','generated','reports'],true))aih_json(['ok'=>false,'error'=>'Bad bucket'],422);$path=aih_path($bucket,$file);$ok=is_file($path)?@unlink($path):false;aih_json(['ok'=>$ok]);}
 if($action==='files'){
   $ttl=aih_file_retention_seconds();$rows=[];foreach(['uploads'=>AIH_UPLOADS,'generated'=>AIH_GENERATED,'reports'=>AIH_REPORTS]as$bucket=>$dir)foreach(glob($dir.'/*')?:[]as$f)if(is_file($f)){$modified=(int)(filemtime($f)?:0);$rows[]=['bucket'=>$bucket,'file'=>basename($f),'bytes'=>(int)(filesize($f)?:0),'modified'=>$modified,'expires'=>$modified+$ttl,'retention_seconds'=>$ttl];}usort($rows,fn($a,$b)=>$b['modified']<=>$a['modified']);aih_json(['ok'=>true,'files'=>$rows,'stats'=>aih_storage_stats(),'retention_seconds'=>$ttl]);
 }
 if($action==='cleanup'){$r=aih_cleanup_expired(true);aih_json(['ok'=>true]+$r);}
 if($action==='settings'){
   if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');aih_json(['ok'=>false,'error'=>'Use POST for settings changes.'],405);}
   $in=aih_json_input();$s=aih_settings();$p=&$s['providers'];$catalog=aih_provider_catalog();
   foreach($catalog as $id=>$meta){
     if(array_key_exists('enabled_'.$id,$in))$p['enabled'][$id]=(bool)$in['enabled_'.$id];
     if(array_key_exists('model_'.$id,$in))$p['models'][$id]=trim((string)$in['model_'.$id]);
     if(!empty($meta['accepts_key'])&&array_key_exists('key_'.$id,$in)&&trim((string)$in['key_'.$id])!=='')$p['keys'][$id]=trim((string)$in['key_'.$id]);
   }
   if(isset($in['provider_order'])&&is_array($in['provider_order']))$p['order']=array_values(array_filter($in['provider_order'],fn($x)=>isset($catalog[$x])));
   foreach(['ollama_local_url','lmstudio_local_url','llamacpp_local_url','localai_local_url','cloudflare_account_id','custom_openai_url']as$k)if(array_key_exists($k,$in))$p[$k]=trim((string)$in[$k]);
   if(array_key_exists('searxng_url',$in))$s['search']['searxng_url']=trim((string)$in['searxng_url']);
   if(array_key_exists('ollama_web_key',$in)&&trim((string)$in['ollama_web_key'])!=='')$s['search']['ollama_web_key']=trim((string)$in['ollama_web_key']);
   if(array_key_exists('brave_search_key',$in)&&trim((string)$in['brave_search_key'])!=='')$s['search']['brave_search_key']=trim((string)$in['brave_search_key']);
   if(array_key_exists('smart_grounding',$in))$s['search']['smart_grounding']=(bool)$in['smart_grounding'];
   if(array_key_exists('developer_grounding',$in))$s['search']['developer_grounding']=(bool)$in['developer_grounding'];
   if(array_key_exists('research_sources',$in))$s['search']['research_sources']=(bool)$in['research_sources'];
   if(array_key_exists('ai_enrich',$in))$s['search']['ai_enrich']=(bool)$in['ai_enrich'];
   if(array_key_exists('image_provider',$in))$s['images']['provider']=(string)$in['image_provider'];
   if(array_key_exists('media_provider_order',$in)){$parts=is_array($in['media_provider_order'])?$in['media_provider_order']:explode(',',(string)$in['media_provider_order']);$known=array_keys(aih_image_catalog());$s['images']['order']=array_values(array_unique(array_values(array_filter(array_map(fn($x)=>trim((string)$x),$parts),fn($x)=>in_array($x,$known,true)))));}
   if(array_key_exists('puter_video_model',$in))$s['images']['puter_video_model']=trim((string)$in['puter_video_model'])?:'auto';
   if(array_key_exists('sdwebui_url',$in))$s['images']['sdwebui_url']=trim((string)$in['sdwebui_url']);
   if(array_key_exists('comfyui_url',$in))$s['images']['comfyui_url']=trim((string)$in['comfyui_url']);
   if(array_key_exists('comfyui_checkpoint',$in))$s['images']['comfyui_checkpoint']=trim((string)$in['comfyui_checkpoint']);
   if(array_key_exists('comfyui_width',$in))$s['images']['comfyui_width']=max(256,min(1536,(int)$in['comfyui_width']));
   if(array_key_exists('comfyui_height',$in))$s['images']['comfyui_height']=max(256,min(1536,(int)$in['comfyui_height']));
   if(array_key_exists('pollinations_model',$in))$s['images']['pollinations_model']=trim((string)$in['pollinations_model']);
   if(array_key_exists('cloudflare_image_model',$in))$s['images']['cloudflare_image_model']=trim((string)$in['cloudflare_image_model']);
   if(array_key_exists('alibaba_workspace_id',$in))$s['images']['alibaba_workspace_id']=preg_replace('/[^A-Za-z0-9_-]/','',(string)$in['alibaba_workspace_id']);
   if(array_key_exists('alibaba_region',$in)&&in_array((string)$in['alibaba_region'],['ap-southeast-1','cn-beijing','cn-hongkong','us-east-1','eu-central-1','ap-northeast-1'],true))$s['images']['alibaba_region']=(string)$in['alibaba_region'];
   if(array_key_exists('alibaba_image_model',$in))$s['images']['alibaba_image_model']=trim((string)$in['alibaba_image_model']);
   if(array_key_exists('alibaba_api_key',$in)&&trim((string)$in['alibaba_api_key'])!=='')$s['images']['alibaba_api_key']=trim((string)$in['alibaba_api_key']);
   if(array_key_exists('huggingface_image_model',$in))$s['images']['huggingface_image_model']=trim((string)$in['huggingface_image_model']);
   if(array_key_exists('siliconflow_image_model',$in))$s['images']['siliconflow_image_model']=trim((string)$in['siliconflow_image_model']);
   if(array_key_exists('siliconflow_api_key',$in)&&trim((string)$in['siliconflow_api_key'])!=='')$s['images']['siliconflow_api_key']=trim((string)$in['siliconflow_api_key']);
   if(array_key_exists('aihorde_model',$in))$s['images']['aihorde_model']=trim((string)$in['aihorde_model']);
   if(array_key_exists('aihorde_timeout',$in))$s['images']['aihorde_timeout']=max(45,min(300,(int)$in['aihorde_timeout']));
   if(array_key_exists('aihorde_steps',$in))$s['images']['aihorde_steps']=max(4,min(30,(int)$in['aihorde_steps']));
   if(array_key_exists('image_speed_mode',$in)&&in_array((string)$in['image_speed_mode'],['fast','balanced','quality'],true))$s['images']['speed_mode']=(string)$in['image_speed_mode'];
   if(array_key_exists('aihorde_queue_limit',$in))$s['images']['aihorde_queue_limit']=max(15,min(900,(int)$in['aihorde_queue_limit']));
   if(array_key_exists('aihorde_api_key',$in)&&trim((string)$in['aihorde_api_key'])!=='')$s['images']['aihorde_api_key']=trim((string)$in['aihorde_api_key']);
   foreach(['unsplash_access_key','pexels_api_key','pixabay_api_key'] as$k)if(array_key_exists($k,$in)&&trim((string)$in[$k])!=='')$s['images'][$k]=trim((string)$in[$k]);
   if(array_key_exists('system_prompt',$in))$s['system_prompt']=trim((string)$in['system_prompt']);
   $s['integrations']=$s['integrations']??[];$s['integrations']['figma']=$s['integrations']['figma']??[];
   if(array_key_exists('figma_client_id',$in))$s['integrations']['figma']['client_id']=trim((string)$in['figma_client_id']);
   if(array_key_exists('figma_client_secret',$in)&&trim((string)$in['figma_client_secret'])!=='')$s['integrations']['figma']['client_secret']=trim((string)$in['figma_client_secret']);
   if(array_key_exists('figma_redirect_uri',$in))$s['integrations']['figma']['redirect_uri']=trim((string)$in['figma_redirect_uri']);
   if(array_key_exists('figma_scopes',$in))$s['integrations']['figma']['scopes']=aih_figma_scopes_normalize((string)$in['figma_scopes']);
   if(array_key_exists('figma_team_id',$in))$s['integrations']['figma']['team_id']=preg_replace('/[^0-9]/','',(string)$in['figma_team_id']);
   $s['security']=$s['security']??[];
   if(array_key_exists('hcaptcha_enabled',$in))$s['security']['hcaptcha_enabled']=(bool)$in['hcaptcha_enabled'];
   if(array_key_exists('hcaptcha_sitekey',$in))$s['security']['hcaptcha_sitekey']=trim((string)$in['hcaptcha_sitekey']);
   if(array_key_exists('hcaptcha_secret',$in)&&trim((string)$in['hcaptcha_secret'])!=='')$s['security']['hcaptcha_secret']=trim((string)$in['hcaptcha_secret']);
   $s['ui']=$s['ui']??[];
   if(array_key_exists('ui_language',$in)&&array_key_exists((string)$in['ui_language'],aih_languages()))$s['ui']['language']=(string)$in['ui_language'];
   if(array_key_exists('ui_theme',$in)&&in_array((string)$in['ui_theme'],['light','dark'],true))$s['ui']['theme']=(string)$in['ui_theme'];
   $new=(string)($in['new_password']??'');
   if($new!==''){
     if(!aih_auth_attempt_reserve('change'))aih_json(['ok'=>false,'error'=>'Too many attempts. Try again after 15 minutes.'],429);
     if(!aih_auth_verify((string)($in['current_password']??''),aih_settings()))aih_json(['ok'=>false,'error'=>'Enter your correct current password to change it.'],403);
     $validation=aih_new_password_error($new);
     if($validation!=='')aih_json(['ok'=>false,'error'=>$validation],422);
     if(!hash_equals($new,(string)($in['confirm_password']??'')))aih_json(['ok'=>false,'error'=>'The new password and confirmation do not match.'],422);
     if(aih_auth_verify($new,aih_settings()))aih_json(['ok'=>false,'error'=>'Choose a different password.'],422);
     $s['password_hash']=password_hash($new,PASSWORD_DEFAULT);
   }
   aih_save_settings($s);
   if($new!==''){
     aih_remember_revoke_all();
     // Keep this tab's CSRF token while rotating its session ID. Other sessions
     // and every remember-me cookie stop working against the new hash.
     aih_auth_mark_logged_in((string)$s['password_hash'],false);
     aih_auth_attempt_clear('change');
     aih_audit('password_changed');
   }
   aih_audit('settings_saved');aih_json(['ok'=>true,'password_changed'=>$new!=='']);
 }
 aih_json(['ok'=>false,'error'=>'Unknown action'],404);
} catch(Throwable $e){aih_json(['ok'=>false,'error'=>'Server error: '.$e->getMessage()],500);}