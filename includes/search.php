<?php
declare(strict_types=1);

function aih_sector_catalog(): array {
    return [
        'ALL'=>['key'=>'sector_all','keywords'=>''],
        'vehicles'=>['key'=>'sector_vehicles','keywords'=>'vehicles cars motorcycles auto'],
        'real_estate'=>['key'=>'sector_real_estate','keywords'=>'real estate apartment house land property rent sale'],
        'jobs'=>['key'=>'sector_jobs','keywords'=>'jobs vacancies employment work career'],
        'services'=>['key'=>'sector_services','keywords'=>'services local service provider repair maintenance'],
        'digital_agency'=>['key'=>'sector_digital_agency','keywords'=>'web digital agency WordPress ecommerce SEO marketing development'],
        'electronics'=>['key'=>'sector_electronics','keywords'=>'electronics audio video appliances'],
        'computers'=>['key'=>'sector_computers','keywords'=>'computers laptops PC components software'],
        'phones'=>['key'=>'sector_phones','keywords'=>'phones smartphones tablets accessories'],
        'home_garden'=>['key'=>'sector_home_garden','keywords'=>'home furniture garden household'],
        'construction'=>['key'=>'sector_construction','keywords'=>'construction tools materials equipment'],
        'agriculture'=>['key'=>'sector_agriculture','keywords'=>'agriculture farming tractors machinery'],
        'transport'=>['key'=>'sector_transport','keywords'=>'transport logistics delivery commercial vehicles'],
        'animals'=>['key'=>'sector_animals','keywords'=>'animals pets livestock'],
        'fashion'=>['key'=>'sector_fashion','keywords'=>'clothing shoes fashion accessories'],
        'business'=>['key'=>'sector_business','keywords'=>'business equipment wholesale industrial company for sale'],
        'leisure'=>['key'=>'sector_leisure','keywords'=>'sports leisure hobbies travel tickets'],
        'education'=>['key'=>'sector_education','keywords'=>'education courses training tutors'],
        'health_beauty'=>['key'=>'sector_health_beauty','keywords'=>'health beauty wellness salon'],
        'other'=>['key'=>'sector_other','keywords'=>'classifieds marketplace offers'],
    ];
}

