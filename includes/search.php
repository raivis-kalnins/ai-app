<?php
declare(strict_types=1);

function aih_market_domains(): array {
    return [
        'LV'=>['ss.com','auto24.lv'],
        'DE'=>['mobile.de','autoscout24.de','kleinanzeigen.de'],
        'UK'=>['autotrader.co.uk','motors.co.uk','pistonheads.com'],
        'LT'=>['autoplius.lt','autogidas.lt'],
        'EE'=>['auto24.ee','soov.ee'],
    ];
}

function aih_build_search_queries(array $in): array {
    $mode=(string)($in['mode']??'general');$q=trim((string)($in['q']??''));$queries=[];
    if($mode==='cars'){
        $make=trim((string)($in['make']??''));$model=trim((string)($in['model']??''));$min=(string)($in['price_min']??'');$max=(string)($in['price_max']??'');$year=(string)($in['year_min']??'');
        $terms=trim($make.' '.$model.' '.$year.' '.$min.' '.$max);
        $countries=$in['countries']??['LV','DE','UK','LT','EE']; if(!is_array($countries))$countries=['LV','DE','UK','LT','EE'];
        foreach(aih_market_domains() as $cc=>$domains){if(!in_array($cc,$countries,true))continue;foreach($domains as $domain)$queries[]=['q'=>trim($terms.' site:'.$domain),'country'=>$cc,'domain'=>$domain];}
    } elseif($mode==='agency') {
        $service=trim((string)($in['service']??$q));$location=trim((string)($in['location']??''));
        foreach(array_filter([$service.' digital agency '.$location,$service.' web development agency '.$location,$service.' WordPress agency '.$location]) as $qq)$queries[]=['q'=>$qq,'country'=>'','domain'=>''];
    } else { if($q!=='')$queries[]=['q'=>$q,'country'=>'','domain'=>'']; }
    return array_slice($queries,0,15);
}

