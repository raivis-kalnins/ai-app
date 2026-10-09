<?php
declare(strict_types=1);

const AIH_VERSION = '0.6.2';
const AIH_ROOT = __DIR__ . '/..';
const AIH_STORAGE = AIH_ROOT . '/storage';
const AIH_UPLOADS = AIH_STORAGE . '/uploads';
const AIH_GENERATED = AIH_STORAGE . '/generated';
const AIH_REPORTS = AIH_STORAGE . '/reports';
const AIH_CACHE = AIH_STORAGE . '/cache';
const AIH_LOGS = AIH_STORAGE . '/logs';
const AIH_CHATS = AIH_STORAGE . '/chats';

function aih_config(): array {
    static $cfg = null;
    if (is_array($cfg)) return $cfg;
    $file = AIH_ROOT . '/config.local.php';
    if (!is_file($file)) $file = AIH_ROOT . '/config.example.php';
    $cfg = require $file;
    date_default_timezone_set((string)($cfg['app']['timezone'] ?? 'Europe/London'));
    return $cfg;
}

function aih_path(string $bucket, string $name=''): string {
    $map = [
        'storage'=>AIH_STORAGE, 'uploads'=>AIH_UPLOADS, 'generated'=>AIH_GENERATED,
        'reports'=>AIH_REPORTS, 'cache'=>AIH_CACHE, 'logs'=>AIH_LOGS, 'chats'=>AIH_CHATS,
    ];
    if (!isset($map[$bucket])) throw new InvalidArgumentException('Unknown storage bucket');
    $base = $map[$bucket];
    return $name === '' ? $base : $base . DIRECTORY_SEPARATOR . $name;
}

function aih_default_settings(): array {
    $cfg = aih_config();
    return [
        'password_hash' => (string)($cfg['security']['initial_password_hash'] ?? ''),
        'providers' => [
            'order' => [
                'ollama_local','lmstudio_local','llamacpp_local','localai_local','aihorde_text',
                'groq','gemini','openrouter','cloudflare','vercel_gateway','nvidia_nim','huggingface','mistral','ollama_cloud','custom_openai','pollinations',
                'webllm_browser','puter'
            ],
            'enabled' => [
                'puter'=>true,
                'ollama_local'=>true,
                'lmstudio_local'=>false,
                'llamacpp_local'=>false,
                'localai_local'=>false,
                'webllm_browser'=>true,
                'aihorde_text'=>true,
                'groq'=>false,
                'gemini'=>false,
                'openrouter'=>false,
                'huggingface'=>false,
                'mistral'=>false,
                'cloudflare'=>false,
                'vercel_gateway'=>false,
                'nvidia_nim'=>false,
                'ollama_cloud'=>false,
                'pollinations'=>false,
                'custom_openai'=>false,
            ],
            'keys' => [
                'aihorde_text'=>'','groq'=>'','gemini'=>'','openrouter'=>'','cloudflare'=>'','vercel_gateway'=>'','nvidia_nim'=>'','huggingface'=>'','mistral'=>'','ollama_cloud'=>'','pollinations'=>'','custom_openai'=>''
            ],
            'models' => [
                'puter'=>'auto',
                'ollama_local'=>'auto',
                'lmstudio_local'=>'local-model',
                'llamacpp_local'=>'local-model',
                'localai_local'=>'local-model',
                'webllm_browser'=>'Llama-3.2-1B-Instruct-q4f16_1-MLC',
                'aihorde_text'=>'',
                'groq'=>'openai/gpt-oss-20b',
                'gemini'=>'gemini-3.8-flash',
                'openrouter'=>'openrouter/free',
                'huggingface'=>'deepseek-ai/DeepSeek-R1:fastest',
                'mistral'=>'mistral-small-latest',
                'cloudflare'=>'@cf/google/gemma-4-26b-a4b-it',
                'vercel_gateway'=>'anthropic/claude-haiku-5.5',
                'nvidia_nim'=>'openai/gpt-oss-20b',
                'ollama_cloud'=>'gpt-oss:20b-cloud',
                'pollinations'=>'openai-fast',
                'custom_openai'=>'',
            ],
            'ollama_local_url'=>'http://127.0.0.1:11434/v1/chat/completions',
            'lmstudio_local_url'=>'http://127.0.0.1:1234/v1/chat/completions',
            'llamacpp_local_url'=>'http://127.0.0.1:8080/v1/chat/completions',
            'localai_local_url'=>'http://127.0.0.1:8080/v1/chat/completions',
            'cloudflare_account_id'=>'',
            'custom_openai_url'=>'',
        ],
        'search' => [
            'searxng_url'=>'',
            'ollama_web_key'=>'',
            'max_results'=>40,
            'ai_enrich'=>true,
        ],
        'integrations' => [
            'figma' => [
                'client_id'=>'',
                'client_secret'=>'',
                'redirect_uri'=>'',
                'scopes'=>'current_user:read,file_content:read,file_metadata:read,projects:read,folders:read',
                'team_id'=>'1302775943917338214',
            ],
        ],
        'images' => [
            'provider'=>'auto',
            'order'=>['sdwebui','comfyui','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations','free_web','aihorde'],
            'puter_image_model'=>'auto',
            'puter_video_model'=>'auto',
            'pollinations_model'=>'flux',
            'sdwebui_url'=>'http://127.0.0.1:7860',
            'comfyui_url'=>'http://127.0.0.1:8188',
            'comfyui_checkpoint'=>'',
            'comfyui_width'=>768,
            'comfyui_height'=>768,
            'cloudflare_image_model'=>'@cf/bytedance/stable-diffusion-xl-lightning',
            'alibaba_api_key'=>'',
            'alibaba_workspace_id'=>'',
            'alibaba_region'=>'ap-southeast-1',
            'alibaba_image_model'=>'qwen-image-2.0',
            'huggingface_image_model'=>'black-forest-labs/FLUX.1-schnell',
            'siliconflow_api_key'=>'',
            'siliconflow_image_model'=>'black-forest-labs/FLUX.1-schnell',
            'aihorde_api_key'=>'',
            'aihorde_model'=>'',
            'aihorde_timeout'=>180,
            'aihorde_steps'=>16,
        ],
        'security' => [
            'hcaptcha_enabled'=>false,
            'hcaptcha_sitekey'=>'',
            'hcaptcha_secret'=>'',
        ],
        'ui' => [
            'language'=>'en',
            'theme'=>'light',
        ],
        'system_prompt' => 'You are a careful assistant for web development, WordPress, app development and structured research. Never invent source facts. When source data is missing, say it is missing.',
        'created_at' => date(DATE_ATOM),
    ];
}

