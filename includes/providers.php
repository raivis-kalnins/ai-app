<?php
declare(strict_types=1);

function aih_provider_catalog(): array {
    return [
        'puter'=>[
            'name'=>'Puter AI','kind'=>'no-key-browser','needs_key'=>false,'client_side'=>true,
            'note'=>'No developer API key. Task-aware routing can use hundreds of current Puter chat models plus image/video providers; user allowance/account quota applies.',
            'connect_url'=>'https://puter.com/','connect_label'=>'Connect'
        ],
        'ollama_local'=>[
            'name'=>'Ollama Local','kind'=>'local','needs_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Run open models locally or on your own server.',
            'connect_url'=>'https://ollama.com/download','connect_label'=>'Setup'
        ],
        'lmstudio_local'=>[
            'name'=>'LM Studio Local','kind'=>'local','needs_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Uses the LM Studio OpenAI-compatible local server.',
            'connect_url'=>'https://lmstudio.ai/','connect_label'=>'Setup'
        ],
        'llamacpp_local'=>[
            'name'=>'llama.cpp Local','kind'=>'local','needs_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Uses a local llama.cpp OpenAI-compatible server.',
            'connect_url'=>'https://github.com/ggml-org/llama.cpp','connect_label'=>'Setup'
        ],
        'localai_local'=>[
            'name'=>'LocalAI Local','kind'=>'local','needs_key'=>false,'client_side'=>false,
            'note'=>'No cloud key. Self-hosted OpenAI-compatible inference.',
            'connect_url'=>'https://localai.io/','connect_label'=>'Setup'
        ],
        'webllm_browser'=>[
            'name'=>'Browser AI / WebLLM','kind'=>'no-key-browser','needs_key'=>false,'client_side'=>true,
            'note'=>'No account or API key. Runs in a WebGPU-capable browser after the model download.',
            'connect_url'=>'https://webllm.mlc.ai/','connect_label'=>'Load'
        ],
        'groq'=>[
            'name'=>'Groq Free API','kind'=>'free-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Free developer plan with rate limits. Good for fast open-model inference.',
            'connect_url'=>'https://console.groq.com/keys','connect_label'=>'Get free key'
        ],
        'gemini'=>[
            'name'=>'Google Gemini Free','kind'=>'free-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Google AI Studio free tier where available; quotas and model availability apply.',
            'connect_url'=>'https://aistudio.google.com/app/apikey','connect_label'=>'Get free key'
        ],
        'openrouter'=>[
            'name'=>'OpenRouter Free Models','kind'=>'free-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Use the openrouter/free router or another :free model. Free limits apply.',
            'connect_url'=>'https://openrouter.ai/settings/keys','connect_label'=>'Get free key'
        ],
        'huggingface'=>[
            'name'=>'Hugging Face Inference','kind'=>'free-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Inference Providers include small monthly free credits and route to many model families.',
            'connect_url'=>'https://huggingface.co/settings/tokens','connect_label'=>'Get free token'
        ],
        'mistral'=>[
            'name'=>'Mistral API','kind'=>'free-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Mistral Free mode includes API usage for evaluation/prototyping within account limits.',
            'connect_url'=>'https://console.mistral.ai/api-keys','connect_label'=>'Get free key'
        ],
        'ollama_cloud'=>[
            'name'=>'Ollama Cloud','kind'=>'free-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Optional Ollama account/cloud access; quotas depend on the current account plan.',
            'connect_url'=>'https://ollama.com/','connect_label'=>'Connect'
        ],
        'pollinations'=>[
            'name'=>'Pollinations','kind'=>'credit-key','needs_key'=>true,'client_side'=>false,
            'note'=>'Optional image/text provider. Account/key/credits may be required depending on current service rules.',
            'connect_url'=>'https://pollinations.ai/','connect_label'=>'Open'
        ],
        'custom_openai'=>[
            'name'=>'Custom OpenAI-compatible','kind'=>'custom','needs_key'=>false,'client_side'=>false,
            'note'=>'Connect another local or free OpenAI-compatible endpoint. API key is optional.',
            'connect_url'=>'','connect_label'=>'Configure'
        ],
    ];
}

function aih_model_presets(): array {
    return [
        ['family'=>'Llama 3.x (Meta)','provider'=>'ollama_local','model'=>'llama3.2:3b','command'=>'ollama pull llama3.2:3b'],
        ['family'=>'Gemma (Google)','provider'=>'ollama_local','model'=>'gemma3:4b','command'=>'ollama pull gemma3:4b'],
        ['family'=>'Mistral 7B','provider'=>'ollama_local','model'=>'mistral:7b','command'=>'ollama pull mistral:7b'],
        ['family'=>'DeepSeek','provider'=>'ollama_local','model'=>'deepseek-r1:8b','command'=>'ollama pull deepseek-r1:8b'],
        ['family'=>'Qwen','provider'=>'ollama_local','model'=>'qwen3:8b','command'=>'ollama pull qwen3:8b'],
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

function aih_openai_call(string $id,string $url,string $key,string $model,array $messages,array $extraHeaders=[]): array {
    $headers=array_merge($extraHeaders,['Content-Type: application/json']); if($key!=='')$headers[]='Authorization: Bearer '.$key;
    $payload=['messages'=>$messages,'temperature'=>0.3,'stream'=>false];
    if(trim($model)!=='')$payload['model']=$model;
    $r=aih_http_json('POST',$url,$payload,$headers,90);
    if($r['code']>=200&&$r['code']<300){$j=json_decode($r['body'],true);$text=(string)($j['choices'][0]['message']['content']??'');if(trim($text)!=='')return ['ok'=>true,'text'=>trim($text),'headers'=>$r['headers']];}
    return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($r['body']?:$r['error'],0,500)),'headers'=>$r['headers']];
}

