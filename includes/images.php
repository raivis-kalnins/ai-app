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
            'name'=>'Free web images · multi-source',
            'note'=>'Searches multiple free image libraries and can compose selected results into cards, posters, social graphics, heroes and moodboards. No-key sources include Openverse, Wikimedia Commons, NASA, The Met and LoremFlickr; optional keys add Unsplash, Pexels and Pixabay.',
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
    if(!aih_free_provider_allowed($id))return false;
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

function aih_web_image_ascii(string $s): string {
    if(function_exists('transliterator_transliterate')){
        $x=@transliterator_transliterate('Any-Latin; Latin-ASCII',$s);
        if(is_string($x)&&$x!=='')return $x;
    }
    if(function_exists('iconv')){
        $x=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);
        if(is_string($x)&&$x!=='')return $x;
    }
    return $s;
}

function aih_web_image_normalize_prompt(string $prompt): string {
    $q=html_entity_decode($prompt,ENT_QUOTES|ENT_HTML5,'UTF-8');
    $q=str_replace(['+','_'], ' ', $q);
    // Repair common mobile typing mistakes before token cleanup.
    $q=preg_replace('/\bcreat\s+enice\b/i','create nice',$q)??$q;
    $q=preg_replace('/\bcreat\b/i','create',$q)??$q;
    $q=preg_replace('/\benice\b/i','nice',$q)??$q;
    $q=preg_replace('/\bcaffe\b/i','cafe',$q)??$q;
    $q=preg_replace('/\bautum\b/i','autumn',$q)??$q;
    $q=preg_replace('/\bridens\b/i','rudens',$q)??$q;
    return trim(preg_replace('/\s+/u',' ',$q)??$q);
}

function aih_web_image_composer_meta(string $prompt): array {
    $plain=aih_web_image_normalize_prompt($prompt);
    $ascii=aih_web_image_lower(aih_web_image_ascii($plain));
    $text='';

    // Prefer explicitly quoted copy, then accept English/Latvian text markers.
    // Latvian prompts commonly use "ar tekstu - ..." and mobile users often omit quotes.
    if(preg_match('/[\x{201c}\x{201e}"\']([^\x{201c}\x{201d}\x{201e}"\']{2,120})[\x{201d}"\']\s*$/u',$plain,$m))$text=trim((string)$m[1]);
    if($text===''&&preg_match('/(?:\bar\s+)?(?:\btext\b|\bmessage\b|\bsaying\b|\bcaption\b|\btekst(?:s|u)?\b|\buzrakst(?:s|u)?\b|\bzi(?:n|ņ)a\b|\bv(?:e|ē)lejums?\b)\s*(?:[:=\-\x{2013}\x{2014}]\s*)?[\x{201c}\x{201e}"\']?(.+?)[\x{201d}"\']?\s*$/ui',$plain,$m))$text=trim((string)$m[1]);

    $hasCard=(bool)preg_match('/\b(?:card|greeting\s+card|birthday\s+card|christmas\s+card|new\s+year\s+card|thank\s+you\s+card|good\s+morning\s+card|kartin\w*|apsveikum\w*|dzimsanas\s+dien\w*|varda\s+dien\w*|ziemassvet\w*|jaun\w*\s+gad\w*)\b/i',$ascii);
    // A short suffix after a dash is normally the greeting itself for card requests.
    if($text===''&&$hasCard&&preg_match('/(?:\-|\x{2013}|\x{2014})\s*([^\-\x{2013}\x{2014}]{2,120})\s*$/u',$plain,$m)){$text=trim((string)$m[1]);$text=trim($text," \t\n\r\0\x0B\"'");$text=preg_replace('/^[\x{201c}\x{201d}\x{201e}]+|[\x{201c}\x{201d}\x{201e}]+$/u','',$text)??$text;}
    if($text!==''&&aih_web_image_strlen($text)>120)$text=aih_web_image_substr($text,0,120);

    $hasCreate=(bool)preg_match('/\b(?:create|make|build|compose|design|generate|produce|izveido|izveidot|uztaisi|uztaisit|izgatavo|izgatavot|radi|radit|veido|izveidoj)\w*\b/i',$ascii);
    $hasVisual=(bool)preg_match('/\b(?:image|images|picture|pictures|photo|photos|card|poster|collage|moodboard|graphic|banner|flyer|attels|attelu|bilde|bildi|foto|kartin\w*|apsveikum\w*|plakat\w*)\b/i',$ascii);
    $explicit=$hasCard||(bool)preg_match('/\b(?:poster|collage|moodboard|social\s+(?:post|graphic)|instagram\s+post|facebook\s+post|hero\s+banner|banner|flyer|build\s+(?:a\s+)?design|create\s+(?:a\s+)?design|compose\s+(?:an?\s+)?image|from\s+(?:the\s+)?available\s+sources)\b/i',$ascii);
    $auto=$explicit||($hasCreate&&$hasVisual&&$text!=='');

    $template='social';
    $cardType='graphic';
    if(preg_match('/\b(?:birthday|happy\s+birthday|dzimsanas\s+dien\w*)\b/i',$ascii)){$template='birthday';$cardType='birthday';}
    elseif(preg_match('/\b(?:christmas|xmas|new\s+year|ziemassvet\w*|jaun\w*\s+gad\w*)\b/i',$ascii)){$template='christmas';$cardType='christmas';}
    elseif(preg_match('/\b(?:romantic|love|partner|valentine|anniversary|romantisk\w*|milestib\w*|partner\w*|valentin\w*|jubilej\w*)\b/i',$ascii)){$template='romantic';$cardType='romantic';}
    elseif(preg_match('/\b(?:autumn|fall|rudens)\b/i',$ascii)&&$hasCard){$template='autumn';$cardType='autumn';}
    elseif($hasCard){$template='greeting';$cardType='greeting';}
    elseif(preg_match('/\b(?:moodboard|collage|kolaz\w*)\b/i',$ascii))$template='moodboard';
    elseif(preg_match('/\b(?:hero|banner|blog\s+cover|galven\w*\s+att\w*)\b/i',$ascii))$template='hero';
    elseif(preg_match('/\b(?:flyer|poster|plakat\w*)\b/i',$ascii))$template='poster';

    $latvian=(bool)preg_match('/\b(?:latvij\w*|skaist\w*|jauk\w*|rudens|kartin\w*|apsveikum\w*|dzimsanas|varda|ziemassvet\w*|dienu|tekstu|uzrakstu)\b/i',$ascii);
    if($text===''){
        if($cardType==='birthday')$text=$latvian?'Daudz laimes!':'Happy Birthday!';
        elseif($cardType==='christmas')$text=$latvian?'Priecīgus svētkus!':'Season\'s Greetings!';
        elseif($cardType==='romantic')$text=$latvian?'Ar mīlestību!':'With love!';
        elseif(in_array($cardType,['autumn','greeting'],true))$text=$latvian?'Jauku dienu!':'Have a nice day!';
    }
    return ['auto'=>$auto,'template'=>$template,'text'=>$text,'card_type'=>$cardType,'latvian'=>$latvian];
}