function aih_read_json(string $path, mixed $default=[]): mixed {
    if (!is_file($path)) return $default;
    $h = @fopen($path,'rb'); if (!$h) return $default;
    try { @flock($h, LOCK_SH); $raw = stream_get_contents($h); @flock($h, LOCK_UN); }
    finally { fclose($h); }
    $v = json_decode((string)$raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $v : $default;
}

function aih_write_json(string $path, mixed $value): void {
    $dir = dirname($path); if (!is_dir($dir)) @mkdir($dir,0770,true);
    $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
    file_put_contents($tmp, json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL, LOCK_EX);
    @chmod($tmp,0660);
    rename($tmp,$path);
}

function aih_array_is_list_compat(array $v): bool { if($v===[])return true; return array_keys($v)===range(0,count($v)-1); }

function aih_merge_missing(array $defaults, array $current): array {
    foreach ($defaults as $k=>$v) {
        if (!array_key_exists($k,$current)) { $current[$k]=$v; continue; }
        if (is_array($v) && is_array($current[$k]) && !aih_array_is_list_compat($v)) {
            $current[$k]=aih_merge_missing($v,$current[$k]);
        }
    }
    return $current;
}

function aih_migrate_settings(array $current): array {
    $defaults = aih_default_settings();
    $merged = aih_merge_missing($defaults,$current);
    $known = $defaults['providers']['order'];
    $oldOrder = is_array($current['providers']['order'] ?? null) ? $current['providers']['order'] : [];
    // v0.6.0: add AI Horde Text near the local providers on installs still using the previous default order.
    $legacyProviderOrderV059=['ollama_local','lmstudio_local','llamacpp_local','localai_local','groq','gemini','openrouter','cloudflare','vercel_gateway','nvidia_nim','huggingface','mistral','ollama_cloud','custom_openai','pollinations','webllm_browser','puter'];
    $seed = (empty($oldOrder) || (string)($oldOrder[0]??'') === 'puter' || $oldOrder===$legacyProviderOrderV059) ? $known : $oldOrder;
    $order=[];
    foreach (array_merge($seed,$known) as $id) {
        $id=(string)$id;
        if (in_array($id,$known,true) && !in_array($id,$order,true)) $order[]=$id;
    }
    $merged['providers']['order']=$order;
    $imageKnown=$defaults['images']['order'];
    $oldImageOrder=is_array($current['images']['order']??null)?$current['images']['order']:[];
    $legacyImageOrder=['sdwebui','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations'];
    $legacyImageOrderV059=['sdwebui','aihorde','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations'];
    $legacyImageOrderV060=['sdwebui','comfyui','aihorde','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations'];
    $legacyImageOrderV061=['sdwebui','comfyui','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations','aihorde'];
    $imageSeed=($oldImageOrder===$legacyImageOrder||$oldImageOrder===array_merge($legacyImageOrder,['aihorde'])||$oldImageOrder===$legacyImageOrderV059||$oldImageOrder===$legacyImageOrderV060||$oldImageOrder===$legacyImageOrderV061)?$imageKnown:$oldImageOrder;
    $imageOrder=[];
    foreach(array_merge($imageSeed,$imageKnown) as $id){
        $id=(string)$id;
        if(in_array($id,$imageKnown,true)&&!in_array($id,$imageOrder,true))$imageOrder[]=$id;
    }
    // v0.6.2 introduces a no-key real-web-image fallback. On older/custom orders,
    // insert it immediately before AI Horde so low-quality anonymous generation is
    // not preferred simply because the new provider did not exist when the order was saved.
    if(!in_array('free_web',$oldImageOrder,true)&&in_array('aihorde',$imageOrder,true)){
        $imageOrder=array_values(array_filter($imageOrder,fn($id)=>$id!=='free_web'));
        $hordePos=array_search('aihorde',$imageOrder,true);
        array_splice($imageOrder,$hordePos===false?count($imageOrder):(int)$hordePos,0,['free_web']);
    }
    $merged['images']['order']=$imageOrder;
    if(($current['images']['provider']??'')==='puter') $merged['images']['provider']='auto';
    foreach ($known as $id) {
        if (!array_key_exists($id,$merged['providers']['enabled'])) $merged['providers']['enabled'][$id]=(bool)$defaults['providers']['enabled'][$id];
        if (!array_key_exists($id,$merged['providers']['models'])) $merged['providers']['models'][$id]=(string)$defaults['providers']['models'][$id];
    }
    // v0.4: old installs used gpt-5-nano and therefore never explored Puter's broader model catalog.
    // Move that legacy default to task-aware Auto; explicit user-selected models remain untouched.
    if (($current['providers']['models']['puter'] ?? '') === 'gpt-5-nano') $merged['providers']['models']['puter']='auto';
    // v0.6.1: legacy Ollama default becomes task-aware Auto. Explicit custom models stay untouched.
    if (($current['providers']['models']['ollama_local'] ?? '') === 'qwen3:8b') $merged['providers']['models']['ollama_local']='auto';
    // v0.6.2: raise the old fast AI Horde default slightly for better detail while retaining user overrides.
    if ((int)($current['images']['aihorde_steps'] ?? 0) === 10) $merged['images']['aihorde_steps']=16;
    return $merged;
}

function aih_bootstrap_storage(): void {
    foreach ([AIH_STORAGE,AIH_UPLOADS,AIH_GENERATED,AIH_REPORTS,AIH_CACHE,AIH_LOGS,AIH_CHATS] as $d) {
        if (!is_dir($d)) @mkdir($d, 0770, true);
    }
    $deny = "Require all denied\nDeny from all\n";
    if (!is_file(AIH_STORAGE.'/.htaccess')) @file_put_contents(AIH_STORAGE.'/.htaccess', $deny);
    $settingsPath=AIH_STORAGE.'/settings.json';
    if (!is_file($settingsPath)) {
        aih_write_json($settingsPath, aih_default_settings());
    } else {
        $before=aih_read_json($settingsPath,[]);
        $after=aih_migrate_settings(is_array($before)?$before:[]);
        if ($after !== $before) aih_write_json($settingsPath,$after);
    }
    if (!is_file(AIH_STORAGE.'/provider_state.json')) aih_write_json(AIH_STORAGE.'/provider_state.json', []);
}

function aih_update_json(string $path, callable $fn, mixed $default=[]): mixed {
    $lock = @fopen($path.'.lock','c+');
    if (!$lock) throw new RuntimeException('Storage lock failed');
    try {
        flock($lock,LOCK_EX);
        $v = $fn(aih_read_json($path,$default));
        aih_write_json($path,$v);
        flock($lock,LOCK_UN);
        return $v;
    } finally { fclose($lock); }
}

function aih_settings(): array {
    $v = aih_read_json(AIH_STORAGE.'/settings.json', []);
    return is_array($v) ? $v : [];
}
function aih_save_settings(array $v): void { aih_write_json(AIH_STORAGE.'/settings.json', aih_migrate_settings($v)); }

function aih_start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $cfg = aih_config();
    $secure = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    session_name((string)($cfg['app']['session_name'] ?? 'free_ai_hub_session'));
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

function aih_headers(bool $html=true): void {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if ($html) {
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob: https:; media-src 'self' data: blob: https:; style-src 'self' 'unsafe-inline' https://*.hcaptcha.com; script-src 'self' 'wasm-unsafe-eval' https://js.puter.com https://esm.run https://cdn.jsdelivr.net https://*.hcaptcha.com; connect-src 'self' https: wss:; frame-src https://*.puter.com https://*.hcaptcha.com; worker-src 'self' blob:; child-src 'self' blob:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    }
}

function aih_e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function aih_is_logged_in(): bool { return !empty($_SESSION['auth_ok']); }
function aih_require_login(): void { if (!aih_is_logged_in()) { http_response_code(401); exit('Unauthorized'); } }

function aih_secure_request(): bool {
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
}
function aih_remember_cookie_name(): string { return 'aih_remember'; }
function aih_remember_path(): string { return AIH_STORAGE.'/remember_tokens.json'; }
function aih_remember_expire_cookie(): void {
    setcookie(aih_remember_cookie_name(), '', ['expires'=>time()-3600,'path'=>'/','secure'=>aih_secure_request(),'httponly'=>true,'samesite'=>'Lax']);
    unset($_COOKIE[aih_remember_cookie_name()]);
}
function aih_remember_revoke_all(): void {
    aih_write_json(aih_remember_path(), []);
    aih_remember_expire_cookie();
}
function aih_remember_forget(): void {
    $raw=(string)($_COOKIE[aih_remember_cookie_name()] ?? '');
    $selector='';
    if(str_contains($raw,'.'))[$selector]=explode('.',$raw,2);
    if($selector!==''){
        aih_update_json(aih_remember_path(),function($rows)use($selector){$rows=is_array($rows)?$rows:[];return array_values(array_filter($rows,fn($r)=>!is_array($r)||!hash_equals((string)($r['selector']??''),$selector)));},[]);
    }
    aih_remember_expire_cookie();
}
function aih_remember_issue(string $passwordHash,int $days=30): void {
    $selector=bin2hex(random_bytes(9));
    $validator=rtrim(strtr(base64_encode(random_bytes(32)),'+/','-_'),'=');
    $expires=time()+max(1,$days)*86400;
    $record=[
        'selector'=>$selector,
        'validator_hash'=>hash('sha256',$validator),
        'password_fp'=>hash('sha256',$passwordHash),
        'created_at'=>time(),
        'expires_at'=>$expires,
    ];
    aih_update_json(aih_remember_path(),function($rows)use($record){
        $rows=is_array($rows)?$rows:[];$now=time();
        $rows=array_values(array_filter($rows,fn($r)=>is_array($r)&&(int)($r['expires_at']??0)>$now));
        $rows[]=$record;return array_slice($rows,-20);
    },[]);
    $cookie=$selector.'.'.$validator;
    setcookie(aih_remember_cookie_name(),$cookie,['expires'=>$expires,'path'=>'/','secure'=>aih_secure_request(),'httponly'=>true,'samesite'=>'Lax']);
    $_COOKIE[aih_remember_cookie_name()]=$cookie;
}
function aih_remember_try_login(array $settings): bool {
    if(aih_is_logged_in())return true;
    $raw=(string)($_COOKIE[aih_remember_cookie_name()] ?? '');
    if($raw===''||!str_contains($raw,'.'))return false;
    [$selector,$validator]=array_pad(explode('.',$raw,2),2,'');
    if(!preg_match('/^[a-f0-9]{18}$/',$selector)||!preg_match('/^[A-Za-z0-9_-]{30,}$/',$validator)){aih_remember_forget();return false;}
    $rows=aih_read_json(aih_remember_path(),[]);if(!is_array($rows))$rows=[];$record=null;
    foreach($rows as$r){if(is_array($r)&&hash_equals((string)($r['selector']??''),$selector)){$record=$r;break;}}
    $passwordHash=(string)($settings['password_hash']??'');
    $valid=is_array($record)
        && (int)($record['expires_at']??0)>time()
        && hash_equals((string)($record['validator_hash']??''),hash('sha256',$validator))
        && hash_equals((string)($record['password_fp']??''),hash('sha256',$passwordHash));
    if(!$valid){aih_remember_forget();return false;}
    // Rotate the persistent token after every automatic login to reduce replay risk.
    aih_remember_forget();
    session_regenerate_id(true);$_SESSION['auth_ok']=true;$_SESSION['csrf']=bin2hex(random_bytes(32));
    aih_remember_issue($passwordHash,30);aih_audit('remember_login_ok');
    return true;
}
function aih_csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return (string)$_SESSION['csrf']; }
function aih_check_csrf(?string $token): void { if (!$token || !hash_equals(aih_csrf(),$token)) { http_response_code(403); exit('Invalid CSRF token'); } }
function aih_json(array $data, int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); exit; }
function aih_json_input(): array { $raw=file_get_contents('php://input'); $v=json_decode((string)$raw,true); return is_array($v)?$v:[]; }

