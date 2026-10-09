<?php
declare(strict_types=1);

function aih_connection_value(array $settings,string $provider,array $names,array $envNames=[]): string {
    $candidates=[];
    $p=$settings['providers']??[];
    $images=$settings['images']??[];
    $connections=$settings['connections']??[];
    $providerConnections=is_array($p['connections']??null)?$p['connections']:[];
    foreach($names as $name){
        if(isset($images[$name]))$candidates[]=$images[$name];
        if(isset($p[$name]))$candidates[]=$p[$name];
        if(isset($settings[$name]))$candidates[]=$settings[$name];
        if(isset($connections[$provider][$name]))$candidates[]=$connections[$provider][$name];
        if(isset($providerConnections[$provider][$name]))$candidates[]=$providerConnections[$provider][$name];
    }
    if(isset(($p['keys']??[])[$provider]))$candidates[]=($p['keys']??[])[$provider];
    foreach(['api_key','key','token','access_token'] as $name){
        if(isset($connections[$provider][$name]))$candidates[]=$connections[$provider][$name];
        if(isset($providerConnections[$provider][$name]))$candidates[]=$providerConnections[$provider][$name];
    }
    foreach($candidates as $value){$v=trim((string)$value);if($v!=='')return $v;}
    foreach($envNames as $env){$v=getenv($env);if($v!==false&&trim((string)$v)!=='')return trim((string)$v);}
    return '';
}

function aih_image_provider_catalog_key(string $id): string {
    return match($id){
        'cloudflare_image'=>'cloudflare','huggingface_image'=>'huggingface','pollinations'=>'pollinations',default=>$id,
    };
}

function aih_image_catalog(): array {
    return [
        'sdwebui'=>[
            'name'=>'Local Stable Diffusion / Forge',
            'note'=>'No cloud quota. Uses your own Stable Diffusion WebUI/Forge server.',
        ],
        'comfyui'=>[
            'name'=>'Local ComfyUI',
            'note'=>'No cloud quota. Uses your own ComfyUI server and checkpoint through the standard workflow API.',
        ],
        'cloudflare_image'=>[
            'name'=>'Cloudflare Workers AI · ByteDance SDXL-Lightning',
            'note'=>'Uses the Cloudflare Account ID and Workers AI token already configured under AI providers. Workers AI has a daily free allocation; quota/rate errors fall through automatically.',
        ],
        'alibaba_qwen_image'=>[
            'name'=>'Alibaba Model Studio · Qwen Image',
            'note'=>'Chinese Qwen image generation. New-user free image quotas are available in the Singapore region for eligible models; enable Free Quota Only in Alibaba to prevent paid overage.',
        ],
        'huggingface_image'=>[
            'name'=>'Hugging Face · Image inference',
            'note'=>'Uses your existing Hugging Face token and its small monthly Inference Providers credits. If the selected model/provider is unavailable, the router continues.',
        ],
        'siliconflow_image'=>[
            'name'=>'SiliconFlow · Image API',
            'note'=>'China-founded inference platform. New accounts currently start with a small credit; when it is exhausted the router continues.',
        ],
        'pollinations'=>[
            'name'=>'Pollinations',
            'note'=>'Optional account/key/credits image provider.',
        ],
        'free_web'=>[
            'name'=>'Free web images · Openverse + Wikimedia Commons',
            'note'=>'Searches existing openly licensed/public-domain web images with no API key. Results include source and licence links so you can verify reuse terms.',
        ],
        'aihorde'=>[
            'name'=>'AI Horde · anonymous community queue',
            'note'=>'No account or developer key is required. Uses AI Horde anonymous mode at the lowest queue priority. Prompts are processed by volunteer workers; do not send confidential images/prompts. Puter is not used automatically for images.',
        ],
    ];
}

function aih_image_state_id(string $id): string { return 'image_'.$id; }

function aih_image_save_bytes(string $bytes,string $contentType='image/png',string $prefix='image'): array {
    if(strlen($bytes)<256) return ['ok'=>false,'code'=>0,'error'=>'Image response was empty or too small','headers'=>[]];
    $ct=strtolower($contentType);
    $ext=str_contains($ct,'webp')?'webp':(str_contains($ct,'jpeg')||str_contains($ct,'jpg')?'jpg':'png');
    if(str_starts_with($bytes,"\xFF\xD8\xFF"))$ext='jpg';
    elseif(str_starts_with($bytes,"\x89PNG"))$ext='png';
    elseif(substr($bytes,0,4)==='RIFF'&&substr($bytes,8,4)==='WEBP')$ext='webp';
    $fn=aih_new_id($prefix).'.'.$ext;
    if(file_put_contents(AIH_GENERATED.'/'.$fn,$bytes)===false)return ['ok'=>false,'code'=>0,'error'=>'Could not save generated image','headers'=>[]];
    return ['ok'=>true,'file'=>$fn];
}

function aih_image_download_url(string $url,string $prefix='image'): array {
    if(!preg_match('#^https?://#i',$url))return ['ok'=>false,'code'=>0,'error'=>'Image provider returned an invalid URL','headers'=>[]];
    $r=aih_http('GET',$url,null,['Accept: image/*'],120);
    if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'error'=>'Could not download generated image (HTTP '.$r['code'].')','headers'=>$r['headers']??[]];
    $saved=aih_image_save_bytes((string)$r['body'],(string)($r['content_type']??'image/png'),$prefix);
    $saved['headers']=$r['headers']??[];
    return $saved;
}

function aih_image_region_host(string $region): string {
    return match($region){
        'cn-beijing'=>'cn-beijing',
        'cn-hongkong'=>'cn-hongkong',
        'eu-central-1'=>'eu-central-1',
        'us-east-1'=>'us-east-1',
        'ap-northeast-1'=>'ap-northeast-1',
        default=>'ap-southeast-1',
    };
}

