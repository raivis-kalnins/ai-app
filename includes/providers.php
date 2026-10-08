<?php
declare(strict_types=1);

function aih_provider_catalog(): array {
    return [
        'ollama_local'=>['name'=>'Ollama Local','kind'=>'local','needs_key'=>false,'note'=>'No subscription; runs on your own machine/server.'],
        'groq'=>['name'=>'Groq Free','kind'=>'free-key','needs_key'=>true,'note'=>'Free account/API key; rate-limited.'],
        'gemini'=>['name'=>'Google Gemini Free','kind'=>'free-key','needs_key'=>true,'note'=>'Free API tier where available; rate-limited.'],
        'openrouter'=>['name'=>'OpenRouter Free Models','kind'=>'free-key','needs_key'=>true,'note'=>'Free-model router; daily/rate limits apply.'],
        'ollama_cloud'=>['name'=>'Ollama Cloud','kind'=>'free-key','needs_key'=>true,'note'=>'Free tier available; requires Ollama account key.'],
        'pollinations'=>['name'=>'Pollinations','kind'=>'credit-key','needs_key'=>true,'note'=>'API key/credits required; optional fallback.'],
        'custom_openai'=>['name'=>'Custom OpenAI-compatible','kind'=>'custom','needs_key'=>false,'note'=>'Connect another free/local OpenAI-compatible endpoint.'],
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
    $r=aih_http_json('POST',$url,['model'=>$model,'messages'=>$messages,'temperature'=>0.3],$headers,90);
    if($r['code']>=200&&$r['code']<300){$j=json_decode($r['body'],true);$text=(string)($j['choices'][0]['message']['content']??'');if(trim($text)!=='')return ['ok'=>true,'text'=>trim($text),'headers'=>$r['headers']];}
    return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($r['body']?:$r['error'],0,500)),'headers'=>$r['headers']];
}

function aih_provider_call(string $id,array $messages,array $settings): array {
    $p=$settings['providers']??[]; $keys=$p['keys']??[]; $models=$p['models']??[]; $model=(string)($models[$id]??'');
    if($id==='ollama_local') return aih_openai_call($id,(string)($p['ollama_local_url']??'http://127.0.0.1:11434/v1/chat/completions'),'',$model,$messages);
    if($id==='groq') return aih_openai_call($id,'https://api.groq.com/openai/v1/chat/completions',(string)($keys['groq']??''),$model,$messages);
    if($id==='openrouter') return aih_openai_call($id,'https://openrouter.ai/api/v1/chat/completions',(string)($keys['openrouter']??''),$model,$messages,['HTTP-Referer: '.((!empty($_SERVER['HTTP_HOST']))?'https://'.$_SERVER['HTTP_HOST']:'https://localhost'),'X-Title: Free AI Hub 63']);
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
    $settings=$settings??aih_settings();$catalog=aih_provider_catalog();$p=$settings['providers']??[];$order=$p['order']??array_keys($catalog);$enabled=$p['enabled']??[];$keys=$p['keys']??[];$state=aih_provider_state();$tried=[];
    foreach($order as $id){
        if(empty($enabled[$id])||!isset($catalog[$id]))continue;
        $cool=(int)($state[$id]['cooldown_until']??0); if($cool>time()){$tried[]=['id'=>$id,'status'=>'cooldown','until'=>$cool];continue;}
        if(!empty($catalog[$id]['needs_key'])&&trim((string)($keys[$id]??''))===''){$tried[]=['id'=>$id,'status'=>'missing_key'];continue;}
        $res=aih_provider_call($id,$messages,$settings);
        if(!empty($res['ok'])){aih_mark_provider_state($id,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);return ['ok'=>true,'text'=>$res['text'],'provider'=>$id,'provider_name'=>$catalog[$id]['name'],'tried'=>$tried];}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'status'=>'failed','code'=>$code,'error'=>$res['error']??''];
        if($code===429){$sec=aih_retry_seconds($res['headers']??[],900);$until=time()+$sec;aih_mark_provider_state($id,['last_error'=>'HTTP 429','cooldown_until'=>$until]);$entry['status']='rate_limited';$entry['until']=$until;}
        elseif(in_array($code,[401,402,403],true)){aih_mark_provider_state($id,['last_error'=>'HTTP '.$code,'cooldown_until'=>time()+3600]);$entry['status']='auth_or_quota';$entry['until']=time()+3600;}
        else {aih_mark_provider_state($id,['last_error'=>'HTTP '.$code.' '.substr((string)($res['error']??''),0,120),'cooldown_until'=>0]);}
        $tried[]=$entry;
    }
    return ['ok'=>false,'error'=>'No configured free/local provider answered. Add a free key, start Ollama locally, or retry after provider cooldowns.','tried'=>$tried];
}

function aih_provider_public_status(): array {
    $s=aih_settings();$cat=aih_provider_catalog();$state=aih_provider_state();$p=$s['providers']??[];$out=[];
    foreach(($p['order']??array_keys($cat))as$id){if(!isset($cat[$id]))continue;$hasKey=trim((string)(($p['keys']??[])[$id]??''))!=='';$out[]=['id'=>$id,'name'=>$cat[$id]['name'],'kind'=>$cat[$id]['kind'],'enabled'=>!empty(($p['enabled']??[])[$id]),'configured'=>!$cat[$id]['needs_key']||$hasKey,'model'=>(string)(($p['models']??[])[$id]??''),'cooldown_until'=>(int)($state[$id]['cooldown_until']??0),'last_ok'=>(int)($state[$id]['last_ok']??0),'last_error'=>(string)($state[$id]['last_error']??''),'note'=>$cat[$id]['note']];}
    return $out;
}