function aih_safe_name(string $name): string {
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9._-]+/','_', $name) ?: 'file';
    return substr($name,0,180);
}
function aih_new_id(string $prefix='f'): string { return $prefix.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(5)); }

function aih_chat_retention_seconds(): int { return max(86400,(int)(aih_config()['app']['chat_retention_seconds'] ?? 2592000)); }
function aih_file_retention_seconds(): int { return max(3600,(int)(aih_config()['app']['file_retention_seconds'] ?? 172800)); }
function aih_chat_history_path(): string { return AIH_CHATS.'/history.json'; }
function aih_chat_id(?string $id): string { $id=preg_replace('/[^a-zA-Z0-9_-]/','',(string)$id);return strlen($id)>=4&&strlen($id)<=80?$id:''; }
function aih_chat_history_load(?string $chatId=null): array {
    $chatId=aih_chat_id($chatId);$rows=aih_read_json(aih_chat_history_path(),[]);if(!is_array($rows))$rows=[];$cut=time()-aih_chat_retention_seconds();$changed=false;$out=[];
    foreach($rows as$row){if(!is_array($row)){$changed=true;continue;}$at=(int)($row['at']??0);if($at>0&&$at<$cut){$changed=true;continue;}if(empty($row['chat_id'])){$row['chat_id']='legacy';$changed=true;}$out[]=$row;}
    if($changed)aih_write_json(aih_chat_history_path(),array_slice($out,-2500));
    if($chatId!=='')$out=array_values(array_filter($out,fn($x)=>(string)($x['chat_id']??'legacy')===$chatId));
    return array_slice($out,-1500);
}
function aih_chat_history_add(string $role,string $content,string $meta='',string $kind='text',string $chatId=''): array {
    $role=in_array($role,['user','assistant'],true)?$role:'assistant';$content=trim($content);$chatId=aih_chat_id($chatId);if($chatId==='')$chatId=aih_new_id('chat');
    if($content==='')return ['chat_id'=>$chatId,'message'=>null];
    $row=['id'=>aih_new_id('msg'),'chat_id'=>$chatId,'at'=>time(),'role'=>$role,'content'=>substr($content,0,80000),'meta'=>substr(trim($meta),0,500),'kind'=>substr(preg_replace('/[^a-z0-9_-]/i','',$kind)?:'text',0,32)];
    aih_update_json(aih_chat_history_path(),function($rows)use($row){$rows=is_array($rows)?$rows:[];$cut=time()-aih_chat_retention_seconds();$rows=array_values(array_filter($rows,fn($x)=>is_array($x)&&(int)($x['at']??0)>=$cut));foreach($rows as&$r)if(is_array($r)&&empty($r['chat_id']))$r['chat_id']='legacy';unset($r);$rows[]=$row;return array_slice($rows,-2500);},[]);
    return ['chat_id'=>$chatId,'message'=>$row];
}
function aih_chat_history_clear(?string $chatId=null): void {
    $chatId=aih_chat_id($chatId);if($chatId===''){aih_write_json(aih_chat_history_path(),[]);return;}
    aih_update_json(aih_chat_history_path(),function($rows)use($chatId){$rows=is_array($rows)?$rows:[];return array_values(array_filter($rows,fn($x)=>!is_array($x)||((string)($x['chat_id']??'legacy')!==$chatId)));},[]);
}
function aih_chat_thread_list(int $limit=18): array {
    $rows=aih_chat_history_load();$threads=[];
    foreach($rows as$row){$id=(string)($row['chat_id']??'legacy');if(!isset($threads[$id]))$threads[$id]=['id'=>$id,'title'=>'New chat','created_at'=>(int)($row['at']??0),'updated_at'=>(int)($row['at']??0),'messages'=>0];$threads[$id]['updated_at']=max($threads[$id]['updated_at'],(int)($row['at']??0));$threads[$id]['messages']++;if(($row['role']??'')==='user'&&$threads[$id]['title']==='New chat'){$title=preg_replace('/\s+/u',' ',trim((string)($row['content']??'')));if($title!=='')$short=function_exists('mb_substr')?mb_substr($title,0,54):substr($title,0,54);$len=function_exists('mb_strlen')?mb_strlen($title):strlen($title);$threads[$id]['title']=$short.($len>54?'…':'');}}
    usort($threads,fn($a,$b)=>$b['updated_at']<=>$a['updated_at']);return array_slice($threads,0,max(1,min(50,$limit)));
}
function aih_chat_thread_delete(string $chatId): void { aih_chat_history_clear($chatId); }