function aih_country_catalog(): array {
    return [
        'LV'=>['name'=>'Latvia','general'=>['ss.lv','ss.com'],'sectors'=>[
            'vehicles'=>['ss.lv','ss.com'],'real_estate'=>['ss.lv','city24.lv'],'jobs'=>['cv.lv','visidarbi.lv','ss.lv'],'services'=>['ss.lv'],'digital_agency'=>['sortlist.com','clutch.co','ss.lv']]],
        'LT'=>['name'=>'Lithuania','general'=>['skelbiu.lt','alio.lt'],'sectors'=>[
            'vehicles'=>['autoplius.lt','autogidas.lt'],'real_estate'=>['aruodas.lt','skelbiu.lt'],'jobs'=>['cvonline.lt','cvbankas.lt'],'services'=>['paslaugos.lt','skelbiu.lt']]],
        'EE'=>['name'=>'Estonia','general'=>['soov.ee','okidoki.ee'],'sectors'=>[
            'vehicles'=>['auto24.ee'],'real_estate'=>['kv.ee','city24.ee'],'jobs'=>['cvkeskus.ee','cv.ee'],'services'=>['hange.ee','soov.ee']]],
        'UK'=>['name'=>'United Kingdom','general'=>['gumtree.com','ebay.co.uk'],'sectors'=>[
            'vehicles'=>['autotrader.co.uk','motors.co.uk','pistonheads.com'],'real_estate'=>['rightmove.co.uk','zoopla.co.uk'],'jobs'=>['indeed.com','reed.co.uk'],'services'=>['bark.com','yell.com'],'digital_agency'=>['clutch.co','sortlist.com']]],
        'DE'=>['name'=>'Germany','general'=>['kleinanzeigen.de'],'sectors'=>[
            'vehicles'=>['mobile.de','autoscout24.de'],'real_estate'=>['immobilienscout24.de','immowelt.de'],'jobs'=>['stepstone.de','indeed.com'],'services'=>['my-hammer.de','kleinanzeigen.de']]],
        'SE'=>['name'=>'Sweden','general'=>['blocket.se'],'sectors'=>['vehicles'=>['blocket.se','wayke.se'],'real_estate'=>['hemnet.se'],'jobs'=>['arbetsformedlingen.se','indeed.com']]],
        'NO'=>['name'=>'Norway','general'=>['finn.no'],'sectors'=>['vehicles'=>['finn.no'],'real_estate'=>['finn.no'],'jobs'=>['finn.no']]],
        'DK'=>['name'=>'Denmark','general'=>['dba.dk'],'sectors'=>['vehicles'=>['bilbasen.dk','dba.dk'],'real_estate'=>['boligsiden.dk'],'jobs'=>['jobindex.dk']]],
        'FI'=>['name'=>'Finland','general'=>['tori.fi'],'sectors'=>['vehicles'=>['nettiauto.com','tori.fi'],'real_estate'=>['etuovi.com'],'jobs'=>['duunitori.fi']]],
        'IS'=>['name'=>'Iceland','general'=>['bland.is'],'sectors'=>['vehicles'=>['bland.is'],'real_estate'=>['mbl.is'],'jobs'=>['alfred.is']]],
        'PL'=>['name'=>'Poland','general'=>['olx.pl'],'sectors'=>['vehicles'=>['otomoto.pl','olx.pl'],'real_estate'=>['otodom.pl'],'jobs'=>['pracuj.pl','olx.pl']]],
        'FR'=>['name'=>'France','general'=>['leboncoin.fr'],'sectors'=>['vehicles'=>['lacentrale.fr','leboncoin.fr'],'real_estate'=>['seloger.com','leboncoin.fr'],'jobs'=>['fr.indeed.com']]],
        'ES'=>['name'=>'Spain','general'=>['milanuncios.com'],'sectors'=>['vehicles'=>['coches.net','milanuncios.com'],'real_estate'=>['idealista.com','fotocasa.es'],'jobs'=>['infojobs.net','indeed.com']]],
        'UA'=>['name'=>'Ukraine','general'=>['olx.ua'],'sectors'=>['vehicles'=>['auto.ria.com','olx.ua'],'real_estate'=>['dom.ria.com','olx.ua'],'jobs'=>['work.ua','robota.ua']]],
        'RU'=>['name'=>'Russia','general'=>['avito.ru'],'sectors'=>['vehicles'=>['auto.ru','avito.ru'],'real_estate'=>['cian.ru','avito.ru'],'jobs'=>['hh.ru','avito.ru']]],
    ];
}

function aih_selected_countries(array $in): array {
    $all=array_keys(aih_country_catalog());
    $selected=$in['countries']??['ALL'];
    if(!is_array($selected))$selected=[$selected];
    $selected=array_values(array_filter(array_map('strval',$selected),fn($x)=>$x==='ALL'||in_array($x,$all,true)));
    if(!$selected||in_array('ALL',$selected,true))return $all;
    return $selected;
}

function aih_selected_sectors(array $in): array {
    $all=array_keys(aih_sector_catalog());
    $selected=$in['sectors']??['ALL'];
    if(!is_array($selected))$selected=[$selected];
    $selected=array_values(array_filter(array_map('strval',$selected),fn($x)=>in_array($x,$all,true)));
    if(!$selected||in_array('ALL',$selected,true))return ['ALL'];
    return $selected;
}

function aih_sector_domains(string $country,string $sector): array {
    $catalog=aih_country_catalog();$meta=$catalog[$country]??[];
    if($sector!=='ALL'&&!empty($meta['sectors'][$sector]))return array_values(array_unique((array)$meta['sectors'][$sector]));
    return array_values(array_unique((array)($meta['general']??[])));
}