function aih_search_ollama_web(string $q,string $key,int $limit): array {
    if($key==='')return [];
    $r=aih_http_json('POST','https://ollama.com/api/web_search',['query'=>$q],['Authorization: Bearer '.$key],30);
    if($r['code']<200||$r['code']>=300)return [];$j=json_decode($r['body'],true);$out=[];
    foreach(($j['results']??[])as$x){$out[]=['title'=>(string)($x['title']??''),'url'=>(string)($x['url']??''),'snippet'=>(string)($x['content']??''),'engine'=>'ollama-web'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_searxng(string $q,string $base,int $limit): array {
    $base=rtrim(trim($base),'/');if($base==='')return [];
    $url=$base.'/search?format=json&safesearch=1&q='.rawurlencode($q);$r=aih_http('GET',$url,null,['Accept: application/json'],30);
    if($r['code']<200||$r['code']>=300)return [];$j=json_decode($r['body'],true);$out=[];
    foreach(($j['results']??[])as$x){$out[]=['title'=>(string)($x['title']??''),'url'=>(string)($x['url']??''),'snippet'=>strip_tags((string)($x['content']??'')),'engine'=>'searxng'];if(count($out)>=$limit)break;}return $out;
}

function aih_parse_ddg_html(string $html,int $limit): array {
    $out=[]; if(trim($html)==='')return $out;
    if(!class_exists('DOMDocument')){
        if(preg_match_all('#<a[^>]+class="[^"]*result__a[^"]*"[^>]+href="([^"]+)"[^>]*>(.*?)</a>#is',$html,$m,PREG_SET_ORDER)){foreach($m as$x){$href=html_entity_decode(strip_tags($x[1]),ENT_QUOTES|ENT_HTML5,'UTF-8');$title=trim(strip_tags($x[2]));if(str_contains($href,'uddg=')){parse_str((string)parse_url($href,PHP_URL_QUERY),$qs);if(!empty($qs['uddg']))$href=(string)$qs['uddg'];}if(preg_match('#^https?://#i',$href))$out[]=['title'=>$title,'url'=>$href,'snippet'=>'','engine'=>'duckduckgo'];if(count($out)>=$limit)break;}}return $out;
    }
    libxml_use_internal_errors(true);$dom=new DOMDocument();@$dom->loadHTML($html);$xp=new DOMXPath($dom);
    $nodes=$xp->query("//*[contains(concat(' ',normalize-space(@class),' '),' result ')]");
    if($nodes){foreach($nodes as $node){$a=$xp->query(".//a[contains(@class,'result__a')]",$node)->item(0);if(!$a)continue;$href=(string)$a->getAttribute('href');$title=trim((string)$a->textContent);$sn=$xp->query(".//*[contains(@class,'result__snippet')]",$node)->item(0);$snippet=$sn?trim((string)$sn->textContent):'';
            if(str_contains($href,'uddg=')){parse_str((string)parse_url($href,PHP_URL_QUERY),$qs);if(!empty($qs['uddg']))$href=(string)$qs['uddg'];}
            if(preg_match('#^https?://#i',$href))$out[]=['title'=>$title,'url'=>$href,'snippet'=>$snippet,'engine'=>'duckduckgo'];if(count($out)>=$limit)break;}}
    libxml_clear_errors();return $out;
}

function aih_search_ddg_batch(array $queries,int $perQuery): array {
    $req=[];foreach($queries as $x)$req[]=['url'=>'https://html.duckduckgo.com/html/?q='.rawurlencode((string)$x['q']),'headers'=>['Accept: text/html']];
    $resp=aih_multi_get($req,30);$all=[];foreach($resp as $i=>$r){if(($r['code']??0)>=200&&($r['code']??0)<300){foreach(aih_parse_ddg_html((string)$r['body'],$perQuery)as$row){$row['query']=$queries[$i]['q'];$row['country']=$queries[$i]['country']??'';$row['domain']=$queries[$i]['domain']??'';$all[]=$row;}}}return $all;
}

function aih_extract_market_fields(array $row): array {
    $text=html_entity_decode(trim(($row['title']??'').' '.($row['snippet']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8');
    $price='';$currency='';
    if(preg_match('/(?:€|EUR\s?)([0-9][0-9.,\s]{2,})/iu',$text,$m)||preg_match('/([0-9][0-9.,\s]{2,})\s?(?:€|EUR)/iu',$text,$m)){$price=trim($m[1]);$currency='EUR';}
    elseif(preg_match('/(?:£|GBP\s?)([0-9][0-9.,\s]{2,})/iu',$text,$m)||preg_match('/([0-9][0-9.,\s]{2,})\s?(?:£|GBP)/iu',$text,$m)){$price=trim($m[1]);$currency='GBP';}
    $year='';if(preg_match('/\b(19[89]\d|20[0-2]\d)\b/',$text,$m))$year=$m[1];
    $mileage='';if(preg_match('/\b([0-9][0-9.,\s]{2,})\s?(km|miles|mi)\b/iu',$text,$m))$mileage=trim($m[1]).' '.$m[2];
    return array_merge($row,['price'=>$price,'currency'=>$currency,'year'=>$year,'mileage'=>$mileage]);
}

function aih_dedupe_results(array $rows,int $limit): array {
    $seen=[];$out=[];foreach($rows as$row){$url=trim((string)($row['url']??''));if($url==='')continue;$key=strtolower(preg_replace('/[#?].*$/','',$url));if(isset($seen[$key]))continue;$seen[$key]=1;$out[]=aih_extract_market_fields($row);if(count($out)>=$limit)break;}return $out;
}

function aih_ai_enrich_rows(array $rows,array $input): array {
    if(!$rows)return $rows;$payload=[];foreach(array_slice($rows,0,40)as$i=>$r)$payload[]=['i'=>$i,'title'=>$r['title'],'snippet'=>$r['snippet'],'url'=>$r['url'],'country'=>$r['country']??''];
    $messages=[['role'=>'system','content'=>'You structure web search results without inventing facts. Return ONLY JSON array. Each item: i, make, model, price, currency, year, mileage, location. Use null when not explicitly present in title/snippet. Never infer missing values.'],['role'=>'user','content'=>json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]];
    $res=aih_ai_chat($messages);if(empty($res['ok']))return $rows;$txt=trim((string)$res['text']);$txt=preg_replace('/^```(?:json)?\s*|\s*```$/i','',$txt);$j=json_decode($txt,true);if(!is_array($j))return $rows;
    foreach($j as$x){$i=(int)($x['i']??-1);if(!isset($rows[$i]))continue;foreach(['make','model','location']as$k)if(isset($x[$k])&&is_scalar($x[$k]))$rows[$i][$k]=(string)$x[$k];foreach(['price','currency','year','mileage']as$k)if(empty($rows[$i][$k])&&isset($x[$k])&&is_scalar($x[$k]))$rows[$i][$k]=(string)$x[$k];}
    return $rows;
}

function aih_run_search(array $input): array {
    $settings=aih_settings();$queries=aih_build_search_queries($input);if(!$queries)return ['ok'=>false,'error'=>'Enter a search query or car criteria.'];
    $cacheKey=sha1(json_encode(['input'=>$input,'queries'=>$queries,'sx'=>$settings['search']['searxng_url']??'','ow'=>(bool)($settings['search']['ollama_web_key']??'')],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));$cachePath=AIH_CACHE.'/search_'.$cacheKey.'.json';
    if(is_file($cachePath) && time()-(int)(filemtime($cachePath)?:0)<600){$cached=aih_read_json($cachePath,[]);if(is_array($cached)&&!empty($cached['ok'])){$cached['cached']=true;return $cached;}}
    $max=max(10,min(100,(int)($settings['search']['max_results']??40)));$rows=[];$ollamaKey=trim((string)($settings['search']['ollama_web_key']??''));$sx=trim((string)($settings['search']['searxng_url']??''));
    if($ollamaKey!==''){foreach($queries as$x){foreach(aih_search_ollama_web($x['q'],$ollamaKey,8)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$rows[]=$r;}}}
    if(!$rows&&$sx!==''){foreach($queries as$x){foreach(aih_search_searxng($x['q'],$sx,8)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$rows[]=$r;}}}
    if(!$rows)$rows=aih_search_ddg_batch($queries,8);
    $rows=aih_dedupe_results($rows,$max);if(!empty($settings['search']['ai_enrich'])&&($input['mode']??'')==='cars')$rows=aih_ai_enrich_rows($rows,$input);
    $id=aih_new_id('report');$report=['id'=>$id,'created_at'=>date(DATE_ATOM),'input'=>$input,'queries'=>$queries,'results'=>$rows];aih_write_json(AIH_REPORTS.'/'.$id.'.json',$report);
    $response=['ok'=>true,'report_id'=>$id,'results'=>$rows,'queries'=>$queries,'count'=>count($rows),'cached'=>false,'warning'=>count($rows)===0?'No indexed results were returned. The no-key DuckDuckGo fallback can be blocked by some hosts; configure SearXNG or an Ollama Web Search free-tier key for reliable production search.':''];aih_write_json($cachePath,$response);return $response;
}
