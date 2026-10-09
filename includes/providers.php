<?php
declare(strict_types=1);

function aih_provider_catalog(): array {
    return [
        'puter'=>[
            'name'=>'Puter AI','kind'=>'no-key-browser','needs_key'=>false,'accepts_key'=>false,'client_side'=>true,
            'note'=>'No developer API key. Task-aware routing can use hundreds of current Puter chat models plus image/video providers; user allowance/account quota applies.',
            'connect_url'=>'https://puter.com/','connect_label'=>'Connect'
        ],
        'ollama_local'=>[
            'name'=>'Ollama Local','kind'=>'local','needs_key'=>false,'accepts_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Run open models locally or on your own server.',
            'connect_url'=>'https://ollama.com/download','connect_label'=>'Setup'
        ],
        'lmstudio_local'=>[
            'name'=>'LM Studio Local','kind'=>'local','needs_key'=>false,'accepts_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Uses the LM Studio OpenAI-compatible local server.',
            'connect_url'=>'https://lmstudio.ai/','connect_label'=>'Setup'
        ],
        'llamacpp_local'=>[
            'name'=>'llama.cpp Local','kind'=>'local','needs_key'=>false,'accepts_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Uses a local llama.cpp OpenAI-compatible server.',
            'connect_url'=>'https://github.com/ggml-org/llama.cpp','connect_label'=>'Setup'
        ],
        'localai_local'=>[
            'name'=>'LocalAI Local','kind'=>'local','needs_key'=>false,'accepts_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Self-hosted OpenAI-compatible inference.',
            'connect_url'=>'https://localai.io/','connect_label'=>'Setup'
        ],
        'aihorde_text'=>[
            'name'=>'AI Horde Text · community queue','kind'=>'free-key','needs_key'=>false,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Free community-powered text generation. Works anonymously with no key; an optional free AI Horde key improves queue priority. Prompts are processed by volunteer workers, so do not send confidential data.',
            'connect_url'=>'https://aihorde.net/register','connect_label'=>'Optional free key'
        ],
        'webllm_browser'=>[
            'name'=>'Browser AI / WebLLM','kind'=>'no-key-browser','needs_key'=>false,'accepts_key'=>false,'client_side'=>true,
            'note'=>'No account or API key. Runs in a WebGPU-capable browser after the model download.',
            'connect_url'=>'https://webllm.mlc.ai/','connect_label'=>'Load'
        ],
        'groq'=>[
            'name'=>'Groq Free API','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Free developer plan with rate limits. Good for fast open-model inference.',
            'connect_url'=>'https://console.groq.com/keys','connect_label'=>'Get free key'
        ],
        'gemini'=>[
            'name'=>'Google Gemini Free','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Google AI Studio free tier where available; quotas and model availability apply.',
            'connect_url'=>'https://aistudio.google.com/app/apikey','connect_label'=>'Get free key'
        ],
        'openrouter'=>[
            'name'=>'OpenRouter Free Models','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Use the openrouter/free router or another :free model. Free limits apply.',
            'connect_url'=>'https://openrouter.ai/settings/keys','connect_label'=>'Get free key'
        ],
        'huggingface'=>[
            'name'=>'Hugging Face Inference','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Inference Providers include small monthly free credits and route to many model families.',
            'connect_url'=>'https://huggingface.co/settings/tokens','connect_label'=>'Get free token'
        ],
        'mistral'=>[
            'name'=>'Mistral API','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Mistral Free mode includes API usage for evaluation/prototyping within account limits.',
            'connect_url'=>'https://console.mistral.ai/api-keys','connect_label'=>'Get free key'
        ],
        'cloudflare'=>[
            'name'=>'Cloudflare Workers AI','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'requires_account_id'=>true,'client_side'=>false,
            'note'=>'Workers Free includes a daily Workers AI allocation. Requires a Cloudflare Account ID and Workers AI API token; quota/capacity errors automatically fall through.',
            'connect_url'=>'https://dash.cloudflare.com/','connect_label'=>'Get API token'
        ],
        'vercel_gateway'=>[
            'name'=>'Vercel AI Gateway · Claude + more','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'OpenAI-compatible AI Gateway with Claude and many other models. Free Vercel users currently receive recurring AI Gateway credits; when credits are exhausted the router falls through.',
            'connect_url'=>'https://vercel.com/ai-gateway','connect_label'=>'Get gateway key'
        ],
        'nvidia_nim'=>[
            'name'=>'NVIDIA NIM Developer API','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'NVIDIA Developer Program offers hosted NIM API access for prototyping/development. Production terms differ; quota/auth errors automatically fall through.',
            'connect_url'=>'https://build.nvidia.com/','connect_label'=>'Get developer key'
        ],
        'ollama_cloud'=>[
            'name'=>'Ollama Cloud','kind'=>'free-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Optional Ollama account/cloud access; quotas depend on the current account plan.',
            'connect_url'=>'https://ollama.com/','connect_label'=>'Connect'
        ],
        'pollinations'=>[
            'name'=>'Pollinations','kind'=>'credit-key','needs_key'=>true,'accepts_key'=>true,'client_side'=>false,
            'note'=>'Optional image/text provider. Account/key/credits may be required depending on current service rules.',
            'connect_url'=>'https://pollinations.ai/','connect_label'=>'Open'
        ],
        'custom_openai'=>[
            'name'=>'Custom OpenAI-compatible','kind'=>'custom','needs_key'=>false,'accepts_key'=>true,'requires_url'=>true,'client_side'=>false,
            'note'=>'Connect another local or free OpenAI-compatible endpoint. API key is optional.',
            'connect_url'=>'','connect_label'=>'Configure'
        ],
    ];
}