function aih_image_provider_configured(string $id,array $s): bool {
    $images=$s['images']??[];
    $cloudflareAccount=aih_connection_value($s,'cloudflare',['cloudflare_account_id','account_id'],['AIH_CLOUDFLARE_ACCOUNT_ID','CLOUDFLARE_ACCOUNT_ID']);
    $cloudflareKey=aih_connection_value($s,'cloudflare',['cloudflare_api_token','api_token'],['AIH_CLOUDFLARE_API_TOKEN','CLOUDFLARE_API_TOKEN','CF_API_TOKEN']);
    $alibabaWorkspace=aih_connection_value($s,'alibaba',['alibaba_workspace_id','workspace_id'],['AIH_ALIBABA_WORKSPACE_ID','ALIBABA_WORKSPACE_ID']);
    $alibabaKey=aih_connection_value($s,'alibaba',['alibaba_api_key','api_key'],['AIH_ALIBABA_API_KEY','DASHSCOPE_API_KEY','ALIBABA_API_KEY']);
    $huggingfaceKey=aih_connection_value($s,'huggingface',['huggingface_api_key','hf_token'],['AIH_HUGGINGFACE_TOKEN','HF_TOKEN','HUGGINGFACEHUB_API_TOKEN']);
    $siliconflowKey=aih_connection_value($s,'siliconflow',['siliconflow_api_key','api_key'],['AIH_SILICONFLOW_API_KEY','SILICONFLOW_API_KEY']);
    $pollinationsKey=aih_connection_value($s,'pollinations',['pollinations_api_key','api_key'],['AIH_POLLINATIONS_API_KEY','POLLINATIONS_API_KEY']);
    return match($id){
        'sdwebui'=>trim((string)($images['sdwebui_url']??''))!=='',
        'comfyui'=>trim((string)($images['comfyui_url']??''))!==''&&trim((string)($images['comfyui_checkpoint']??''))!=='',
        'cloudflare_image'=>$cloudflareAccount!==''&&$cloudflareKey!=='',
        'alibaba_qwen_image'=>$alibabaWorkspace!==''&&$alibabaKey!=='',
        'huggingface_image'=>$huggingfaceKey!=='',
        'siliconflow_image'=>$siliconflowKey!=='',
        'pollinations'=>$pollinationsKey!=='',
        'free_web'=>true,
        'aihorde'=>true,
        default=>false,
    };
}

function aih_web_image_strlen(string $s): int { return function_exists('mb_strlen')?mb_strlen($s):strlen($s); }
function aih_web_image_substr(string $s,int $start,int $length): string { return function_exists('mb_substr')?mb_substr($s,$start,$length):substr($s,$start,$length); }
function aih_web_image_lower(string $s): string { return function_exists('mb_strtolower')?mb_strtolower($s):strtolower($s); }
function aih_web_image_pos(string $haystack,string $needle): int|false { return function_exists('mb_strpos')?mb_strpos($haystack,$needle):strpos($haystack,$needle); }