function aih_build_search_queries(array $in): array {
    $q=trim((string)($in['q']??''));
    $brand=trim((string)($in['brand']??$in['make']??''));$model=trim((string)($in['model']??''));
    $min=trim((string)($in['price_min']??''));$max=trim((string)($in['price_max']??''));$year=trim((string)($in['year_min']??''));
    $location=trim((string)($in['location']??''));$condition=trim((string)($in['condition']??''));$keywords=trim((string)($in['keywords']??''));
    $structured=trim(implode(' ',array_filter([$brand,$model,$keywords,$location,$condition,$year,$min!==''?'price from '.$min:'',$max!==''?'price to '.$max:''])));
    $base=trim($q!==''?$q:$structured);if($base==='')return [];
    if($q!==''&&$structured!=='')$base=trim($q.' '.$structured);
    $countries=aih_selected_countries($in);$sectors=aih_selected_sectors($in);$catalog=aih_country_catalog();$sectorCatalog=aih_sector_catalog();$queries=[];
    foreach($countries as$cc){
        $country=$catalog[$cc]['name']??$cc;
        foreach($sectors as$sector){
            $sectorWords=$sector==='ALL'?'':($sectorCatalog[$sector]['keywords']??'');
            $broad=trim($base.' '.$sectorWords.' '.$country);
            $queries[]=['q'=>$broad,'country'=>$cc,'sector'=>$sector,'domain'=>''];
            $domains=aih_sector_domains($cc,$sector);
            if($domains){
                $parts=array_map(fn($d)=>'site:'.$d,array_slice($domains,0,3));
                $queries[]=['q'=>trim($base.' '.$sectorWords.' '.$country.' ('.implode(' OR ',$parts).')'),'country'=>$cc,'sector'=>$sector,'domain'=>implode(',',$domains)];
            }
            if(count($queries)>=30)break 2;
        }
    }
    return $queries;
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


function aih_search_gemini_grounding(string $q,string $key,string $model,int $limit): array {
    if($key===''||$model==='')return [];
    $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent?key='.urlencode($key);
    $payload=[
        'contents'=>[['role'=>'user','parts'=>[['text'=>'Search the live web for: '.$q.' . Prefer primary/reputable sources and current public listings.']]]],
        'tools'=>[['google_search'=>new stdClass()]],
        'generationConfig'=>['temperature'=>0.1,'maxOutputTokens'=>1200],
    ];
    $r=aih_http_json('POST',$url,$payload,[],45);if($r['code']<200||$r['code']>=300)return [];
    $j=json_decode($r['body'],true);$cand=$j['candidates'][0]??[];$text='';foreach(($cand['content']['parts']??[])as$part)$text.=(string)($part['text']??'');
    $chunks=$cand['groundingMetadata']['groundingChunks']??[];$out=[];
    foreach($chunks as$chunk){$web=$chunk['web']??null;if(!is_array($web))continue;$uri=(string)($web['uri']??'');if(!preg_match('#^https?://#i',$uri))continue;$out[]=['title'=>(string)($web['title']??parse_url($uri,PHP_URL_HOST)??'Google grounded result'),'url'=>$uri,'snippet'=>(function_exists('mb_substr')?mb_substr(trim($text),0,500):substr(trim($text),0,500)),'engine'=>'gemini-grounding'];if(count($out)>=$limit)break;}
    return $out;
}

function aih_search_groq_browser_note(string $q,string $key,string $model): string {
    if($key===''||!in_array($model,['openai/gpt-oss-20b','openai/gpt-oss-120b','openai/gpt-oss-safeguard-20b'],true))return '';
    $payload=['model'=>$model,'messages'=>[['role'=>'user','content'=>'Search the live web for this query and give a short factual comparison with citations: '.$q]],'tools'=>[['type'=>'browser_search']],'tool_choice'=>'required','citation_options'=>'enabled','temperature'=>0.2,'max_completion_tokens'=>1200];
    $r=aih_http_json('POST','https://api.groq.com/openai/v1/chat/completions',$payload,['Authorization: Bearer '.$key],45);if($r['code']<200||$r['code']>=300)return '';
    $j=json_decode($r['body'],true);return trim((string)($j['choices'][0]['message']['content']??''));
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
    $resp=aih_multi_get($req,30);$all=[];foreach($resp as $i=>$r){if(($r['code']??0)>=200&&($r['code']??0)<300){foreach(aih_parse_ddg_html((string)$r['body'],$perQuery)as$row){$row['query']=$queries[$i]['q'];$row['country']=$queries[$i]['country']??'';$row['domain']=$queries[$i]['domain']??'';$row['sector']=$queries[$i]['sector']??'ALL';$all[]=$row;}}}return $all;
}

function aih_extract_market_fields(array $row): array {
    $text=html_entity_decode(trim(($row['title']??'').' '.($row['snippet']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8');
    $price='';$currency='';
    if(preg_match('/(?:€|EUR\s?)([0-9][0-9.,\s]{2,})/iu',$text,$m)||preg_match('/([0-9][0-9.,\s]{2,})\s?(?:€|EUR)/iu',$text,$m)){$price=trim($m[1]);$currency='EUR';}
    elseif(preg_match('/(?:£|GBP\s?)([0-9][0-9.,\s]{2,})/iu',$text,$m)||preg_match('/([0-9][0-9.,\s]{2,})\s?(?:£|GBP)/iu',$text,$m)){$price=trim($m[1]);$currency='GBP';}
    elseif(preg_match('/(?:PLN\s?)([0-9][0-9.,\s]{2,})/iu',$text,$m)||preg_match('/([0-9][0-9.,\s]{2,})\s?PLN/iu',$text,$m)){$price=trim($m[1]);$currency='PLN';}
    $year='';if(preg_match('/\b(19[89]\d|20[0-3]\d)\b/',$text,$m))$year=$m[1];
    $mileage='';if(preg_match('/\b([0-9][0-9.,\s]{2,})\s?(km|miles|mi)\b/iu',$text,$m))$mileage=trim($m[1]).' '.$m[2];
    $area='';if(preg_match('/\b([0-9]+(?:[.,][0-9]+)?)\s?(?:m²|m2|sqm|sq\.?\s?m)\b/iu',$text,$m))$area=trim($m[1]).' m²';
    $salary='';if(preg_match('/(?:salary|wage|alga|atlyginimas|salary from|pay)\D{0,18}([€£]?[0-9][0-9.,\s]{2,}(?:\s?(?:EUR|GBP|PLN|€|£))?)/iu',$text,$m))$salary=trim($m[1]);
    return array_merge($row,['price'=>$price,'currency'=>$currency,'year'=>$year,'mileage'=>$mileage,'area'=>$area,'salary'=>$salary]);
}

function aih_dedupe_results(array $rows,int $limit): array {
    $seen=[];$out=[];foreach($rows as$row){$url=trim((string)($row['url']??''));if($url==='')continue;$key=strtolower(preg_replace('/[#?].*$/','',$url));if(isset($seen[$key]))continue;$seen[$key]=1;$out[]=aih_extract_market_fields($row);if(count($out)>=$limit)break;}return $out;
}

function aih_ai_enrich_rows(array $rows): array {
    if(!$rows)return $rows;$payload=[];foreach(array_slice($rows,0,40)as$i=>$r)$payload[]=['i'=>$i,'title'=>$r['title'],'snippet'=>$r['snippet'],'url'=>$r['url'],'country'=>$r['country']??'','sector'=>$r['sector']??'ALL'];
    $messages=[['role'=>'system','content'=>'You structure web search results without inventing facts. Return ONLY JSON array. Each item: i, category, brand, model, price, currency, year, mileage, location, company, salary, area, condition. Use null when not explicitly present in title/snippet. Never infer missing values.'],['role'=>'user','content'=>json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)]];
    $res=aih_ai_chat($messages);if(empty($res['ok']))return $rows;$txt=trim((string)$res['text']);$txt=preg_replace('/^```(?:json)?\s*|\s*```$/i','',$txt);$j=json_decode($txt,true);if(!is_array($j))return $rows;
    foreach($j as$x){$i=(int)($x['i']??-1);if(!isset($rows[$i]))continue;foreach(['category','brand','model','location','company','salary','area','condition']as$k)if(isset($x[$k])&&is_scalar($x[$k]))$rows[$i][$k]=(string)$x[$k];foreach(['price','currency','year','mileage']as$k)if(empty($rows[$i][$k])&&isset($x[$k])&&is_scalar($x[$k]))$rows[$i][$k]=(string)$x[$k];}
    foreach($rows as &$row){if(empty($row['make'])&&!empty($row['brand']))$row['make']=$row['brand'];}unset($row);
    return $rows;
}

function aih_search_ai_summary(array $rows,string $query,string $lang): array {
    if(!$rows)return ['text'=>'','provider'=>''];
    $payload=[];foreach(array_slice($rows,0,20)as$r)$payload[]=['title'=>$r['title'],'snippet'=>$r['snippet'],'url'=>$r['url'],'country'=>$r['country']??'','price'=>$r['price']??'','year'=>$r['year']??'','sector'=>$r['sector']??'ALL','location'=>$r['location']??'','company'=>$r['company']??'','area'=>$r['area']??''];
    $prompt='Search query: '.$query."\nResults:\n".json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $res=aih_ai_chat([['role'=>'system','content'=>'Summarize and compare only the supplied sourced results. Do not invent facts. Mention uncertainty and important differences. Reply in language code '.$lang.'. Keep it concise.'],['role'=>'user','content'=>$prompt]]);
    return !empty($res['ok'])?['text'=>(string)$res['text'],'provider'=>(string)($res['provider_name']??$res['provider']??'')]:['text'=>'','provider'=>''];
}

function aih_run_search(array $input): array {
    $settings=aih_settings();$queries=aih_build_search_queries($input);if(!$queries)return ['ok'=>false,'error'=>'Enter a search query or structured criteria.'];
    $cacheKey=sha1(json_encode(['input'=>$input,'queries'=>$queries,'sx'=>$settings['search']['searxng_url']??'','ow'=>(bool)($settings['search']['ollama_web_key']??'')],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));$cachePath=AIH_CACHE.'/search_'.$cacheKey.'.json';
    if(is_file($cachePath) && time()-(int)(filemtime($cachePath)?:0)<600){$cached=aih_read_json($cachePath,[]);if(is_array($cached)&&!empty($cached['ok'])){$cached['cached']=true;return $cached;}}
    $max=max(10,min(100,(int)($settings['search']['max_results']??40)));$rows=[];$ollamaKey=trim((string)($settings['search']['ollama_web_key']??''));$sx=trim((string)($settings['search']['searxng_url']??''));$engines=[];$researchNotes=[];
    if($ollamaKey!==''){$engines[]='ollama-web';foreach($queries as$x){foreach(aih_search_ollama_web($x['q'],$ollamaKey,5)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$r['sector']=$x['sector']??'ALL';$rows[]=$r;}}}
    if($sx!==''){$engines[]='searxng';foreach($queries as$x){foreach(aih_search_searxng($x['q'],$sx,5)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$r['sector']=$x['sector']??'ALL';$rows[]=$r;}}}
    $p=$settings['providers']??[];$enabled=$p['enabled']??[];$keys=$p['keys']??[];$models=$p['models']??[];
    if(!empty($enabled['gemini'])&&trim((string)($keys['gemini']??''))!==''){$gRows=aih_search_gemini_grounding((string)($queries[0]['q']??($input['q']??'')),trim((string)$keys['gemini']),(string)($models['gemini']??'gemini-3.8-flash'),8);if($gRows){$engines[]='gemini-grounding';foreach($gRows as$r){$r['query']=(string)($input['q']??'');$r['country']='';$r['domain']='';$r['sector']=(string)(aih_selected_sectors($input)[0]??'ALL');$rows[]=$r;}}}
    if(!empty($enabled['groq'])&&trim((string)($keys['groq']??''))!==''){$note=aih_search_groq_browser_note((string)($queries[0]['q']??($input['q']??'')),trim((string)$keys['groq']),(string)($models['groq']??'openai/gpt-oss-20b'));if($note!==''){$engines[]='groq-browser-search';$researchNotes[]=['provider'=>'Groq Browser Search','text'=>$note];}}
    $ddg=aih_search_ddg_batch($queries,5);if($ddg){$engines[]='duckduckgo';$rows=array_merge($rows,$ddg);}
    $rows=aih_dedupe_results($rows,$max);if(!empty($settings['search']['ai_enrich']))$rows=aih_ai_enrich_rows($rows);
    $lang=(string)($input['lang']??'en');$sectorNames=[];foreach(aih_selected_sectors($input) as $sec)$sectorNames[]=$sec==='ALL'?'all sectors':($sec);$summaryQuery=trim((string)($input['q']??'').' | sectors: '.implode(', ',$sectorNames));$summary=aih_search_ai_summary($rows,$summaryQuery,$lang);
    $id=aih_new_id('report');$report=['id'=>$id,'created_at'=>date(DATE_ATOM),'input'=>$input,'queries'=>$queries,'results'=>$rows,'engines'=>$engines,'research_notes'=>$researchNotes,'ai_summary'=>$summary];aih_write_json(AIH_REPORTS.'/'.$id.'.json',$report);
    $warning=count($rows)===0?'No indexed results were returned. Public DuckDuckGo HTML can be blocked by some hosts; add SearXNG or Ollama Web Search for stronger coverage.':'';
    $response=['ok'=>true,'report_id'=>$id,'results'=>$rows,'queries'=>$queries,'count'=>count($rows),'engines'=>array_values(array_unique($engines)),'research_notes'=>$researchNotes,'ai_summary'=>$summary,'cached'=>false,'warning'=>$warning];aih_write_json($cachePath,$response);return $response;
}