function aih_model_presets(): array {
    return [
        ['family'=>'Auto · best installed local model','provider'=>'ollama_local','model'=>'auto','command'=>'Install one or more presets below; Auto selects by task'],
        ['family'=>'GPT-OSS 20B · reasoning / agents','provider'=>'ollama_local','model'=>'gpt-oss:20b','command'=>'ollama pull gpt-oss:20b'],
        ['family'=>'Qwen3-Coder 30B · coding / agents','provider'=>'ollama_local','model'=>'qwen3-coder:30b','command'=>'ollama pull qwen3-coder:30b'],
        ['family'=>'Qwen3 8B · balanced','provider'=>'ollama_local','model'=>'qwen3:8b','command'=>'ollama pull qwen3:8b'],
        ['family'=>'Gemma 3 12B · multimodal capable','provider'=>'ollama_local','model'=>'gemma3:12b','command'=>'ollama pull gemma3:12b'],
        ['family'=>'Gemma 3 4B · lighter local','provider'=>'ollama_local','model'=>'gemma3:4b','command'=>'ollama pull gemma3:4b'],
        ['family'=>'DeepSeek R1 8B · reasoning','provider'=>'ollama_local','model'=>'deepseek-r1:8b','command'=>'ollama pull deepseek-r1:8b'],
        ['family'=>'Mistral 7B · light / general','provider'=>'ollama_local','model'=>'mistral:7b','command'=>'ollama pull mistral:7b'],
        ['family'=>'Llama 3.2 3B · low-memory','provider'=>'ollama_local','model'=>'llama3.2:3b','command'=>'ollama pull llama3.2:3b'],
    ];
}

function aih_provider_state(): array { $v=aih_read_json(AIH_STORAGE.'/provider_state.json',[]);return is_array($v)?$v:[]; }
function aih_mark_provider_state(string $id,array $patch): void {
    aih_update_json(AIH_STORAGE.'/provider_state.json',function($s)use($id,$patch){$s=is_array($s)?$s:[];$s[$id]=array_merge(is_array($s[$id]??null)?$s[$id]:[],$patch);return $s;},[]);
}
function aih_retry_seconds(array $headers,int $default=900): int {
    $r=(string)($headers['retry-after']??''); if($r!==''&&ctype_digit($r))return max(5,min(86400,(int)$r));
    $reset=(string)($headers['x-ratelimit-reset-requests']??'');
    if(preg_match('/(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)(?:\.\d+)?s)?/',$reset,$m)){$sec=((int)($m[1]??0))*3600+((int)($m[2]??0))*60+(int)($m[3]??0);if($sec>0)return min(86400,$sec);}
    return $default;
}