function aih_cleanup_expired(bool $force=false): array {
    $cfg=aih_config();$stamp=AIH_STORAGE.'/last_cleanup.txt';
    if(!$force&&is_file($stamp)&&(time()-(int)@file_get_contents($stamp))<900)return ['deleted'=>0,'bytes'=>0,'chat_deleted'=>0,'skipped'=>true];
    @file_put_contents($stamp,(string)time(),LOCK_EX);$deleted=0;$fileDeleted=0;$cacheDeleted=0;$bytes=0;
    // User-facing temporary files expire after 48 hours by default. Settings, provider state, logs and chat storage are excluded.
    $fileCut=time()-aih_file_retention_seconds();
    foreach([AIH_UPLOADS,AIH_GENERATED,AIH_REPORTS] as$dir){
        foreach(glob($dir.'/*')?:[] as$path){if(!is_file($path))continue;$mt=@filemtime($path)?:time();if($mt<$fileCut){$sz=@filesize($path)?:0;if(@unlink($path)){$deleted++;$fileDeleted++;$bytes+=$sz;}}}
    }
    $cacheCut=time()-max(3600,(int)($cfg['app']['cache_retention_seconds']??86400));
    foreach(glob(AIH_CACHE.'/*')?:[] as$path){if(!is_file($path))continue;$mt=@filemtime($path)?:time();if($mt<$cacheCut){$sz=@filesize($path)?:0;if(@unlink($path)){$deleted++;$cacheDeleted++;$bytes+=$sz;}}}
    $before=aih_read_json(aih_chat_history_path(),[]);$beforeCount=is_array($before)?count($before):0;$after=aih_chat_history_load();$chatDeleted=max(0,$beforeCount-count($after));
    return ['deleted'=>$deleted,'file_deleted'=>$fileDeleted,'cache_deleted'=>$cacheDeleted,'bytes'=>$bytes,'chat_deleted'=>$chatDeleted,'skipped'=>false];
}

function aih_storage_stats(): array {
    $out=['files'=>0,'bytes'=>0,'by_bucket'=>[]];
    foreach (['uploads'=>AIH_UPLOADS,'generated'=>AIH_GENERATED,'reports'=>AIH_REPORTS,'cache'=>AIH_CACHE] as $k=>$dir) {
        $n=0;$b=0;
        foreach (glob($dir.'/*') ?: [] as $f) if (is_file($f)) {$n++;$b+=(int)(filesize($f)?:0);}
        $out['by_bucket'][$k]=['files'=>$n,'bytes'=>$b]; $out['files']+=$n; $out['bytes']+=$b;
    }
    return $out;
}

function aih_audit(string $event, array $context=[]): void {
    $row=['at'=>date(DATE_ATOM),'event'=>$event,'ip'=>substr((string)($_SERVER['REMOTE_ADDR']??''),0,64),'context'=>$context];
    $path=AIH_LOGS.'/audit.json';
    aih_update_json($path,function($rows)use($row){$rows=is_array($rows)?$rows:[];$rows[]=$row;return array_slice($rows,-500);},[]);
}

aih_bootstrap_storage();
aih_start_session();
if(!defined('AIH_SKIP_AUTO_CLEANUP')) aih_cleanup_expired(false);