function aih_provider_call(string $id,array $messages,array $settings): array {
    $p=$settings['providers']??[]; $keys=$p['keys']??[]; $models=$p['models']??[]; $model=(string)($models[$id]??'');
    if(in_array($id,['puter','webllm_browser'],true))return ['ok'=>false,'code'=>0,'error'=>'Client-side provider','headers'=>[]];
    if($id==='ollama_local') return aih_openai_call($id,(string)($p['ollama_local_url']??'http://127.0.0.1:11434/v1/chat/completions'),'',$model,$messages);
    if($id==='lmstudio_local') return aih_openai_call($id,(string)($p['lmstudio_local_url']??'http://127.0.0.1:1234/v1/chat/completions'),'',$model,$messages);
    if($id==='llamacpp_local') return aih_openai_call($id,(string)($p['llamacpp_local_url']??'http://127.0.0.1:8080/v1/chat/completions'),'',$model,$messages);
    if($id==='localai_local') return aih_openai_call($id,(string)($p['localai_local_url']??'http://127.0.0.1:8080/v1/chat/completions'),'',$model,$messages);
    if($id==='groq') return aih_openai_call($id,'https://api.groq.com/openai/v1/chat/completions',(string)($keys['groq']??''),$model,$messages);
    if($id==='openrouter') return aih_openai_call($id,'https://openrouter.ai/api/v1/chat/completions',(string)($keys['openrouter']??''),$model,$messages,['HTTP-Referer: '.((!empty($_SERVER['HTTP_HOST']))?'https://'.$_SERVER['HTTP_HOST']:'https://localhost'),'X-Title: Free AI Hub 63']);
    if($id==='huggingface') return aih_openai_call($id,'https://router.huggingface.co/v1/chat/completions',(string)($keys['huggingface']??''),$model,$messages);
    if($id==='mistral') return aih_openai_call($id,'https://api.mistral.ai/v1/chat/completions',(string)($keys['mistral']??''),$model,$messages);
    if($id==='pollinations') return aih_openai_call($id,'https://gen.pollinations.ai/v1/chat/completions',(string)($keys['pollinations']??''),$model,$messages);
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

function aih_ai_chat(array $messages,?array $settings=null): array {
    $settings=$settings??aih_settings();$catalog=aih_provider_catalog();$p=$settings['providers']??[];$order=$p['order']??array_keys($catalog);$enabled=$p['enabled']??[];$keys=$p['keys']??[];$state=aih_provider_state();$tried=[];$clientFallbacks=[];
    foreach($order as $id){
        if(empty($enabled[$id])||!isset($catalog[$id]))continue;
        if(!empty($catalog[$id]['client_side'])){$clientFallbacks[]=$id;$tried[]=['id'=>$id,'status'=>'client_side'];continue;}
        $cool=(int)($state[$id]['cooldown_until']??0); if($cool>time()){$tried[]=['id'=>$id,'status'=>'cooldown','until'=>$cool];continue;}
        if(!empty($catalog[$id]['needs_key'])&&trim((string)($keys[$id]??''))===''){$tried[]=['id'=>$id,'status'=>'optional_key_not_set'];continue;}
        $res=aih_provider_call($id,$messages,$settings);
        if(!empty($res['ok'])){aih_mark_provider_state($id,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);return ['ok'=>true,'text'=>$res['text'],'provider'=>$id,'provider_name'=>$catalog[$id]['name'],'tried'=>$tried,'client_fallbacks'=>$clientFallbacks];}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'status'=>'failed','code'=>$code,'error'=>$res['error']??''];
        if($code===429){$sec=aih_retry_seconds($res['headers']??[],900);$until=time()+$sec;aih_mark_provider_state($id,['last_error'=>'HTTP 429','cooldown_until'=>$until]);$entry['status']='rate_limited';$entry['until']=$until;}
        elseif(in_array($code,[401,402,403],true)){aih_mark_provider_state($id,['last_error'=>'HTTP '.$code,'cooldown_until'=>time()+3600]);$entry['status']='auth_or_quota';$entry['until']=time()+3600;}
        else {aih_mark_provider_state($id,['last_error'=>'HTTP '.$code.' '.substr((string)($res['error']??''),0,120),'cooldown_until'=>0]);}
        $tried[]=$entry;
    }
    return [
        'ok'=>false,
        'error'=>'No server-side provider answered. Puter AI, Browser AI/WebLLM and local engines work without a developer cloud API key.',
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
        $res=aih_provider_call($id,$messages,$settings);
        if(!empty($res['ok'])){$answers[]=['provider'=>$id,'provider_name'=>$catalog[$id]['name'],'model'=>(string)(($p['models']??[])[$id]??''),'text'=>$res['text']];aih_mark_provider_state($id,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);$tried[]=['id'=>$id,'status'=>'ok'];continue;}
        $code=(int)($res['code']??0);$tried[]=['id'=>$id,'status'=>$code===429?'rate_limited':'failed','code'=>$code];
        if($code===429){$sec=aih_retry_seconds($res['headers']??[],900);aih_mark_provider_state($id,['last_error'=>'HTTP 429','cooldown_until'=>time()+$sec]);}
    }
    return ['ok'=>count($answers)>0,'answers'=>$answers,'tried'=>$tried];
}

function aih_provider_public_status(): array {
    $s=aih_settings();$cat=aih_provider_catalog();$state=aih_provider_state();$p=$s['providers']??[];$out=[];
    foreach(($p['order']??array_keys($cat))as$id){
        if(!isset($cat[$id]))continue;
        $hasKey=trim((string)(($p['keys']??[])[$id]??''))!=='';
        $configured=!$cat[$id]['needs_key']||$hasKey;
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