function aih_openai_call(string $id,string $url,string $key,string $model,array $messages,array $extraHeaders=[],int $timeout=90): array {
    $headers=array_merge($extraHeaders,['Content-Type: application/json']); if($key!=='')$headers[]='Authorization: Bearer '.$key;
    $payload=['messages'=>$messages,'temperature'=>0.3,'stream'=>false];
    if(trim($model)!=='')$payload['model']=$model;
    $r=aih_http_json('POST',$url,$payload,$headers,$timeout);
    if($r['code']>=200&&$r['code']<300){$j=json_decode($r['body'],true);$text=(string)($j['choices'][0]['message']['content']??'');if(trim($text)!=='')return ['ok'=>true,'text'=>trim($text),'headers'=>$r['headers']];}
    return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($r['body']?:$r['error'],0,500)),'headers'=>$r['headers']];
}

function aih_horde_text_call(array $messages,array $settings): array {
    $p=$settings['providers']??[];$keys=$p['keys']??[];$models=$p['models']??[];
    $key=trim((string)($keys['aihorde_text']??''));
    if($key==='')$key=trim((string)(($settings['images']??[])['aihorde_api_key']??''));
    if($key==='')$key='0000000000';
    $model=trim((string)($models['aihorde_text']??''));
    $parts=[];
    foreach($messages as $m){$role=strtolower((string)($m['role']??'user'));$label=$role==='assistant'?'Assistant':($role==='system'?'System':'User');$parts[]=$label.":\n".(string)($m['content']??'');}
    $prompt=trim(implode("\n\n",$parts))."\n\nAssistant:\n";
    // Community workers have varying context limits; keep the newest useful context.
    if(strlen($prompt)>14000)$prompt=substr($prompt,-14000);
    $payload=[
        'prompt'=>$prompt,
        'params'=>[
            'n'=>1,'max_context_length'=>2048,'max_length'=>512,'temperature'=>0.7,
            'top_p'=>0.9,'top_k'=>0,'rep_pen'=>1.08,'frmttriminc'=>true,
        ],
        'trusted_workers'=>false,'slow_workers'=>true,
    ];
    if($model!=='')$payload['models']=[$model];
    $client='FreeAIHub63:'.AIH_VERSION.':https://ai.63.lv/';
    $headers=['apikey: '.$key,'Client-Agent: '.$client];
    $start=aih_http_json('POST','https://aihorde.net/api/v2/generate/text/async',$payload,$headers,30);
    $j=json_decode((string)$start['body'],true);$requestId=trim((string)($j['id']??''));
    if($start['code']<200||$start['code']>=300||$requestId===''){
        $err=is_array($j)?((string)($j['message']??$j['error']??'')):(string)$start['body'];
        return ['ok'=>false,'code'=>$start['code'],'error'=>trim(substr($err?:$start['error']?:'AI Horde text request was not accepted',0,600)),'headers'=>$start['headers']??[]];
    }
    $deadline=microtime(true)+60;
    while(microtime(true)<$deadline){
        usleep(2000000);
        $status=aih_http('GET','https://aihorde.net/api/v2/generate/text/status/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],25);
        if($status['code']<200||$status['code']>=300){if($status['code']>=500||$status['code']===0)continue;return ['ok'=>false,'code'=>$status['code'],'error'=>trim(substr((string)($status['body']?:$status['error']?:'AI Horde text status failed'),0,600)),'headers'=>$status['headers']??[]];}
        $sj=json_decode((string)$status['body'],true);$text=trim((string)($sj['generations'][0]['text']??''));
        if($text!=='')return ['ok'=>true,'text'=>$text,'headers'=>$status['headers']??[]];
        if(!empty($sj['faulted']))return ['ok'=>false,'code'=>503,'error'=>'AI Horde text generation faulted','headers'=>$status['headers']??[]];
        if(!empty($sj['done'])&&$text==='')return ['ok'=>false,'code'=>503,'error'=>'AI Horde text generation completed without text','headers'=>$status['headers']??[]];
    }
    return ['ok'=>false,'code'=>504,'error'=>'AI Horde text community queue timed out after 60 seconds','headers'=>[]];
}

function aih_ollama_native_base(string $chatUrl): string {
    $u=parse_url(trim($chatUrl));
    if(!is_array($u)||empty($u['host']))return '';
    $scheme=(string)($u['scheme']??'http');$host=(string)$u['host'];
    if(str_contains($host,':')&&!str_starts_with($host,'['))$host='['.$host.']';
    $port=isset($u['port'])?':'.(int)$u['port']:'';
    return $scheme.'://'.$host.$port;
}

function aih_ollama_installed_models(string $chatUrl): array {
    $base=aih_ollama_native_base($chatUrl);if($base==='')return [];
    $r=aih_http('GET',$base.'/api/tags',null,['Accept: application/json'],5);
    if($r['code']<200||$r['code']>=300)return [];
    $j=json_decode((string)$r['body'],true);$out=[];
    foreach((array)($j['models']??[])as$row){if(!is_array($row))continue;$name=trim((string)($row['name']??$row['model']??''));if($name!==''&&!in_array($name,$out,true))$out[]=$name;}
    return $out;
}

function aih_local_model_score(string $name,string $task='chat'): float {
    $n=strtolower($name);$task=strtolower($task);$score=0.0;$coder=str_contains($n,'qwen3-coder');
    if($task==='code'){
        if($coder)$score=1200;
        elseif(str_contains($n,'gpt-oss'))$score=1050;
        elseif(str_contains($n,'qwen3'))$score=900;
        elseif(str_contains($n,'deepseek'))$score=820;
        elseif(str_contains($n,'gemma3'))$score=760;
    }elseif(in_array($task,['research','compare'],true)){
        if(str_contains($n,'gpt-oss'))$score=1200;
        elseif($coder)$score=780;
        elseif(str_contains($n,'qwen3'))$score=1020;
        elseif(str_contains($n,'deepseek'))$score=930;
        elseif(str_contains($n,'gemma3'))$score=850;
    }else{
        if(str_contains($n,'gpt-oss'))$score=1150;
        elseif($coder)$score=850;
        elseif(str_contains($n,'qwen3'))$score=1000;
        elseif(str_contains($n,'gemma3'))$score=900;
        elseif(str_contains($n,'deepseek'))$score=820;
    }
    if($score===0.0&&str_contains($n,'mistral'))$score=650;
    if($score===0.0&&str_contains($n,'llama'))$score=620;
    if(preg_match('/(\d+(?:\.\d+)?)b\b/i',$n,$m))$score+=min(240.0,(float)$m[1]*4.0);
    if(str_contains($n,'latest'))$score+=10;
    return $score;
}

function aih_select_ollama_model(string $chatUrl,string $task='chat',string $fallback='qwen3:8b'): string {
    $rows=aih_ollama_installed_models($chatUrl);if(!$rows)return $fallback;
    $best='';$bestScore=-INF;
    foreach($rows as$name){$score=aih_local_model_score((string)$name,$task);if($score>$bestScore){$bestScore=$score;$best=(string)$name;}}
    return $best!==''?$best:$fallback;
}

function aih_provider_call(string $id,array $messages,array $settings,string $task='chat'): array {
    $p=$settings['providers']??[]; $keys=$p['keys']??[]; $models=$p['models']??[]; $model=(string)($models[$id]??'');
    if(in_array($id,['puter','webllm_browser'],true))return ['ok'=>false,'code'=>0,'error'=>'Client-side provider','headers'=>[]];
    if($id==='ollama_local'){$url=(string)($p['ollama_local_url']??'http://127.0.0.1:11434/v1/chat/completions');if($model===''||strtolower($model)==='auto')$model=aih_select_ollama_model($url,$task,'qwen3:8b');$r=aih_openai_call($id,$url,'',$model,$messages,[],18);$r['model']=$model;return $r;}
    if($id==='lmstudio_local') return aih_openai_call($id,(string)($p['lmstudio_local_url']??'http://127.0.0.1:1234/v1/chat/completions'),'',$model,$messages,[],12);
    if($id==='llamacpp_local') return aih_openai_call($id,(string)($p['llamacpp_local_url']??'http://127.0.0.1:8080/v1/chat/completions'),'',$model,$messages,[],12);
    if($id==='localai_local') return aih_openai_call($id,(string)($p['localai_local_url']??'http://127.0.0.1:8080/v1/chat/completions'),'',$model,$messages,[],12);
    if($id==='aihorde_text') return aih_horde_text_call($messages,$settings);
    if($id==='groq') return aih_openai_call($id,'https://api.groq.com/openai/v1/chat/completions',(string)($keys['groq']??''),$model,$messages);
    if($id==='openrouter') return aih_openai_call($id,'https://openrouter.ai/api/v1/chat/completions',(string)($keys['openrouter']??''),$model,$messages,['HTTP-Referer: '.((!empty($_SERVER['HTTP_HOST']))?'https://'.$_SERVER['HTTP_HOST']:'https://localhost'),'X-Title: Free AI Hub 63']);
    if($id==='huggingface') return aih_openai_call($id,'https://router.huggingface.co/v1/chat/completions',(string)($keys['huggingface']??''),$model,$messages);
    if($id==='mistral') return aih_openai_call($id,'https://api.mistral.ai/v1/chat/completions',(string)($keys['mistral']??''),$model,$messages);
    if($id==='pollinations') return aih_openai_call($id,'https://gen.pollinations.ai/v1/chat/completions',(string)($keys['pollinations']??''),$model,$messages);
    if($id==='cloudflare'){
        $account=trim((string)($p['cloudflare_account_id']??''));
        if($account==='')return ['ok'=>false,'code'=>0,'error'=>'Cloudflare Account ID missing','headers'=>[]];
        $url='https://api.cloudflare.com/client/v4/accounts/'.rawurlencode($account).'/ai/v1/chat/completions';
        return aih_openai_call($id,$url,(string)($keys['cloudflare']??''),$model,$messages);
    }
    if($id==='vercel_gateway') return aih_openai_call($id,'https://ai-gateway.vercel.sh/v1/chat/completions',(string)($keys['vercel_gateway']??''),$model,$messages);
    if($id==='nvidia_nim') return aih_openai_call($id,'https://integrate.api.nvidia.com/v1/chat/completions',(string)($keys['nvidia_nim']??''),$model,$messages);
    if($id==='custom_openai'){
        $url=trim((string)($p['custom_openai_url']??'')); if($url==='')return ['ok'=>false,'code'=>0,'error'=>'Custom endpoint URL missing','headers'=>[]];
        return aih_openai_call($id,$url,(string)($keys['custom_openai']??''),$model,$messages);
    }
    if($id==='gemini'){
        $key=(string)($keys['gemini']??''); $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent?key='.urlencode($key);
        $sys='';$contents=[];foreach($messages as $m){if(($m['role']??'')==='system'){$sys.=(string)$m['content']."\n";continue;}$contents[]=['role'=>(($m['role']??'')==='assistant'?'model':'user'),'parts'=>[['text'=>(string)($m['content']??'')]]];}
        $body=['contents'=>$contents]; if(trim($sys)!=='')$body['systemInstruction']=['parts'=>[['text'=>trim($sys)]]];
        $r=aih_http_json('POST',$url,$body,[],90); if($r['code']>=200&&$r['code']<300){$j=json_decode($r['body'],true);$txt='';foreach(($j['candidates'][0]['content']['parts']??[])as $part)$txt.=(string)($part['text']??'');if(trim($txt)!=='')return ['ok'=>true,'text'=>trim($txt),'headers'=>$r['headers']];}
        return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($r['body']?:$r['error'],0,500)),'headers'=>$r['headers']];
    }
    if($id==='ollama_cloud'){
        $key=(string)($keys['ollama_cloud']??'');$r=aih_http_json('POST','https://ollama.com/api/chat',['model'=>$model,'messages'=>$messages,'stream'=>false],['Authorization: Bearer '.$key],90);
        if($r['code']>=200&&$r['code']<300){$j=json_decode($r['body'],true);$txt=(string)($j['message']['content']??'');if(trim($txt)!=='')return ['ok'=>true,'text'=>trim($txt),'headers'=>$r['headers']];}
        return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($r['body']?:$r['error'],0,500)),'headers'=>$r['headers']];
    }
    return ['ok'=>false,'code'=>0,'error'=>'Unknown provider','headers'=>[]];
}