function aih_web_image_query(string $prompt): string {
    $plain=aih_web_image_normalize_prompt($prompt);
    $meta=aih_web_image_composer_meta($prompt);
    $ascii=aih_web_image_ascii($plain);
    // Remove overlay copy before tokenising. This handles English and Latvian
    // labels such as text/teksts/uzraksts and a trailing quoted greeting.
    $ascii=preg_replace('/(?:\btext\b|\bmessage\b|\bsaying\b|\bcaption\b|\btekst(?:s|u)?\b|\buzrakst(?:s|u)?\b|\bzina\b|\bvelejums?\b)\s*(?:[:=\-]\s*)?[\x{201c}\x{201d}"]?.*$/ui',' ',$ascii)??$ascii;
    $ascii=preg_replace('/[\x{201c}\x{201d}"]/u',' ',$ascii)??$ascii;
    $tokens=preg_split('/[^A-Za-z0-9]+/',$ascii)?:[];
    $stop=array_fill_keys([
        'please','pls','need','to','want','generate','create','created','creating','make','build','compose','show','find','give','me','image','images','picture','pictures','photo','photos','photograph','photographs','high','quality','better','nicer','nice','colours','colors','available','sources','source','free','card','greeting','social','post','instagram','facebook','poster','flyer','moodboard','collage','design','for','my','partner','partnerim','partnerei','bildem','atteliem','and','or','with','the','a','an','from','have','day','text','message','saying','caption','creat','enice',
        'ludzu','vajag','velos','gribu','izveido','izveidot','uztaisi','uztaisit','izgatavo','izgatavot','radi','radit','attels','attelu','attela','bilde','bildi','bildes','foto','fotografija','fotografiju','jauks','jauku','jauka','skaists','skaistu','skaista','skaisti','kvalitate','kvalitativs','kvalitativu','krasains','krasaina','krasainu','pieejams','pieejamiem','avotiem','avoti','ar','un','no','par','man','manu','mana','teksts','tekstu','uzraksts','uzrakstu','zina','velejums','kartite','kartiti','kartina','kartinu','kartinas','apsveikums','apsveikumu','dzimsanas','dienas','varda','ziemassvetku','plakats','plakatu'
    ],true);
    $map=[
        'latvijas'=>'Latvia','latvija'=>'Latvia','latvian'=>'Latvia','latvia'=>'Latvia',
        'riga'=>'Riga','rigas'=>'Riga','vecriga'=>'Old Riga','old'=>'Old',
        'rudens'=>'autumn','ziema'=>'winter','pavasaris'=>'spring','vasara'=>'summer','autumn'=>'autumn','winter'=>'winter','spring'=>'spring','summer'=>'summer',
        'kafija'=>'coffee','teja'=>'tea','kafejnica'=>'cafe','kafe'=>'cafe','cafe'=>'cafe','coffee'=>'coffee',
        'mezs'=>'forest','meza'=>'forest','daba'=>'nature','jura'=>'sea','pludmale'=>'beach','upe'=>'river','ezers'=>'lake','kalni'=>'mountains','cels'=>'road','pilseta'=>'city','maja'=>'house','majas'=>'house',
        'ziedi'=>'flowers','rozes'=>'roses','sirds'=>'heart','sirdis'=>'hearts','sveces'=>'candles','saule'=>'sun','saulriets'=>'sunset','saullekts'=>'sunrise','lietus'=>'rain','sniegs'=>'snow',
        'suns'=>'dog','kakis'=>'cat','cilveks'=>'person','cilveki'=>'people','masina'=>'car',
        'romantisks'=>'romantic','romantiska'=>'romantic','romantisku'=>'romantic','romantiski'=>'romantic','milestiba'=>'love','silts'=>'warm','silta'=>'warm','gaisma'=>'light','vakars'=>'evening'
    ];
    $out=[];$seen=[];
    foreach($tokens as$token){$token=trim((string)$token);if($token==='')continue;$key=aih_web_image_lower($token);if(isset($stop[$key]))continue;$value=$map[$key]??$token;$dedupe=aih_web_image_lower((string)$value);if(isset($seen[$dedupe]))continue;$seen[$dedupe]=true;$out[]=(string)$value;}
    $q=trim(preg_replace('/\s+/u',' ',implode(' ',$out))??implode(' ',$out));
    if(($meta['template']??'')==='romantic'&&!preg_match('/\b(?:flower|flowers|rose|roses|coffee|cafe|sunset|couple|love|heart|candle|candles|autumn|winter|spring|summer|riga|latvia|romantic)\b/i',$q))$q=trim($q.' romantic flowers warm light');
    if(aih_web_image_strlen($q)<3){$type=(string)($meta['card_type']??'');if($type==='birthday')$q='birthday celebration flowers cake balloons';elseif($type==='christmas')$q='Christmas festive lights winter candles';elseif($type==='autumn')$q='autumn leaves cozy warm landscape';elseif($type==='greeting')$q='flowers sunlight warm greeting';elseif(($meta['template']??'')==='romantic')$q='romantic flowers warm light';else $q='nature landscape';}
    $artIntent=(bool)preg_match('/\b(?:art|painting|paintings|painter|artist|museum|portrait|sculpture|renaissance|arcimboldo|da vinci|monet|picasso)\b/i',$ascii);
    if(!$artIntent&&!preg_match('/\bphoto(?:graph)?s?\b/i',$q))$q=trim($q.' photo');
    return aih_web_image_substr($q,0,180);
}