function aih_web_image_query(string $prompt): string {
    $q=html_entity_decode($prompt,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $q=str_replace(['+','_'], ' ', $q);
    // Remove common generation/UI filler while preserving the subject and place names.
    $q=preg_replace('/\b(?:please|pls|generate|create|make|build|show|find|give me|image|images|picture|pictures|photo|photos|photograph|photographs|high quality|better quality|nicer|nice|quality|colou?rs?|colors?)\b/ui',' ',$q)??$q;
    $q=preg_replace('/\b(?:and|or|with|for|the|a|an)\b/ui',' ',$q)??$q;
    $q=preg_replace('/\s+/u',' ',trim($q))??trim($q);
    if(aih_web_image_strlen($q)<3)$q=preg_replace('/\s+/u',' ',trim($prompt))??trim($prompt);
    return aih_web_image_substr($q,0,180);
}

function aih_web_image_plain(mixed $value): string {
    $s=html_entity_decode(strip_tags((string)$value),ENT_QUOTES|ENT_HTML5,'UTF-8');
    return trim(preg_replace('/\s+/u',' ',$s)??$s);
}

function aih_web_image_url(mixed $value): string {
    $u=trim((string)$value);
    return preg_match('#^https?://#i',$u)?$u:'';
}

function aih_web_image_score(array $row,string $query): float {
    $w=max(0,(int)($row['width']??0));$h=max(0,(int)($row['height']??0));
    $pixels=$w*$h;$score=min(7.0,$pixels>0?log(max(1,$pixels),10):0.0);
    $long=max($w,$h);
    if($long>=2400)$score+=3.2;elseif($long>=1600)$score+=2.4;elseif($long>=1000)$score+=1.4;elseif($long>0&&$long<640)$score-=4.0;
    $hay=aih_web_image_lower((string)(($row['title']??'').' '.($row['creator']??'').' '.($row['source']??'')));
    $queryLower=aih_web_image_lower($query);
    if($queryLower!==''&&aih_web_image_pos($hay,$queryLower)!==false)$score+=2.5;
    foreach(preg_split('/\s+/u',$queryLower)?:[] as$term){$term=trim($term);if(aih_web_image_strlen($term)>=3&&aih_web_image_pos($hay,$term)!==false)$score+=0.9;}
    if(!empty($row['license']))$score+=0.6;
    if(($row['source_key']??'')==='openverse')$score+=0.4;
    return $score;
}

function aih_openverse_image_search(string $query,int $limit=12): array {
    $url='https://api.openverse.org/v1/images/?'.http_build_query(['q'=>$query,'page_size'=>max(4,min(40,$limit)),'mature'=>'false','filter_dead'=>'true','license'=>'by,by-sa,cc0,pdm']);
    $r=aih_http('GET',$url,null,['Accept: application/json'],15);
    if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'Openverse HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$rows=[];
    foreach((is_array($j)?($j['results']??[]):[]) as$x){
        if(!is_array($x))continue;
        $preview=aih_web_image_url($x['thumbnail']??'');$image=aih_web_image_url($x['url']??'');if($preview===''&&$image==='')continue;
        $row=[
            'id'=>'ov-'.preg_replace('/[^A-Za-z0-9_-]/','',(string)($x['id']??aih_new_id('img'))),
            'title'=>aih_web_image_plain($x['title']??'Open image'),
            'preview_url'=>$preview!==''?$preview:$image,
            'image_url'=>$image!==''?$image:$preview,
            'source_url'=>aih_web_image_url($x['foreign_landing_url']??''),
            'license_url'=>aih_web_image_url($x['license_url']??''),
            'creator'=>aih_web_image_plain($x['creator']??''),
            'license'=>strtoupper(trim((string)($x['license']??''))).(trim((string)($x['license_version']??''))!==''?' '.trim((string)$x['license_version']):''),
            'source'=>'Openverse'.(!empty($x['provider'])?' · '.aih_web_image_plain($x['provider']):''),
            'source_key'=>'openverse','width'=>(int)($x['width']??0),'height'=>(int)($x['height']??0),
        ];
        if($row['source_url']==='')$row['source_url']=$row['image_url'];
        $rows[]=$row;
    }
    return ['ok'=>true,'code'=>$r['code'],'rows'=>$rows];
}

function aih_wikimedia_image_search(string $query,int $limit=12): array {
    $params=['action'=>'query','format'=>'json','formatversion'=>'2','generator'=>'search','gsrsearch'=>$query,'gsrnamespace'=>6,'gsrlimit'=>max(4,min(30,$limit)),'gsrsort'=>'relevance','prop'=>'imageinfo','iiprop'=>'url|extmetadata|mime|size','iiurlwidth'=>1200];
    $r=aih_http('GET','https://commons.wikimedia.org/w/api.php?'.http_build_query($params),null,['Accept: application/json'],15);
    if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'Wikimedia HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$rows=[];
    foreach((is_array($j)?($j['query']['pages']??[]):[]) as$x){
        if(!is_array($x))continue;$ii=$x['imageinfo'][0]??null;if(!is_array($ii))continue;
        $mime=strtolower((string)($ii['mime']??''));if($mime!==''&&!str_starts_with($mime,'image/'))continue;
        $preview=aih_web_image_url($ii['thumburl']??'');$image=aih_web_image_url($ii['url']??'');if($preview===''&&$image==='')continue;
        $m=is_array($ii['extmetadata']??null)?$ii['extmetadata']:[];
        $meta=function(string $key)use($m){return isset($m[$key]['value'])?aih_web_image_plain($m[$key]['value']):'';};
        $title=aih_web_image_plain(preg_replace('/^File:/i','',(string)($x['title']??'Open image')));
        $row=[
            'id'=>'wm-'.preg_replace('/[^A-Za-z0-9_-]/','',(string)($x['pageid']??aih_new_id('img'))),
            'title'=>$title!==''?$title:'Open image','preview_url'=>$preview!==''?$preview:$image,'image_url'=>$image!==''?$image:$preview,
            'source_url'=>aih_web_image_url($ii['descriptionurl']??''),'license_url'=>aih_web_image_url($m['LicenseUrl']['value']??''),
            'creator'=>$meta('Artist'),'license'=>$meta('LicenseShortName')?:$meta('UsageTerms'),'source'=>'Wikimedia Commons','source_key'=>'wikimedia',
            'width'=>(int)($ii['width']??0),'height'=>(int)($ii['height']??0),
        ];
        if($row['source_url']==='')$row['source_url']=$row['image_url'];
        $rows[]=$row;
    }
    return ['ok'=>true,'code'=>$r['code'],'rows'=>$rows];
}

function aih_web_image_search(string $prompt,int $limit=8): array {
    $query=aih_web_image_query($prompt);$want=max(4,min(12,$limit));$all=[];$errors=[];
    $open=aih_openverse_image_search($query,$want*2);if(!empty($open['ok']))$all=array_merge($all,$open['rows']??[]);elseif(!empty($open['error']))$errors[]=$open['error'];
    // Commons is queried separately because it often has stronger real-place/travel photography and useful attribution metadata.
    $wiki=aih_wikimedia_image_search($query,$want*2);if(!empty($wiki['ok']))$all=array_merge($all,$wiki['rows']??[]);elseif(!empty($wiki['error']))$errors[]=$wiki['error'];
    $seen=[];$rows=[];
    foreach($all as$row){$key=strtolower((string)($row['image_url']??$row['source_url']??''));if($key===''||isset($seen[$key]))continue;$seen[$key]=1;$row['_score']=aih_web_image_score($row,$query);$rows[]=$row;}
    usort($rows,fn($a,$b)=>(float)($b['_score']??0)<=>(float)($a['_score']??0));$rows=array_slice($rows,0,$want);foreach($rows as&$row)unset($row['_score']);unset($row);
    if(!$rows)return ['ok'=>false,'code'=>503,'error'=>'No matching free web images were returned'.($errors?' ('.implode('; ',$errors).')':''),'headers'=>[]];
    return ['ok'=>true,'pending'=>false,'result_type'=>'web_search','provider'=>'Free web images · Openverse + Wikimedia Commons','provider_id'=>'free_web','query'=>$query,'results'=>$rows,'notice'=>'Openly licensed/public-domain search results. Check the source/licence link before reuse.','headers'=>[]];
}

function aih_aihorde_prompt_is_stylized(string $prompt): bool {
    return (bool)preg_match('/\b(?:anime|manga|cartoon|illustration|watercolou?r|oil painting|pixel art|comic|sketch|vector|3d render|cgi|fantasy art|concept art)\b/ui',$prompt);
}

function aih_image_prompt_prefers_generation(string $prompt): bool {
    // Ordinary real-world/photo requests use existing web photography before anonymous
    // generation. Clearly stylised/original design requests keep generation first.
    return aih_aihorde_prompt_is_stylized($prompt) || (bool)preg_match('/\b(?:logo|brand mark|mascot|icon set|poster design|product mockup|surreal|fantasy|sci[- ]?fi|character design|3d render|cgi render)\b/ui',$prompt);
}

function aih_image_prompt_order(array $order,string $prompt,string $requested): array {
    if($requested!=='auto'||!aih_image_prompt_prefers_generation($prompt))return $order;
    if(!in_array('aihorde',$order,true)||!in_array('free_web',$order,true))return $order;
    // Put free_web immediately after AI Horde only for an explicitly generative prompt.
    $order=array_values(array_filter($order,fn($id)=>$id!=='free_web'));
    $pos=array_search('aihorde',$order,true);
    array_splice($order,$pos===false?count($order):(int)$pos+1,0,['free_web']);
    return $order;
}

function aih_aihorde_quality_prompt(string $prompt): string {
    $p=trim($prompt);if($p===''||str_contains($p,'###'))return $p;
    if(aih_aihorde_prompt_is_stylized($p))return str_replace('+',',',$p).' ### blurry, low detail, text, watermark, malformed anatomy, duplicate objects';
    $p=str_replace('+',',',$p);
    return $p.', realistic professional photograph, natural lighting, balanced rich colours, sharp focus, fine detail ### anime, cartoon, illustration, blurry, low detail, text, watermark, distorted anatomy, duplicate objects';
}

function aih_image_call(string $id,string $prompt,array $s): array {
    $images=$s['images']??[];$p=$s['providers']??[];$keys=$p['keys']??[];
    if($id==='free_web')return aih_web_image_search($prompt,10);
    if($id==='sdwebui'){
        $base=rtrim(trim((string)($images['sdwebui_url']??'')),'/');
        if($base==='')return ['ok'=>false,'code'=>0,'error'=>'Stable Diffusion WebUI URL missing','headers'=>[]];
        $r=aih_http_json('POST',$base.'/sdapi/v1/txt2img',['prompt'=>$prompt,'steps'=>20,'width'=>1024,'height'=>1024],[],180);
        $j=json_decode((string)$r['body'],true);$b64=(string)($j['images'][0]??'');
        if($r['code']<200||$r['code']>=300||$b64==='')return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr((string)($r['body']?:$r['error']?:'Local image endpoint failed'),0,500)),'headers'=>$r['headers']??[]];
        $bin=base64_decode((string)preg_replace('#^data:image/[^;]+;base64,#','',$b64),true);
        if($bin===false)return ['ok'=>false,'code'=>0,'error'=>'Bad local image response','headers'=>$r['headers']??[]];
        $saved=aih_image_save_bytes($bin,'image/png');$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;
    }
    if($id==='comfyui'){
        $base=rtrim(trim((string)($images['comfyui_url']??'')),'/');
        $checkpoint=trim((string)($images['comfyui_checkpoint']??''));
        if($base===''||$checkpoint==='')return ['ok'=>false,'code'=>0,'error'=>'ComfyUI URL or checkpoint missing','headers'=>[]];
        $width=max(256,min(1536,(int)($images['comfyui_width']??768)));
        $height=max(256,min(1536,(int)($images['comfyui_height']??768)));
        $seed=random_int(1,2147483646);
        $workflow=[
            '4'=>['class_type'=>'CheckpointLoaderSimple','inputs'=>['ckpt_name'=>$checkpoint]],
            '6'=>['class_type'=>'CLIPTextEncode','inputs'=>['text'=>$prompt,'clip'=>['4',1]]],
            '7'=>['class_type'=>'CLIPTextEncode','inputs'=>['text'=>'low quality, blurry, distorted, malformed','clip'=>['4',1]]],
            '5'=>['class_type'=>'EmptyLatentImage','inputs'=>['width'=>$width,'height'=>$height,'batch_size'=>1]],
            '3'=>['class_type'=>'KSampler','inputs'=>['seed'=>$seed,'steps'=>20,'cfg'=>7,'sampler_name'=>'euler','scheduler'=>'normal','denoise'=>1,'model'=>['4',0],'positive'=>['6',0],'negative'=>['7',0],'latent_image'=>['5',0]]],
            '8'=>['class_type'=>'VAEDecode','inputs'=>['samples'=>['3',0],'vae'=>['4',2]]],
            '9'=>['class_type'=>'SaveImage','inputs'=>['filename_prefix'=>'FreeAIHub63','images'=>['8',0]]],
        ];
        $client=aih_new_id('comfy');
        $submit=aih_http_json('POST',$base.'/prompt',['prompt'=>$workflow,'client_id'=>$client],[],30);
        $j=json_decode((string)$submit['body'],true);$promptId=trim((string)($j['prompt_id']??''));
        if($submit['code']<200||$submit['code']>=300||$promptId==='')return ['ok'=>false,'code'=>$submit['code'],'error'=>trim(substr((string)($submit['body']?:$submit['error']?:'ComfyUI prompt was not accepted'),0,700)),'headers'=>$submit['headers']??[]];
        $deadline=microtime(true)+180;
        while(microtime(true)<$deadline){
            usleep(1500000);
            $history=aih_http('GET',$base.'/history/'.rawurlencode($promptId),null,['Accept: application/json'],20);
            if($history['code']<200||$history['code']>=300)continue;
            $hj=json_decode((string)$history['body'],true);$entry=is_array($hj)?($hj[$promptId]??null):null;
            if(!is_array($entry))continue;
            $outputs=$entry['outputs']??[];$imgMeta=null;
            foreach($outputs as $output){if(!is_array($output))continue;foreach(($output['images']??[]) as $img){if(is_array($img)&&!empty($img['filename'])){$imgMeta=$img;break 2;}}}
            if(!$imgMeta){$status=(string)($entry['status']['status_str']??'');if($status==='error')return ['ok'=>false,'code'=>500,'error'=>'ComfyUI workflow failed','headers'=>$history['headers']??[]];continue;}
            $query=http_build_query(['filename'=>(string)$imgMeta['filename'],'subfolder'=>(string)($imgMeta['subfolder']??''),'type'=>(string)($imgMeta['type']??'output')]);
            $img=aih_http('GET',$base.'/view?'.$query,null,['Accept: image/*'],60);
            if($img['code']>=200&&$img['code']<300){$saved=aih_image_save_bytes((string)$img['body'],(string)($img['content_type']??'image/png'),'comfyui');$saved['code']=$img['code'];$saved['headers']=$img['headers']??[];return $saved;}
            return ['ok'=>false,'code'=>$img['code'],'error'=>'ComfyUI generated an image but it could not be downloaded','headers'=>$img['headers']??[]];
        }
        return ['ok'=>false,'code'=>504,'error'=>'ComfyUI generation timed out after 180 seconds','headers'=>[]];
    }
    if($id==='cloudflare_image'){
        $account=aih_connection_value($s,'cloudflare',['cloudflare_account_id','account_id'],['AIH_CLOUDFLARE_ACCOUNT_ID','CLOUDFLARE_ACCOUNT_ID']);$key=aih_connection_value($s,'cloudflare',['cloudflare_api_token','api_token'],['AIH_CLOUDFLARE_API_TOKEN','CLOUDFLARE_API_TOKEN','CF_API_TOKEN']);
        if($account===''||$key==='')return ['ok'=>false,'code'=>0,'error'=>'Cloudflare Account ID or Workers AI token missing','headers'=>[]];
        $model=trim((string)($images['cloudflare_image_model']??'@cf/bytedance/stable-diffusion-xl-lightning'))?:'@cf/bytedance/stable-diffusion-xl-lightning';
        $url='https://api.cloudflare.com/client/v4/accounts/'.rawurlencode($account).'/ai/run/'.str_replace(['%2F','%40'],['/','@'],rawurlencode($model));
        $r=aih_http_json('POST',$url,['prompt'=>$prompt,'width'=>1024,'height'=>1024,'num_steps'=>8],['Authorization: Bearer '.$key,'Accept: image/*'],180);
        if($r['code']>=200&&$r['code']<300){
            $ct=strtolower((string)($r['content_type']??''));
            if(str_contains($ct,'image/')||str_starts_with((string)$r['body'],"\xFF\xD8\xFF")||str_starts_with((string)$r['body'],"\x89PNG")){
                $saved=aih_image_save_bytes((string)$r['body'],$ct?:'image/jpeg');$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;
            }
            $j=json_decode((string)$r['body'],true);$b64=(string)($j['result']['image']??$j['result']['image_b64']??'');
            if($b64!==''){$bin=base64_decode((string)preg_replace('#^data:image/[^;]+;base64,#','',$b64),true);if($bin!==false){$saved=aih_image_save_bytes($bin,'image/png');$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;}}
        }
        return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr((string)($r['body']?:$r['error']?:'Cloudflare image generation failed'),0,700)),'headers'=>$r['headers']??[]];
    }
    if($id==='alibaba_qwen_image'){
        $key=aih_connection_value($s,'alibaba',['alibaba_api_key','api_key'],['AIH_ALIBABA_API_KEY','DASHSCOPE_API_KEY','ALIBABA_API_KEY']);$workspace=preg_replace('/[^A-Za-z0-9_-]/','',aih_connection_value($s,'alibaba',['alibaba_workspace_id','workspace_id'],['AIH_ALIBABA_WORKSPACE_ID','ALIBABA_WORKSPACE_ID']));
        if($key===''||$workspace==='')return ['ok'=>false,'code'=>0,'error'=>'Alibaba Model Studio API key or Workspace ID missing','headers'=>[]];
        $region=aih_image_region_host((string)($images['alibaba_region']??'ap-southeast-1'));
        $model=trim((string)($images['alibaba_image_model']??'qwen-image-2.0'))?:'qwen-image-2.0';
        $url='https://'.$workspace.'.'.$region.'.maas.aliyuncs.com/api/v1/services/aigc/multimodal-generation/generation';
        $payload=['model'=>$model,'input'=>['messages'=>[['role'=>'user','content'=>[['text'=>$prompt]]]]],'parameters'=>['n'=>1,'size'=>'1024*1024','prompt_extend'=>true,'watermark'=>false]];
        $r=aih_http_json('POST',$url,$payload,['Authorization: Bearer '.$key],240);$j=json_decode((string)$r['body'],true);
        if($r['code']>=200&&$r['code']<300&&is_array($j)){
            $urlOut='';
            foreach(($j['output']['choices'][0]['message']['content']??[]) as $item){if(is_array($item)&&!empty($item['image'])){$urlOut=(string)$item['image'];break;}}
            if($urlOut===''&&!empty($j['data'][0]['url']))$urlOut=(string)$j['data'][0]['url'];
            if($urlOut===''&&!empty($j['output']['results'][0]['url']))$urlOut=(string)$j['output']['results'][0]['url'];
            if($urlOut!==''){$saved=aih_image_download_url($urlOut);$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;}
        }
        $err=is_array($j)?((string)($j['message']??$j['error']['message']??$j['code']??'')):(string)$r['body'];
        return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($err?:$r['error']?:'Alibaba Qwen image generation failed',0,700)),'headers'=>$r['headers']??[]];
    }
    if($id==='huggingface_image'){
        $key=aih_connection_value($s,'huggingface',['huggingface_api_key','hf_token'],['AIH_HUGGINGFACE_TOKEN','HF_TOKEN','HUGGINGFACEHUB_API_TOKEN']);if($key==='')return ['ok'=>false,'code'=>0,'error'=>'Hugging Face token missing','headers'=>[]];
        $model=trim((string)($images['huggingface_image_model']??'black-forest-labs/FLUX.1-schnell'))?:'black-forest-labs/FLUX.1-schnell';
        $url='https://router.huggingface.co/hf-inference/models/'.str_replace('%2F','/',rawurlencode($model));
        $r=aih_http_json('POST',$url,['inputs'=>$prompt,'parameters'=>['width'=>1024,'height'=>1024]],['Authorization: Bearer '.$key,'Accept: image/*'],240);
        if($r['code']>=200&&$r['code']<300){$saved=aih_image_save_bytes((string)$r['body'],(string)($r['content_type']??'image/png'));$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;}
        return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr((string)($r['body']?:$r['error']?:'Hugging Face image generation failed'),0,700)),'headers'=>$r['headers']??[]];
    }
    if($id==='siliconflow_image'){
        $key=aih_connection_value($s,'siliconflow',['siliconflow_api_key','api_key'],['AIH_SILICONFLOW_API_KEY','SILICONFLOW_API_KEY']);if($key==='')return ['ok'=>false,'code'=>0,'error'=>'SiliconFlow API key missing','headers'=>[]];
        $model=trim((string)($images['siliconflow_image_model']??'black-forest-labs/FLUX.1-schnell'))?:'black-forest-labs/FLUX.1-schnell';
        $r=aih_http_json('POST','https://api.siliconflow.com/v1/images/generations',['model'=>$model,'prompt'=>$prompt,'image_size'=>'1024x1024'],['Authorization: Bearer '.$key],240);$j=json_decode((string)$r['body'],true);
        if($r['code']>=200&&$r['code']<300&&is_array($j)){
            $urlOut=(string)($j['images'][0]['url']??$j['data'][0]['url']??'');$b64=(string)($j['data'][0]['b64_json']??$j['images'][0]['b64_json']??'');
            if($urlOut!==''){$saved=aih_image_download_url($urlOut);$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;}
            if($b64!==''){$bin=base64_decode($b64,true);if($bin!==false){$saved=aih_image_save_bytes($bin,'image/png');$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;}}
        }
        $err=is_array($j)?((string)($j['message']??$j['error']['message']??'')):(string)$r['body'];return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr($err?:$r['error']?:'SiliconFlow image generation failed',0,700)),'headers'=>$r['headers']??[]];
    }
    if($id==='pollinations'){
        $key=aih_connection_value($s,'pollinations',['pollinations_api_key','api_key'],['AIH_POLLINATIONS_API_KEY','POLLINATIONS_API_KEY']);if($key==='')return ['ok'=>false,'code'=>0,'error'=>'Pollinations key/credits not configured','headers'=>[]];
        $model=trim((string)($images['pollinations_model']??'flux'))?:'flux';$url='https://gen.pollinations.ai/image/'.rawurlencode($prompt).'?model='.rawurlencode($model);
        $r=aih_http('GET',$url,null,['Authorization: Bearer '.$key,'Accept: image/*'],180);if($r['code']>=200&&$r['code']<300){$saved=aih_image_save_bytes((string)$r['body'],(string)($r['content_type']??'image/jpeg'));$saved['code']=$r['code'];$saved['headers']=$r['headers']??[];return $saved;}
        return ['ok'=>false,'code'=>$r['code'],'error'=>trim(substr((string)($r['body']?:$r['error']?:'Pollinations image generation failed'),0,700)),'headers'=>$r['headers']??[]];
    }
    if($id==='aihorde'){
        $key=aih_connection_value($s,'aihorde',['aihorde_api_key','api_key'],['AIH_AIHORDE_API_KEY','AIHORDE_API_KEY']);
        if($key==='')$key='0000000000';
        $model=trim((string)($images['aihorde_model']??''));if($model==='')$model=aih_aihorde_auto_model($s,$prompt);
        $timeout=max(45,min(300,(int)($images['aihorde_timeout']??180)));$qualityPrompt=aih_aihorde_quality_prompt($prompt);
        $payload=[
            'prompt'=>$qualityPrompt,
            'params'=>['sampler_name'=>'k_euler_a','cfg_scale'=>7,'steps'=>20,'width'=>512,'height'=>512,'n'=>1,'karras'=>true],
            'nsfw'=>false,'censor_nsfw'=>true,'trusted_workers'=>false,'slow_workers'=>true,'r2'=>true,'shared'=>false,
        ];
        if($model!=='')$payload['models']=[$model];
        $client='FreeAIHub63:'.AIH_VERSION.':https://ai.63.lv/';
        $headers=['apikey: '.$key,'Client-Agent: '.$client];
        $start=aih_http_json('POST','https://aihorde.net/api/v2/generate/async',$payload,$headers,20);
        $startJson=json_decode((string)$start['body'],true);$requestId=trim((string)($startJson['id']??''));
        if($start['code']<200||$start['code']>=300||$requestId===''){
            $err=is_array($startJson)?((string)($startJson['message']??$startJson['error']??'')):(string)$start['body'];
            return ['ok'=>false,'code'=>$start['code'],'error'=>trim(substr($err?:$start['error']?:'AI Horde request was not accepted',0,700)),'headers'=>$start['headers']??[]];
        }
        $deadline=microtime(true)+$timeout;$lastCheck=[];
        while(microtime(true)<$deadline){
            usleep(2000000);
            $check=aih_http('GET','https://aihorde.net/api/v2/generate/check/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],10);
            $checkJson=json_decode((string)$check['body'],true);if(is_array($checkJson))$lastCheck=$checkJson;
            if($check['code']>=400&&$check['code']!==404)break;
            if(!empty($checkJson['faulted']))return ['ok'=>false,'code'=>503,'error'=>'AI Horde generation faulted','headers'=>$check['headers']??[]];
            if(empty($checkJson['done']))continue;
            $status=aih_http('GET','https://aihorde.net/api/v2/generate/status/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],30);
            $statusJson=json_decode((string)$status['body'],true);$generation=is_array($statusJson)?($statusJson['generations'][0]??[]):[];if(is_array($generation)&&aih_aihorde_generation_censored($generation))return ['ok'=>false,'code'=>422,'error'=>'AI Horde worker censored this result. This can be a false positive on SFW prompts; retry or use Free web images for real-world/reference photography.','headers'=>$status['headers']??[]];$img=is_array($generation)?(string)($generation['img']??''):'';
            if($status['code']>=200&&$status['code']<300&&$img!==''){
                if(preg_match('#^https?://#i',$img)){$saved=aih_image_download_url($img);$saved['code']=$status['code'];$saved['headers']=$status['headers']??[];return $saved;}
                $raw=preg_replace('#^data:image/[^;]+;base64,#','',$img);$bin=base64_decode((string)$raw,true);
                if($bin!==false){$saved=aih_image_save_bytes($bin,'image/webp');$saved['code']=$status['code'];$saved['headers']=$status['headers']??[];return $saved;}
            }
            $err=is_array($statusJson)?((string)($statusJson['message']??$statusJson['error']??'')):(string)$status['body'];
            return ['ok'=>false,'code'=>$status['code'],'error'=>trim(substr($err?:'AI Horde returned no image',0,700)),'headers'=>$status['headers']??[]];
        }
        @aih_http('DELETE','https://aihorde.net/api/v2/generate/status/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],10);
        $wait=(int)($lastCheck['wait_time']??0);$suffix=$wait>0?' (last queue estimate '.$wait.'s)':'';
        return ['ok'=>false,'code'=>504,'error'=>'AI Horde anonymous queue timed out after '.$timeout.'s'.$suffix,'headers'=>[]];
    }
    return ['ok'=>false,'code'=>0,'error'=>'Unknown image provider','headers'=>[]];
}

function aih_aihorde_generation_censored(array $generation): bool {
    if(!empty($generation['censored']))return true;
    foreach(($generation['gen_metadata']??[]) as$m){if(is_array($m)&&str_contains(strtolower((string)($m['type']??'')),'censor'))return true;}
    return false;
}

function aih_aihorde_client(): string { return 'FreeAIHub63:'.AIH_VERSION.':https://ai.63.lv/'; }

function aih_aihorde_key(array $s): string {
    $key=aih_connection_value($s,'aihorde',['aihorde_api_key','api_key'],['AIH_AIHORDE_API_KEY','AIHORDE_API_KEY']);
    return $key!==''?$key:'0000000000';
}

function aih_aihorde_pick_model_from_rows(array $rows,string $prompt=''): string {
    $best='';$bestScore=INF;$stylized=aih_aihorde_prompt_is_stylized($prompt);
    foreach($rows as$row){
        if(!is_array($row))continue;$name=trim((string)($row['name']??''));$count=max(0,(int)($row['count']??0));
        if($name===''||$count<1)continue;
        $eta=max(0.0,(float)($row['eta']??999999));$queued=max(0.0,(float)($row['queued']??0));$performance=max(0.0,(float)($row['performance']??0));
        $score=$eta+($queued/max(1,$count))*0.35-min(90.0,$count*3.0)-min(60.0,$performance/20.0);$n=aih_web_image_lower($name);
        $photo=(bool)preg_match('/(?:sdxl|stable diffusion xl|realvis|juggernaut|dreamshaper|deliberate|albedo|realistic|photo|photoreal|flux)/ui',$n);
        $anime=(bool)preg_match('/(?:anime|manga|pony|waifu|hentai|furry|toony|cartoon)/ui',$n);
        if($stylized&&$anime)$score-=220;elseif(!$stylized&&$photo)$score-=180;
        if(!$stylized&&$anime)$score+=900;
        if($score<$bestScore){$bestScore=$score;$best=$name;}
    }
    return $best;
}

function aih_aihorde_auto_model(array $s,string $prompt=''): string {
    $configured=trim((string)(($s['images']??[])['aihorde_model']??''));if($configured!=='')return $configured;
    $bucket=aih_aihorde_prompt_is_stylized($prompt)?'art':'photo';$cache=AIH_CACHE.'/aihorde_image_model_'.$bucket.'.json';$cached=aih_read_json($cache,[]);
    if(is_array($cached)&&!empty($cached['model'])&&(int)($cached['expires']??0)>time())return trim((string)$cached['model']);
    $r=aih_http('GET','https://aihorde.net/api/v2/status/models?type=image&min_count=1',null,['Accept: application/json','Client-Agent: '.aih_aihorde_client()],6);
    if($r['code']<200||$r['code']>=300)return '';
    $j=json_decode((string)$r['body'],true);$model=is_array($j)?aih_aihorde_pick_model_from_rows($j,$prompt):'';
    if($model!=='')aih_write_json($cache,['model'=>$model,'expires'=>time()+300]);
    return $model;
}

function aih_aihorde_submit(string $prompt,array $s): array {
    $images=$s['images']??[];$model=aih_aihorde_auto_model($s,$prompt);$steps=max(4,min(30,(int)($images['aihorde_steps']??16)));$qualityPrompt=aih_aihorde_quality_prompt($prompt);
    $payload=[
        'prompt'=>$qualityPrompt,
        'params'=>['sampler_name'=>'k_euler_a','cfg_scale'=>7,'steps'=>$steps,'width'=>512,'height'=>512,'n'=>1,'karras'=>true],
        'nsfw'=>false,'censor_nsfw'=>true,'trusted_workers'=>false,'slow_workers'=>true,'r2'=>true,'shared'=>false,
    ];
    if($model!=='')$payload['models']=[$model];
    $headers=['apikey: '.aih_aihorde_key($s),'Client-Agent: '.aih_aihorde_client()];
    $start=aih_http_json('POST','https://aihorde.net/api/v2/generate/async',$payload,$headers,20);
    $j=json_decode((string)$start['body'],true);$requestId=trim((string)($j['id']??''));
    if($start['code']<200||$start['code']>=300||$requestId===''){
        $err=is_array($j)?((string)($j['message']??$j['error']??'')):(string)$start['body'];
        return ['ok'=>false,'code'=>$start['code'],'error'=>trim(substr($err?:$start['error']?:'AI Horde request was not accepted',0,700)),'headers'=>$start['headers']??[]];
    }
    // Return immediately once the job is accepted. Queue ETA/position are fetched by the browser poll.
    return ['ok'=>true,'pending'=>true,'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'model'=>$model,'steps'=>$steps,'wait_time'=>0,'queue_position'=>0,'is_possible'=>true];
}

function aih_aihorde_status(string $requestId,array $s): array {
    if(!preg_match('/^[A-Za-z0-9-]{10,100}$/',$requestId))return ['ok'=>false,'code'=>422,'error'=>'Invalid AI Horde request id'];
    $client=aih_aihorde_client();$check=aih_http('GET','https://aihorde.net/api/v2/generate/check/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],10);
    if($check['code']===429||$check['code']===0||$check['code']>=500)return ['ok'=>true,'pending'=>true,'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'temporary'=>true,'retry_after'=>5];
    if($check['code']<200||$check['code']>=300)return ['ok'=>false,'code'=>$check['code'],'error'=>trim(substr((string)($check['body']?:$check['error']?:'AI Horde status failed'),0,700)),'headers'=>$check['headers']??[]];
    $j=json_decode((string)$check['body'],true);if(!is_array($j))return ['ok'=>true,'pending'=>true,'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'temporary'=>true,'retry_after'=>5];
    if(!empty($j['faulted']))return ['ok'=>false,'code'=>503,'error'=>'AI Horde generation faulted','headers'=>$check['headers']??[]];
    $progress=['ok'=>true,'pending'=>empty($j['done']),'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'wait_time'=>max(0,(int)($j['wait_time']??0)),'queue_position'=>max(0,(int)($j['queue_position']??0)),'is_possible'=>array_key_exists('is_possible',$j)?(bool)$j['is_possible']:true];
    if(empty($j['done']))return $progress;
    $status=aih_http('GET','https://aihorde.net/api/v2/generate/status/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],30);
    if($status['code']===429||$status['code']===0||$status['code']>=500){$progress['pending']=true;$progress['temporary']=true;$progress['retry_after']=5;return $progress;}
    $sj=json_decode((string)$status['body'],true);$generation=is_array($sj)?($sj['generations'][0]??[]):[];if(is_array($generation)&&aih_aihorde_generation_censored($generation))return ['ok'=>false,'code'=>422,'error'=>'AI Horde worker censored this result. This can be a false positive on SFW prompts; retry or choose Free web images (Openverse + Wikimedia) for real-world/reference photography.','headers'=>$status['headers']??[],'censored'=>true];$img=is_array($generation)?trim((string)($generation['img']??'')):'';
    if($status['code']>=200&&$status['code']<300&&$img!==''){
        if(preg_match('#^https?://#i',$img))$saved=aih_image_download_url($img);else{$raw=preg_replace('#^data:image/[^;]+;base64,#','',$img);$bin=base64_decode((string)$raw,true);$saved=$bin===false?['ok'=>false,'code'=>0,'error'=>'AI Horde returned invalid image data','headers'=>[]]:aih_image_save_bytes($bin,'image/webp');}
        if(!empty($saved['ok']))return ['ok'=>true,'pending'=>false,'file'=>$saved['file'],'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'model'=>(string)($generation['model']??''),'seed'=>(string)($generation['seed']??'')];
        return $saved+['pending'=>false];
    }
    $err=is_array($sj)?((string)($sj['message']??$sj['error']??'')):(string)$status['body'];
    return ['ok'=>false,'code'=>$status['code'],'error'=>trim(substr($err?:'AI Horde completed without an image',0,700)),'headers'=>$status['headers']??[]];
}

function aih_image_start(string $prompt,string $requested='auto',?array $settings=null): array {
    $settings=$settings??aih_settings();$catalog=aih_image_catalog();$images=$settings['images']??[];$state=aih_provider_state();$tried=[];
    $order=is_array($images['order']??null)?$images['order']:['sdwebui','comfyui','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations','free_web','aihorde'];
    if($requested!=='auto')$order=[$requested];
    else $order=aih_image_prompt_order($order,$prompt,$requested);
    foreach($order as$id){
        $id=(string)$id;if(!isset($catalog[$id]))continue;$stateId=aih_image_state_id($id);$cool=(int)($state[$stateId]['cooldown_until']??0);
        if($cool>time()){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'cooldown','until'=>$cool];continue;}
        if(!aih_image_provider_configured($id,$settings)){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'not_configured'];continue;}
        $res=$id==='aihorde'?aih_aihorde_submit($prompt,$settings):aih_image_call($id,$prompt,$settings);
        if(!empty($res['ok'])){aih_mark_provider_state($stateId,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);$res['provider']=$catalog[$id]['name'];$res['provider_id']=$id;$res['tried']=$tried;if(!array_key_exists('pending',$res))$res['pending']=false;return $res;}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'failed','code'=>$code,'error'=>substr((string)($res['error']??''),0,220)];$until=0;
        if($code===429){$until=time()+aih_retry_seconds($res['headers']??[],900);$entry['status']='rate_limited';}
        elseif(in_array($code,[401,402,403],true)){$until=time()+3600;$entry['status']='auth_or_quota';}
        elseif($code===0){$until=time()+300;$entry['status']='unavailable';}
        elseif($code>=500){$until=time()+120;$entry['status']='temporary_error';}
        if($until>0)$entry['until']=$until;aih_mark_provider_state($stateId,['last_error'=>'HTTP '.$code.' '.substr((string)($res['error']??''),0,160),'cooldown_until'=>$until]);$tried[]=$entry;
    }
    return ['ok'=>false,'error'=>'No image provider could answer. Local SD/Forge and ComfyUI need a reachable local server; configured cloud providers are tried next; ordinary photo/reference prompts use free web search before AI Horde, while explicitly stylised generation can try AI Horde first. Puter is not used automatically for images.','tried'=>$tried,'provider_catalog'=>$catalog];
}

function aih_image_generate(string $prompt,string $requested='auto',?array $settings=null): array {
    $settings=$settings??aih_settings();$catalog=aih_image_catalog();$images=$settings['images']??[];$state=aih_provider_state();$tried=[];
    $order=is_array($images['order']??null)?$images['order']:['sdwebui','comfyui','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations','free_web','aihorde'];
    if($requested!=='auto')$order=[$requested];
    else $order=aih_image_prompt_order($order,$prompt,$requested);
    foreach($order as $id){$id=(string)$id;if(!isset($catalog[$id]))continue;$stateId=aih_image_state_id($id);$cool=(int)($state[$stateId]['cooldown_until']??0);if($cool>time()){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'cooldown','until'=>$cool];continue;}
        if(!aih_image_provider_configured($id,$settings)){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'not_configured'];continue;}
        if($id==='free_web'){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'requires_v062_ui'];continue;}
        $res=aih_image_call($id,$prompt,$settings);if(!empty($res['ok'])){aih_mark_provider_state($stateId,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);return ['ok'=>true,'file'=>$res['file'],'provider'=>$catalog[$id]['name'],'provider_id'=>$id,'tried'=>$tried];}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'failed','code'=>$code,'error'=>substr((string)($res['error']??''),0,220)];$until=0;
        if($code===429){$until=time()+aih_retry_seconds($res['headers']??[],900);$entry['status']='rate_limited';}
        elseif(in_array($code,[401,402,403],true)){$until=time()+3600;$entry['status']='auth_or_quota';}
        elseif($code===0){$until=time()+300;$entry['status']='unavailable';}
        elseif($code>=500){$until=time()+300;$entry['status']='temporary_error';}
        if($until>0)$entry['until']=$until;aih_mark_provider_state($stateId,['last_error'=>'HTTP '.$code.' '.substr((string)($res['error']??''),0,160),'cooldown_until'=>$until]);$tried[]=$entry;
    }
    return ['ok'=>false,'error'=>'No image provider completed synchronously. Use the asynchronous image route for AI Horde queues; local SD/Forge and ComfyUI remain the no-cloud-quota options. Puter is not used automatically for images.','tried'=>$tried,'provider_catalog'=>$catalog];
}