function aih_ai_chat(array $messages,?array $settings=null,string $task='chat'): array {
    $settings=$settings??aih_settings();$catalog=aih_provider_catalog();$p=$settings['providers']??[];$order=$p['order']??array_keys($catalog);$enabled=$p['enabled']??[];$keys=$p['keys']??[];$state=aih_provider_state();$tried=[];$clientFallbacks=[];
    foreach($order as $id){
        if(empty($enabled[$id])||!isset($catalog[$id]))continue;
        if(!empty($catalog[$id]['client_side'])){$clientFallbacks[]=$id;$tried[]=['id'=>$id,'status'=>'client_side'];continue;}
        $cool=(int)($state[$id]['cooldown_until']??0); if($cool>time()){$tried[]=['id'=>$id,'status'=>'cooldown','until'=>$cool];continue;}
        if(!empty($catalog[$id]['needs_key'])&&trim((string)($keys[$id]??''))===''){$tried[]=['id'=>$id,'status'=>'optional_key_not_set'];continue;}
        if(!empty($catalog[$id]['requires_url'])){$urlKey=$id.'_url';if(trim((string)($p[$urlKey]??''))===''){$tried[]=['id'=>$id,'status'=>'endpoint_not_set'];continue;}}
        if(!empty($catalog[$id]['requires_account_id'])&&trim((string)($p['cloudflare_account_id']??''))===''){$tried[]=['id'=>$id,'status'=>'account_id_not_set'];continue;}
        $res=aih_provider_call($id,$messages,$settings,$task);
        if(!empty($res['ok'])){aih_mark_provider_state($id,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);return ['ok'=>true,'text'=>$res['text'],'provider'=>$id,'provider_name'=>$catalog[$id]['name'],'model'=>(string)($res['model']??(($p['models']??[])[$id]??'')),'tried'=>$tried,'client_fallbacks'=>$clientFallbacks];}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'status'=>'failed','code'=>$code,'error'=>$res['error']??''];
        if($code===429){$sec=aih_retry_seconds($res['headers']??[],900);$until=time()+$sec;aih_mark_provider_state($id,['last_error'=>'HTTP 429','cooldown_until'=>$until]);$entry['status']='rate_limited';$entry['until']=$until;}
        elseif(in_array($code,[401,402,403],true)){aih_mark_provider_state($id,['last_error'=>'HTTP '.$code,'cooldown_until'=>time()+3600]);$entry['status']='auth_or_quota';$entry['until']=time()+3600;}
        elseif($code===0){$until=time()+300;aih_mark_provider_state($id,['last_error'=>'connection_failed '.substr((string)($res['error']??''),0,120),'cooldown_until'=>$until]);$entry['status']='unavailable';$entry['until']=$until;}
        else {aih_mark_provider_state($id,['last_error'=>'HTTP '.$code.' '.substr((string)($res['error']??''),0,120),'cooldown_until'=>0]);}
        $tried[]=$entry;
    }
    return [
        'ok'=>false,
        'error'=>'No server-side provider answered. Auto also includes the free AI Horde community queue; the browser can then try WebLLM and only use Puter while free allowance remains.',
        'tried'=>$tried,
        'client_fallbacks'=>$clientFallbacks,
    ];
}