function aih_web_image_query_variants(string $prompt): array {
    $base=aih_web_image_query($prompt);$variants=[$base];$lower=aih_web_image_lower(aih_web_image_ascii($base));
    if(str_contains($lower,'latvia')&&str_contains($lower,'autumn')){
        foreach(['Latvia autumn','autumn landscape Latvia','Riga autumn'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;
    }elseif(str_contains($lower,'latvia')){
        foreach(['Latvia landscape','Riga Latvia'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;
    }
    if(str_contains($lower,'riga')&&str_contains($lower,'autumn')&&!in_array('Riga autumn',$variants,true))$variants[]='Riga autumn';
    $meta=aih_web_image_composer_meta($prompt);
    $cardType=(string)($meta['card_type']??'');
    if($cardType==='birthday'){foreach(['birthday celebration flowers','birthday cake balloons elegant'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;}
    elseif($cardType==='christmas'){foreach(['Christmas warm lights winter','Christmas candles festive'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;}
    elseif($cardType==='autumn'){foreach(['cozy autumn coffee maple leaves golden hour bokeh','autumn lake sunset warm leaves cozy cup','autumn still life candle coffee leaves','golden autumn terrace coffee warm light'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;}
    elseif($cardType==='greeting'){foreach(['beautiful greeting flowers golden light','cozy flowers coffee light','warm greeting flowers sunlight'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;}
    if(($meta['template']??'')==='romantic'){
        foreach(['romantic warm light flowers','romantic coffee flowers candle'] as$v)if(!in_array($v,$variants,true))$variants[]=$v;
    }
    $words=preg_split('/\s+/u',$base)?:[];
    if(count($words)>2){$relaxed=implode(' ',array_slice($words,-2));if(aih_web_image_strlen($relaxed)>=3&&!in_array($relaxed,$variants,true))$variants[]=$relaxed;}
    return array_slice(array_values(array_filter($variants,fn($v)=>trim((string)$v)!=='')),0,6);
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

function aih_unsplash_image_search(string $query,string $key,int $limit=12,string $orientation=''): array {
    $key=trim($key);if($key==='')return ['ok'=>true,'code'=>0,'rows'=>[]];
    $params=['query'=>$query,'per_page'=>max(4,min(30,$limit)),'content_filter'=>'high','order_by'=>'relevant'];if(in_array($orientation,['portrait','landscape','squarish'],true))$params['orientation']=$orientation;$url='https://api.unsplash.com/search/photos?'.http_build_query($params);
    $r=aih_http('GET',$url,null,['Accept: application/json','Authorization: Client-ID '.$key],15);
    if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'Unsplash HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$rows=[];
    foreach((is_array($j)?($j['results']??[]):[]) as$x){if(!is_array($x))continue;$urls=is_array($x['urls']??null)?$x['urls']:[];$preview=aih_web_image_url($urls['small']??$urls['regular']??'');$image=aih_web_image_url($urls['full']??$urls['regular']??'');if($preview===''&&$image==='')continue;$user=is_array($x['user']??null)?$x['user']:[];$links=is_array($x['links']??null)?$x['links']:[];$rows[]=['id'=>'us-'.preg_replace('/[^A-Za-z0-9_-]/','',(string)($x['id']??aih_new_id('img'))),'title'=>aih_web_image_plain($x['description']??$x['alt_description']??'Unsplash photo'),'preview_url'=>$preview!==''?$preview:$image,'image_url'=>$image!==''?$image:$preview,'source_url'=>aih_web_image_url($links['html']??''),'license_url'=>'https://unsplash.com/license','creator'=>aih_web_image_plain($user['name']??''),'license'=>'Unsplash License','source'=>'Unsplash','source_key'=>'unsplash','width'=>(int)($x['width']??0),'height'=>(int)($x['height']??0)];}
    return ['ok'=>true,'code'=>$r['code'],'rows'=>$rows];
}

function aih_pexels_image_search(string $query,string $key,int $limit=12,string $orientation=''): array {
    $key=trim($key);if($key==='')return ['ok'=>true,'code'=>0,'rows'=>[]];
    $params=['query'=>$query,'per_page'=>max(4,min(30,$limit))];if(in_array($orientation,['portrait','landscape','square'],true))$params['orientation']=$orientation;$url='https://api.pexels.com/v1/search?'.http_build_query($params);
    $r=aih_http('GET',$url,null,['Accept: application/json','Authorization: '.$key],15);
    if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'Pexels HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$rows=[];
    foreach((is_array($j)?($j['photos']??[]):[]) as$x){if(!is_array($x))continue;$src=is_array($x['src']??null)?$x['src']:[];$preview=aih_web_image_url($src['medium']??$src['large']??'');$image=aih_web_image_url($src['original']??$src['large2x']??'');if($preview===''&&$image==='')continue;$rows[]=['id'=>'px-'.preg_replace('/[^A-Za-z0-9_-]/','',(string)($x['id']??aih_new_id('img'))),'title'=>aih_web_image_plain($x['alt']??'Pexels photo'),'preview_url'=>$preview!==''?$preview:$image,'image_url'=>$image!==''?$image:$preview,'source_url'=>aih_web_image_url($x['url']??''),'license_url'=>'https://www.pexels.com/license/','creator'=>aih_web_image_plain($x['photographer']??''),'license'=>'Pexels License','source'=>'Pexels','source_key'=>'pexels','width'=>(int)($x['width']??0),'height'=>(int)($x['height']??0)];}
    return ['ok'=>true,'code'=>$r['code'],'rows'=>$rows];
}

function aih_pixabay_image_search(string $query,string $key,int $limit=12): array {
    $key=trim($key);if($key==='')return ['ok'=>true,'code'=>0,'rows'=>[]];
    $url='https://pixabay.com/api/?'.http_build_query(['key'=>$key,'q'=>$query,'image_type'=>'photo','safesearch'=>'true','per_page'=>max(4,min(50,$limit)),'order'=>'popular']);
    $r=aih_http('GET',$url,null,['Accept: application/json'],15);
    if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'Pixabay HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$rows=[];
    foreach((is_array($j)?($j['hits']??[]):[]) as$x){if(!is_array($x))continue;$preview=aih_web_image_url($x['webformatURL']??'');$image=aih_web_image_url($x['largeImageURL']??$x['fullHDURL']??'');if($preview===''&&$image==='')continue;$rows[]=['id'=>'pb-'.preg_replace('/[^A-Za-z0-9_-]/','',(string)($x['id']??aih_new_id('img'))),'title'=>aih_web_image_plain($x['tags']??'Pixabay photo'),'preview_url'=>$preview!==''?$preview:$image,'image_url'=>$image!==''?$image:$preview,'source_url'=>aih_web_image_url($x['pageURL']??''),'license_url'=>'https://pixabay.com/service/license-summary/','creator'=>aih_web_image_plain($x['user']??''),'license'=>'Pixabay Content License','source'=>'Pixabay','source_key'=>'pixabay','width'=>(int)($x['imageWidth']??0),'height'=>(int)($x['imageHeight']??0)];}
    return ['ok'=>true,'code'=>$r['code'],'rows'=>$rows];
}

function aih_nasa_image_search(string $query,int $limit=10): array {
    $url='https://images-api.nasa.gov/search?'.http_build_query(['q'=>$query,'media_type'=>'image','page_size'=>max(4,min(25,$limit))]);
    $r=aih_http('GET',$url,null,['Accept: application/json'],15);if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'NASA Images HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$rows=[];
    foreach((is_array($j)?($j['collection']['items']??[]):[]) as$x){if(!is_array($x))continue;$data=is_array($x['data'][0]??null)?$x['data'][0]:[];$links=is_array($x['links']??null)?$x['links']:[];$preview='';foreach($links as$l){if(is_array($l)&&($l['rel']??'')==='preview'){$preview=aih_web_image_url($l['href']??'');break;}}if($preview==='')continue;$id=trim((string)($data['nasa_id']??''));$rows[]=['id'=>'nasa-'.preg_replace('/[^A-Za-z0-9_-]/','',$id?:aih_new_id('img')),'title'=>aih_web_image_plain($data['title']??'NASA image'),'preview_url'=>$preview,'image_url'=>$preview,'source_url'=>$id!==''?'https://images.nasa.gov/details/'.rawurlencode($id):'https://images.nasa.gov/','license_url'=>'https://www.nasa.gov/nasa-brand-center/images-and-media/','creator'=>aih_web_image_plain($data['photographer']??$data['secondary_creator']??'NASA'),'license'=>'NASA media usage guidelines','source'=>'NASA Images','source_key'=>'nasa','width'=>0,'height'=>0];}
    return ['ok'=>true,'code'=>$r['code'],'rows'=>$rows];
}

function aih_met_image_search(string $query,int $limit=8): array {
    $r=aih_http('GET','https://collectionapi.metmuseum.org/public/collection/v1/search?'.http_build_query(['hasImages'=>'true','q'=>$query]),null,['Accept: application/json'],15);if($r['code']<200||$r['code']>=300)return ['ok'=>false,'code'=>$r['code'],'rows'=>[],'error'=>'The Met HTTP '.$r['code']];
    $j=json_decode((string)$r['body'],true);$ids=array_slice(array_values(array_filter(array_map('intval',(array)($j['objectIDs']??[])))),0,max(4,min(12,$limit)));if(!$ids)return ['ok'=>true,'code'=>$r['code'],'rows'=>[]];
    $req=[];foreach($ids as$id)$req[]=['url'=>'https://collectionapi.metmuseum.org/public/collection/v1/objects/'.$id,'headers'=>['Accept: application/json']];$resp=aih_multi_get($req,20);$rows=[];
    foreach($resp as$rr){if(($rr['code']??0)<200||($rr['code']??0)>=300)continue;$x=json_decode((string)($rr['body']??''),true);if(!is_array($x)||empty($x['isPublicDomain']))continue;$preview=aih_web_image_url($x['primaryImageSmall']??'');$image=aih_web_image_url($x['primaryImage']??'');if($preview===''&&$image==='')continue;$rows[]=['id'=>'met-'.(int)($x['objectID']??0),'title'=>aih_web_image_plain($x['title']??'The Met artwork'),'preview_url'=>$preview!==''?$preview:$image,'image_url'=>$image!==''?$image:$preview,'source_url'=>aih_web_image_url($x['objectURL']??''),'license_url'=>'https://www.metmuseum.org/policies/terms-and-conditions#open-access','creator'=>aih_web_image_plain($x['artistDisplayName']??''),'license'=>'Public Domain / Open Access','source'=>'The Met Open Access','source_key'=>'met','width'=>0,'height'=>0];}
    return ['ok'=>true,'code'=>200,'rows'=>$rows];
}

function aih_loremflickr_image_search(string $query,int $limit=4): array {
    $terms=preg_replace('/\s+/u',',',trim($query))??trim($query);$terms=rawurlencode($terms!==''?$terms:'nature');$rows=[];$n=max(1,min(6,$limit));
    for($i=1;$i<=$n;$i++){$url='https://loremflickr.com/1280/960/'.$terms.'?lock='.(abs(crc32($query.'|'.$i))%999999+1);$rows[]=['id'=>'lf-'.$i.'-'.substr(sha1($query),0,8),'title'=>'LoremFlickr · '.$query,'preview_url'=>$url,'image_url'=>$url,'source_url'=>'https://loremflickr.com/','license_url'=>'https://loremflickr.com/','creator'=>'Flickr community','license'=>'Creative Commons photo via LoremFlickr','source'=>'LoremFlickr','source_key'=>'loremflickr','width'=>1280,'height'=>960];}
    return ['ok'=>true,'code'=>200,'rows'=>$rows];
}

function aih_web_image_resource_links(string $query): array {
    $q=rawurlencode($query);$slug=rawurlencode(str_replace(' ','-',trim($query)));
    return [
        ['name'=>'Unsplash','url'=>'https://unsplash.com/s/photos/'.$slug],['name'=>'Pexels','url'=>'https://www.pexels.com/search/'.$q.'/'],['name'=>'Pixabay','url'=>'https://pixabay.com/images/search/'.$q.'/'],['name'=>'Freepik','url'=>'https://www.freepik.com/search?query='.$q],['name'=>'Burst','url'=>'https://www.shopify.com/stock-photos/search?search='.$q],['name'=>'StockSnap','url'=>'https://stocksnap.io/search/'.$q],['name'=>'Kaboompics','url'=>'https://kaboompics.com/gallery?search='.$q],['name'=>'Gratisography','url'=>'https://gratisography.com/?s='.$q],['name'=>'Openverse','url'=>'https://openverse.org/search/image?q='.$q],['name'=>'Wikimedia','url'=>'https://commons.wikimedia.org/w/index.php?search='.$q.'&title=Special:MediaSearch&type=image'],['name'=>'Rawpixel','url'=>'https://www.rawpixel.com/search/'.$q],['name'=>'unDraw','url'=>'https://undraw.co/illustrations'],['name'=>'SVG Repo','url'=>'https://www.svgrepo.com/vectors/'.$slug.'/'],['name'=>'Lucide','url'=>'https://lucide.dev/icons/?search='.$q],['name'=>'OpenMoji','url'=>'https://openmoji.org/library/'],['name'=>'Reshot','url'=>'https://www.reshot.com/search/'.$q.'/'],['name'=>'NASA Images','url'=>'https://images.nasa.gov/search?q='.$q],['name'=>'The Met','url'=>'https://www.metmuseum.org/art/collection/search?q='.$q],['name'=>'Lorem Picsum','url'=>'https://picsum.photos/'],['name'=>'LoremFlickr','url'=>'https://loremflickr.com/']
    ];
}

function aih_web_image_search(string $prompt,int $limit=8,?array $settings=null): array {
    $settings=$settings??aih_settings();$images=$settings['images']??[];$queries=aih_web_image_query_variants($prompt);$query=$queries[0]??aih_web_image_query($prompt);$want=max(6,min(18,$limit));$all=[];$errors=[];$used=[];$status=[];
    $cacheTtl=max(60,min(3600,(int)($images['web_image_cache_ttl']??900)));$cacheKey=sha1($query.'|'.implode('|',$queries).'|'.$want.'|'.(!empty($images['unsplash_access_key'])?'u':'').(!empty($images['pexels_api_key'])?'p':'').(!empty($images['pixabay_api_key'])?'x':''));$cacheFile=AIH_CACHE.'/web-image-'.$cacheKey.'.json';$cached=aih_read_json($cacheFile,[]);if(is_array($cached)&&!empty($cached['ok'])&&(int)($cached['_cached_at']??0)>time()-$cacheTtl){unset($cached['_cached_at']);$cached['cached']=true;return $cached;}
    $qLower=aih_web_image_lower(aih_web_image_ascii(implode(' ',$queries)));$composerMeta=aih_web_image_composer_meta($prompt);$orientation=!empty($composerMeta['auto'])?'portrait':'';
    $sources=[
        ['unsplash',2,fn($q)=>aih_unsplash_image_search($q,(string)($images['unsplash_access_key']??''),$want,$orientation),trim((string)($images['unsplash_access_key']??''))!==''],
        ['pexels',2,fn($q)=>aih_pexels_image_search($q,(string)($images['pexels_api_key']??''),$want,$orientation),trim((string)($images['pexels_api_key']??''))!==''],
        ['pixabay',2,fn($q)=>aih_pixabay_image_search($q,(string)($images['pixabay_api_key']??''),$want),trim((string)($images['pixabay_api_key']??''))!==''],
        ['openverse',2,fn($q)=>aih_openverse_image_search($q,$want*2),true],
        ['wikimedia',2,fn($q)=>aih_wikimedia_image_search($q,$want*2),true],
        ['nasa',1,fn($q)=>aih_nasa_image_search($q,min(8,$want)),(bool)preg_match('/\b(?:space|nasa|moon|earth|planet|rocket|astronaut|galaxy|satellite|mars|jupiter)\b/i',$qLower)],
        ['met',1,fn($q)=>aih_met_image_search($q,min(8,$want)),(bool)preg_match('/\b(?:art|painting|museum|portrait|sculpture|historical|history|vintage|ancient|renaissance)\b/i',$qLower)],
    ];
    foreach($sources as[$name,$variantLimit,$fn,$enabled]){
        if(!$enabled){$status[$name]='skipped';continue;}
        $sourceRows=[];$lastError='';$attempted=0;
        foreach(array_slice($queries,0,$variantLimit) as$qq){$attempted++;try{$res=$fn($qq);}catch(Throwable $e){$res=['ok'=>false,'rows'=>[],'error'=>$name.' '.$e->getMessage()];}
            if(!empty($res['ok'])&&!empty($res['rows'])){
                $sourceRows=array_merge($sourceRows,$res['rows']);
                if(count($sourceRows)>=$want*2)break;
            }
            if(!empty($res['error']))$lastError=(string)$res['error'];
        }
        if($sourceRows){$all=array_merge($all,$sourceRows);$used[]=$name;$status[$name]='ok '.count($sourceRows);}else{$status[$name]=$lastError!==''?$lastError:'no matches';if($lastError!=='')$errors[]=$lastError;}
    }
    // Do not inject LoremFlickr placeholders into automatic results. They are useful
    // as a manual prototype source, but keyword quality/hotlink reliability is too
    // inconsistent for an automatic card composer.
    $scoreQuery=$query;$seen=[];$rows=[];
    foreach($all as$row){$key=strtolower((string)($row['image_url']??$row['source_url']??''));if($key===''||isset($seen[$key]))continue;$seen[$key]=1;$row['_score']=aih_web_image_score($row,$scoreQuery);$source=(string)($row['source_key']??'');$row['_score']+=match($source){'unsplash'=>1.8,'pexels'=>1.7,'pixabay'=>1.4,'wikimedia'=>1.2,'openverse'=>1.0,'nasa'=>0.7,'met'=>0.6,default=>0.0};if(!empty($composerMeta['auto'])){$hay=aih_web_image_lower((string)(($row['title']??'').' '.($row['creator']??'')));foreach(['golden','warm','sunset','autumn','leaves','maple','flower','flowers','coffee','cup','candle','cozy','bokeh','lake','light','glow','table','terrace','still life'] as$good)if(aih_web_image_pos($hay,$good)!==false)$row['_score']+=0.62;foreach(['road','construction','document','diagram','map','sculpture','waiting','archive','restoration','survey','mud','ruin','industrial','traffic','parking'] as$bad)if(aih_web_image_pos($hay,$bad)!==false)$row['_score']-=1.7;if(!preg_match('/\b(?:art|painting|paintings|painter|artist|museum|portrait|sculpture|renaissance|arcimboldo|da vinci|monet|picasso)\b/i',$qLower)){foreach(['painting','paintings','artwork','archive','archival','museum','historical painting'] as$bad)if(aih_web_image_pos($hay,$bad)!==false)$row['_score']-=5.0;}$w=max(0,(int)($row['width']??0));$h=max(0,(int)($row['height']??0));if($w>0&&$h>0){$ratio=$w/$h;if($ratio>=.62&&$ratio<=.95)$row['_score']+=1.25;elseif($ratio>1.65)$row['_score']-=.45;}}$rows[]=$row;}
    usort($rows,fn($a,$b)=>(float)($b['_score']??0)<=>(float)($a['_score']??0));$rows=array_slice($rows,0,$want);foreach($rows as&$row)unset($row['_score']);unset($row);
    if(!$rows)return ['ok'=>false,'code'=>503,'error'=>'No matching reusable web images were returned'.($errors?' ('.implode('; ',array_slice(array_unique($errors),0,4)).')':''),'query'=>$query,'searched_queries'=>$queries,'source_status'=>$status,'resource_links'=>aih_web_image_resource_links($query),'headers'=>[]];
    $labels=array_values(array_unique(array_map(fn($r)=>(string)($r['source']??''),$rows)));$provider='Free web images · '.implode(' + ',array_slice($labels,0,4));if(count($labels)>4)$provider.=' + more';
    $result=['ok'=>true,'pending'=>false,'result_type'=>'web_search','provider'=>$provider,'provider_id'=>'free_web','query'=>$query,'searched_queries'=>$queries,'results'=>$rows,'sources_used'=>array_values(array_unique($used)),'source_status'=>$status,'resource_links'=>aih_web_image_resource_links($query),'composer'=>$composerMeta,'notice'=>'Reusable-source results are ranked for relevance and image size. Select images or ask to create an image with text to build a finished design automatically. Check each source/licence before reuse.','headers'=>[]];aih_write_json($cacheFile,$result+['_cached_at'=>time()]);return $result;
}


function aih_remote_image_url_is_public(string $url): bool {
    $parts=parse_url($url);if(!is_array($parts))return false;$scheme=strtolower((string)($parts['scheme']??''));$host=strtolower((string)($parts['host']??''));
    if(!in_array($scheme,['http','https'],true)||$host===''||$host==='localhost'||str_ends_with($host,'.local'))return false;
    if(filter_var($host,FILTER_VALIDATE_IP))return (bool)filter_var($host,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE);
    $ips=@gethostbynamel($host);if(!$ips)return false;foreach($ips as$ip)if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))return false;return true;
}

function aih_resolve_redirect_url(string $base,string $location): string {
    $location=trim($location);if($location==='')return '';
    if(preg_match('#^https?://#i',$location))return $location;
    $bp=parse_url($base);if(!is_array($bp)||empty($bp['host']))return '';$scheme=(string)($bp['scheme']??'https');$host=(string)$bp['host'];$port=isset($bp['port'])?':'.(int)$bp['port']:'';
    if(str_starts_with($location,'//'))return $scheme.':'.$location;
    if(str_starts_with($location,'/'))return $scheme.'://'.$host.$port.$location;
    $path=(string)($bp['path']??'/');$dir=rtrim(str_replace('\\','/',dirname($path)),'/');return $scheme.'://'.$host.$port.($dir===''?'':$dir).'/'.$location;
}

function aih_fetch_public_image(string $url,int $maxRedirects=4): array {
    $current=trim($url);
    for($hop=0;$hop<=$maxRedirects;$hop++){
        if(!aih_remote_image_url_is_public($current))return ['code'=>422,'body'=>'','content_type'=>'','headers'=>[],'error'=>'Blocked non-public image URL'];
        $parts=parse_url($current);$host=(string)($parts['host']??'');$scheme=strtolower((string)($parts['scheme']??'https'));$port=(int)($parts['port']??($scheme==='https'?443:80));$headers=[];$body='';$code=0;$ctype='';$error='';
        if(function_exists('curl_init')){
            $ips=@gethostbynamel($host)?:[];$ip='';foreach($ips as$candidate)if(filter_var($candidate,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)){$ip=$candidate;break;}
            $ch=curl_init($current);$opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>12,CURLOPT_TIMEOUT=>30,CURLOPT_USERAGENT=>'FreeAIHub63/'.AIH_VERSION,CURLOPT_HTTPHEADER=>['Accept: image/*'],CURLOPT_HEADERFUNCTION=>function($ch,$line)use(&$headers){$len=strlen($line);$p=strpos($line,':');if($p!==false){$headers[strtolower(trim(substr($line,0,$p)))]=trim(substr($line,$p+1));}return $len;}];if($ip!=='')$opts[CURLOPT_RESOLVE]=[$host.':'.$port.':'.$ip];curl_setopt_array($ch,$opts);$data=curl_exec($ch);$body=$data===false?'':(string)$data;$code=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$ctype=(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE);$error=(string)curl_error($ch);curl_close($ch);
        }else{
            $ctx=stream_context_create(['http'=>['method'=>'GET','header'=>"Accept: image/*\r\nUser-Agent: FreeAIHub63/".AIH_VERSION."\r\n",'timeout'=>30,'ignore_errors'=>true,'follow_location'=>0,'max_redirects'=>0]]);$data=@file_get_contents($current,false,$ctx);$body=$data===false?'':(string)$data;$raw=$http_response_header??[];foreach($raw as$line){if(preg_match('#^HTTP/\\S+\\s+(\\d+)#i',$line,$m))$code=(int)$m[1];elseif(($p=strpos($line,':'))!==false){$k=strtolower(trim(substr($line,0,$p)));$v=trim(substr($line,$p+1));$headers[$k]=$v;if($k==='content-type')$ctype=$v;}}if($data===false)$error='HTTP stream request failed';
        }
        if($code>=300&&$code<400&&!empty($headers['location'])){$next=aih_resolve_redirect_url($current,(string)$headers['location']);if($next==='')return ['code'=>$code,'body'=>'','content_type'=>$ctype,'headers'=>$headers,'error'=>'Invalid redirect'];$current=$next;continue;}
        return ['code'=>$code,'body'=>$body,'content_type'=>$ctype,'headers'=>$headers,'error'=>$error,'effective_url'=>$current];
    }
    return ['code'=>508,'body'=>'','content_type'=>'','headers'=>[],'error'=>'Too many image redirects'];
}

function aih_cache_remote_image(string $url): array {
    $url=trim($url);if(!aih_remote_image_url_is_public($url))return ['ok'=>false,'code'=>422,'error'=>'Only public HTTP(S) image URLs can be used by the composer.'];
    $r=aih_fetch_public_image($url,4);$code=(int)($r['code']??0);
    if($code<200||$code>=300)return ['ok'=>false,'code'=>$code?:502,'error'=>'Could not fetch source image'.($code?' (HTTP '.$code.')':'').(!empty($r['error'])?' '.$r['error']:'')];
    $bytes=(string)($r['body']??'');if(strlen($bytes)>15*1024*1024)return ['ok'=>false,'code'=>413,'error'=>'Source image is larger than the 15 MB composer limit.'];
    $ct=strtolower((string)($r['content_type']??''));$looksLikeImage=str_starts_with($ct,'image/')||str_starts_with($bytes,"\x89PNG")||str_starts_with($bytes,"\xFF\xD8\xFF")||(substr($bytes,0,4)==='RIFF'&&substr($bytes,8,4)==='WEBP');
    if(!$looksLikeImage)return ['ok'=>false,'code'=>415,'error'=>'Remote resource is not a supported image.'];
    $saved=aih_image_save_bytes($bytes,$ct!==''?$ct:'image/jpeg','composer-source');if(!empty($saved['ok']))$saved['source_url']=(string)($r['effective_url']??$url);return $saved;
}

function aih_save_composed_data_url(string $dataUrl,string $prompt=''): array {
    if(!preg_match('#^data:image/(png|jpeg|jpg|webp);base64,([A-Za-z0-9+/=\\r\\n]+)$#i',$dataUrl,$m))return ['ok'=>false,'code'=>422,'error'=>'Invalid composed image data.'];
    $bytes=base64_decode(preg_replace('/\\s+/','',$m[2]),true);if($bytes===false||strlen($bytes)<256)return ['ok'=>false,'code'=>422,'error'=>'Composed image is empty.'];
    if(strlen($bytes)>20*1024*1024)return ['ok'=>false,'code'=>413,'error'=>'Composed image exceeds 20 MB.'];
    $type=strtolower($m[1]);$ct=$type==='png'?'image/png':($type==='webp'?'image/webp':'image/jpeg');$saved=aih_image_save_bytes($bytes,$ct,'design');
    if(!empty($saved['ok'])){$saved['provider']='Free Source Composer';$saved['prompt']=aih_web_image_substr(trim($prompt),0,500);}return $saved;
}

function aih_aihorde_prompt_is_stylized(string $prompt): bool {
    return (bool)preg_match('/\b(?:anime|manga|cartoon|illustration|watercolou?r|oil painting|pixel art|comic|sketch|vector|3d render|cgi|fantasy art|concept art)\b/ui',$prompt);
}

function aih_image_prompt_prefers_generation(string $prompt): bool {
    // Ordinary real-world/photo requests use existing web photography before anonymous
    // generation. Clearly stylised/original design requests keep generation first.
    return aih_aihorde_prompt_is_stylized($prompt) || (bool)preg_match('/\b(?:logo|brand mark|mascot|icon set|poster design|product mockup|surreal|fantasy|sci[- ]?fi|character design|3d render|cgi render)\b/ui',$prompt);
}

function aih_image_speed_mode(array $settings): string { $mode=(string)(($settings['images']??[])['speed_mode']??'fast');return in_array($mode,['fast','balanced','quality'],true)?$mode:'fast'; }
function aih_aihorde_queue_limit(array $settings): int { $mode=aih_image_speed_mode($settings);if($mode==='quality')return 0;$configured=max(15,min(900,(int)(($settings['images']??[])['aihorde_queue_limit']??45)));return $mode==='fast'?min(60,$configured):max(60,$configured); }

function aih_image_prompt_order(array $order,string $prompt,string $requested,array $settings=[]): array {
    if($requested!=='auto')return $order;
    return array_values(array_filter($order, fn($id)=>$id!=='free_web' && aih_free_provider_allowed((string)$id)));
}

function aih_card_background_prompt(string $prompt): string {
    $meta=aih_web_image_composer_meta($prompt);if(empty($meta['auto']))return $prompt;
    $q=aih_web_image_query($prompt);$type=(string)($meta['card_type']??'greeting');
    $style=match($type){
        'autumn'=>'beautiful premium autumn greeting card background, golden hour, rich orange and amber leaves, cozy warm light, elegant seasonal still life, soft bokeh, tasteful composition, generous clean space for greeting text',
        'birthday'=>'beautiful premium birthday greeting card background, elegant flowers, warm celebratory lights, tasteful confetti, soft bokeh, joyful luxury composition, generous clean space for greeting text',
        'christmas'=>'beautiful premium Christmas greeting card background, warm festive lights, candles, evergreen details, gold accents, cozy winter atmosphere, soft bokeh, generous clean space for greeting text',
        'romantic'=>'beautiful premium romantic greeting card background, warm sunset light, flowers, candles, subtle hearts, elegant soft bokeh, tasteful luxury composition, generous clean space for greeting text',
        default=>'beautiful premium greeting card background, warm natural light, elegant seasonal details, soft bokeh, tasteful editorial composition, generous clean space for greeting text'
    };
    return trim(($q!==''?$q.', ':'').$style.', no words, no letters, no typography, no watermark');
}

function aih_aihorde_quality_prompt(string $prompt): string {
    $p=trim($prompt);if($p===''||str_contains($p,'###'))return $p;
    if(aih_aihorde_prompt_is_stylized($p))return str_replace('+',',',$p).' ### blurry, low detail, text, watermark, malformed anatomy, duplicate objects';
    $p=str_replace('+',',',$p);
    return $p.', realistic professional photograph, natural lighting, balanced rich colours, sharp focus, fine detail ### anime, cartoon, illustration, blurry, low detail, text, watermark, distorted anatomy, duplicate objects';
}

function aih_image_call(string $id,string $prompt,array $s): array {
    if(!aih_free_provider_allowed($id))return aih_free_blocked();
    $images=$s['images']??[];$p=$s['providers']??[];$keys=$p['keys']??[];
    if($id==='free_web')return aih_web_image_search($prompt,12,$s);
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
        $timeout=max(45,min(300,(int)($images['aihorde_timeout']??180)));$qualityPrompt=aih_aihorde_quality_prompt(aih_card_background_prompt($prompt));
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

function aih_aihorde_pick_model_from_rows(array $rows,string $prompt='',int $maxEta=0): string {
    $best='';$bestScore=INF;$stylized=aih_aihorde_prompt_is_stylized($prompt);
    foreach($rows as$row){
        if(!is_array($row))continue;$name=trim((string)($row['name']??''));$count=max(0,(int)($row['count']??0));
        if($name===''||$count<1)continue;
        $eta=max(0.0,(float)($row['eta']??999999));$queued=max(0.0,(float)($row['queued']??0));$performance=max(0.0,(float)($row['performance']??0));
        if($maxEta>0&&$eta>$maxEta*2.2)continue;
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
    $j=json_decode((string)$r['body'],true);$model=is_array($j)?aih_aihorde_pick_model_from_rows($j,$prompt,aih_aihorde_queue_limit($s)):'';
    if($model!=='')aih_write_json($cache,['model'=>$model,'expires'=>time()+300]);
    return $model;
}

function aih_aihorde_submit(string $prompt,array $s): array {
    $images=$s['images']??[];$model=aih_aihorde_auto_model($s,$prompt);$steps=max(4,min(30,(int)($images['aihorde_steps']??16)));$qualityPrompt=aih_aihorde_quality_prompt(aih_card_background_prompt($prompt));
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
    return ['ok'=>true,'pending'=>true,'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'model'=>$model,'steps'=>$steps,'wait_time'=>0,'queue_position'=>0,'queue_limit'=>aih_aihorde_queue_limit($s),'is_possible'=>true];
}

function aih_aihorde_cancel(string $requestId): array {
    if(!preg_match('/^[A-Za-z0-9-]{10,100}$/',$requestId))return ['ok'=>false,'error'=>'Invalid AI Horde request id'];
    $r=aih_http('DELETE','https://aihorde.net/api/v2/generate/status/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.aih_aihorde_client()],8);
    return ['ok'=>$r['code']>=200&&$r['code']<300,'code'=>$r['code'],'request_id'=>$requestId];
}

function aih_aihorde_status(string $requestId,array $s): array {
    if(!preg_match('/^[A-Za-z0-9-]{10,100}$/',$requestId))return ['ok'=>false,'code'=>422,'error'=>'Invalid AI Horde request id'];
    $client=aih_aihorde_client();$check=aih_http('GET','https://aihorde.net/api/v2/generate/check/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],10);
    if($check['code']===429||$check['code']===0||$check['code']>=500)return ['ok'=>true,'pending'=>true,'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'temporary'=>true,'retry_after'=>5];
    if($check['code']<200||$check['code']>=300)return ['ok'=>false,'code'=>$check['code'],'error'=>trim(substr((string)($check['body']?:$check['error']?:'AI Horde status failed'),0,700)),'headers'=>$check['headers']??[]];
    $j=json_decode((string)$check['body'],true);if(!is_array($j))return ['ok'=>true,'pending'=>true,'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'temporary'=>true,'retry_after'=>5];
    if(!empty($j['faulted']))return ['ok'=>false,'code'=>503,'error'=>'AI Horde generation faulted','headers'=>$check['headers']??[]];
    $progress=['ok'=>true,'pending'=>empty($j['done']),'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'wait_time'=>max(0,(int)($j['wait_time']??0)),'queue_position'=>max(0,(int)($j['queue_position']??0)),'queue_limit'=>aih_aihorde_queue_limit($s),'is_possible'=>array_key_exists('is_possible',$j)?(bool)$j['is_possible']:true];
    if(empty($j['done']))return $progress;
    $status=aih_http('GET','https://aihorde.net/api/v2/generate/status/'.rawurlencode($requestId),null,['Accept: application/json','Client-Agent: '.$client],30);
    if($status['code']===429||$status['code']===0||$status['code']>=500){$progress['pending']=true;$progress['temporary']=true;$progress['retry_after']=5;return $progress;}
    if($status['code']===0||$status['code']===429||$status['code']>=500)return array_merge($progress,['pending'=>true,'temporary'=>true,'retry_after'=>5]);
    $sj=json_decode((string)$status['body'],true);if($status['code']>=200&&$status['code']<300&&!is_array($sj))return array_merge($progress,['pending'=>true,'temporary'=>true,'retry_after'=>5]);$generation=is_array($sj)?($sj['generations'][0]??[]):[];if(is_array($generation)&&aih_aihorde_generation_censored($generation))return ['ok'=>false,'code'=>422,'error'=>'AI Horde worker censored this result. This can be a false positive on SFW prompts; retry or choose Free web images (multi-source) for real-world/reference photography.','headers'=>$status['headers']??[],'censored'=>true];$img=is_array($generation)?trim((string)($generation['img']??'')):'';
    if($status['code']>=200&&$status['code']<300&&$img!==''){
        if(preg_match('#^https?://#i',$img))$saved=aih_image_download_url($img);else{$raw=preg_replace('#^data:image/[^;]+;base64,#','',$img);$bin=base64_decode((string)$raw,true);$saved=$bin===false?['ok'=>false,'code'=>0,'error'=>'AI Horde returned invalid image data','headers'=>[]]:aih_image_save_bytes($bin,'image/webp');}
        if(!empty($saved['ok']))return ['ok'=>true,'pending'=>false,'file'=>$saved['file'],'provider'=>'AI Horde · anonymous community queue','provider_id'=>'aihorde','request_id'=>$requestId,'model'=>(string)($generation['model']??''),'seed'=>(string)($generation['seed']??'')];
        return $saved+['pending'=>false];
    }
    $err=is_array($sj)?((string)($sj['message']??$sj['error']??'')):(string)$status['body'];
    return ['ok'=>false,'code'=>$status['code'],'error'=>trim(substr($err?:'AI Horde completed without an image',0,700)),'headers'=>$status['headers']??[]];
}

function aih_image_start(string $prompt,string $requested='auto',?array $settings=null): array {
    $settings=aih_free_settings($settings??aih_settings());$catalog=aih_image_catalog();$images=$settings['images']??[];$state=aih_provider_state();$tried=[];
    $order=is_array($images['order']??null)?$images['order']:['sdwebui','comfyui','cloudflare_image','alibaba_qwen_image','huggingface_image','siliconflow_image','pollinations','free_web','aihorde'];
    if($requested!=='auto'&&!aih_free_provider_allowed($requested))return aih_free_blocked();
    if($requested!=='auto')$order=[$requested];
    else $order=aih_image_prompt_order($order,$prompt,$requested,$settings);
    foreach($order as$id){
        $id=(string)$id;if(!isset($catalog[$id]))continue;$stateId=aih_image_state_id($id);$cool=(int)($state[$stateId]['cooldown_until']??0);
        if($cool>time()){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'cooldown','until'=>$cool];continue;}
        if(!aih_image_provider_configured($id,$settings)){$tried[]=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'not_configured'];continue;}
        $res=$id==='aihorde'?aih_aihorde_submit($prompt,$settings):aih_image_call($id,$prompt,$settings);
        if(!empty($res['ok'])&&$id==='aihorde'&&!empty($res['pending'])&&$requested==='auto'){
            $limit=aih_aihorde_queue_limit($settings);if($limit>0){usleep(300000);$peek=aih_aihorde_status((string)$res['request_id'],$settings);if(!empty($peek['ok'])&&empty($peek['pending'])){$res=$peek;}elseif(!empty($peek['ok'])){$wait=max(0,(int)($peek['wait_time']??0));if($wait>$limit){aih_aihorde_cancel((string)$res['request_id']);$tried[]=['id'=>'aihorde','name'=>$catalog[$id]['name'],'status'=>'queue_skipped','wait_time'=>$wait,'queue_position'=>max(0,(int)($peek['queue_position']??0)),'error'=>'Queue estimate '.$wait.'s exceeded Auto limit '.$limit.'s'];continue;}$res=array_merge($res,$peek);}}
        }
        if(!empty($res['ok'])){aih_mark_provider_state($stateId,['last_ok'=>time(),'last_error'=>'','cooldown_until'=>0]);$res['provider']=$catalog[$id]['name'];$res['provider_id']=$id;$res['tried']=$tried;if(!array_key_exists('pending',$res))$res['pending']=false;return $res;}
        $code=(int)($res['code']??0);$entry=['id'=>$id,'name'=>$catalog[$id]['name'],'status'=>'failed','code'=>$code,'error'=>substr((string)($res['error']??''),0,220)];$until=0;
        if($code===429){$until=time()+aih_retry_seconds($res['headers']??[],900);$entry['status']='rate_limited';}
        elseif(in_array($code,[401,402,403],true)){$until=time()+3600;$entry['status']='auth_or_quota';}
        elseif($code===0){$until=time()+300;$entry['status']='unavailable';}
        elseif($code>=500){$until=time()+120;$entry['status']='temporary_error';}
        if($until>0)$entry['until']=$until;aih_mark_provider_state($stateId,['last_error'=>'HTTP '.$code.' '.substr((string)($res['error']??''),0,160),'cooldown_until'=>$until]);$tried[]=$entry;
    }
    return ['ok'=>false,'error'=>'Free image generation is temporarily unavailable. AI Horde may be busy; retry later or explicitly choose Free web images to search existing media. No paid API was called.','tried'=>$tried,'provider_catalog'=>$catalog];
}

function aih_image_generate(string $prompt,string $requested='auto',?array $settings=null): array {
    return aih_image_start($prompt,$requested,$settings);
}