function aih_ai_compare(array $messages,?array $settings=null,int $max=3): array {
    $settings=$settings??aih_settings();$catalog=aih_provider_catalog();$p=$settings['providers']??[];$order=$p['order']??array_keys($catalog);$enabled=$p['enabled']??[];$keys=$p['keys']??[];$state=aih_provider_state();$answers=[];$tried=[];
    foreach($order as $id){
        if(count($answers)>=$max)break;
        if(empty($enabled[$id])||!isset($catalog[$id])||!empty($catalog[$id]['client_side']))continue;
        $cool=(int)($state[$id]['cooldown_until']??0);if($cool>time()){$tried[]=['id'=>$id,'status'=>'cooldown'];continue;}
        if(!empty($catalog[$id]['needs_key'])&&trim((string)($keys[$id]??''))===''){$tried[]=['id'=>$id,'status'=>'optional_key_not_set'];continue;}
        if(!empty($catalog[$id]['requires_url'])){$urlKey=$id.'_url';if(trim((string)($p[$urlKey]??''))===''){$tried[]=['id'=>$id,'status'=>'endpoint_not_set'];continue;}}
        if(!empty($catalog[$id]['requires_account_id'])&&trim((string)($p['cloudflare_account_id']??''))===''){$tried[]=['id'=>$id,'status'=>'account_id_not_set'];continue;}
        $res=aih_provider_call($id,$messages,$settings,'compare');
        if(!empty($res['ok'])){$answers[]=['provider'=>$id,'provider_name'=>$catalog[$id]['name'],'model'=>(string)($res['model']??(($p['models']??[])[$id]??'')),'text'=>$res['text']];aih_mark_provider_state($id,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);$tried[]=['id'=>$id,'status'=>'ok'];continue;}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'status'=>'failed','code'=>$code];
        if($code===429){$sec=aih_retry_seconds($res['headers']??[],900);$entry['status']='rate_limited';$entry['until']=time()+$sec;aih_mark_provider_state($id,['last_error'=>'HTTP 429','cooldown_until'=>$entry['until']]);}
        elseif(in_array($code,[401,402,403],true)){$entry['status']='auth_or_quota';$entry['until']=time()+3600;aih_mark_provider_state($id,['last_error'=>'HTTP '.$code,'cooldown_until'=>$entry['until']]);}
        elseif($code===0){$entry['status']='unavailable';$entry['until']=time()+300;aih_mark_provider_state($id,['last_error'=>'connection_failed '.substr((string)($res['error']??''),0,120),'cooldown_until'=>$entry['until']]);}
        $tried[]=$entry;
    }
    return ['ok'=>count($answers)>0,'answers'=>$answers,'tried'=>$tried];
}

function aih_provider_public_status(): array {
    $s=aih_settings();$cat=aih_provider_catalog();$state=aih_provider_state();$p=$s['providers']??[];$out=[];
    foreach(($p['order']??array_keys($cat))as$id){
        if(!isset($cat[$id]))continue;
        $hasKey=trim((string)(($p['keys']??[])[$id]??''))!=='';
        $configured=!$cat[$id]['needs_key']||$hasKey; if(!empty($cat[$id]['requires_url'])){$urlKey=$id.'_url';$configured=trim((string)($p[$urlKey]??''))!=='';} if(!empty($cat[$id]['requires_account_id']))$configured=$configured&&trim((string)($p['cloudflare_account_id']??''))!=='';
        $out[]=[
            'id'=>$id,'name'=>$cat[$id]['name'],'kind'=>$cat[$id]['kind'],'enabled'=>!empty(($p['enabled']??[])[$id]),
            'configured'=>$configured,'needs_key'=>$cat[$id]['needs_key'],'client_side'=>!empty($cat[$id]['client_side']),
            'model'=>(string)(($p['models']??[])[$id]??''),'cooldown_until'=>(int)($state[$id]['cooldown_until']??0),
            'last_ok'=>(int)($state[$id]['last_ok']??0),'last_error'=>(string)($state[$id]['last_error']??''),'note'=>$cat[$id]['note'],
            'connect_url'=>(string)($cat[$id]['connect_url']??''),'connect_label'=>(string)($cat[$id]['connect_label']??'Connect')
        ];
    }
    return $out;
}
