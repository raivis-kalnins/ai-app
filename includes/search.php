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

function aih_sector_filter_schema(): array {
    $f=fn(string $name,string $label,string $type='text',string $placeholder='',array $options=[])=>['name'=>$name,'label'=>$label,'type'=>$type,'placeholder'=>$placeholder,'options'=>$options];
    return [
        'ALL'=>[$f('keywords','Keywords'),$f('location','Location / city'),$f('condition','Condition / type'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'vehicles'=>[$f('brand','Make / brand'),$f('model','Model'),$f('body_type','Body type','select','',['SUV','Estate','Hatchback','Saloon','Van','Motorcycle','Other']),$f('fuel','Fuel','select','',['Petrol','Diesel','Hybrid','Plug-in hybrid','Electric','LPG','Other']),$f('transmission','Transmission','select','',['Automatic','Manual','Other']),$f('drive','Drive','select','',['FWD','RWD','AWD / 4x4']),$f('seats','Seats','number'),$f('year_min','Min year','number'),$f('year_max','Max year','number'),$f('mileage_max','Max mileage','number'),$f('location','Location / city'),$f('condition','Condition','select','',['New','Used','Damaged']),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'real_estate'=>[$f('property_type','Property type','select','',['Apartment','House','Land','Commercial','Room','Garage','Other']),$f('transaction','Transaction','select','',['For sale','For rent']),$f('rooms_min','Min rooms','number'),$f('rooms_max','Max rooms','number'),$f('area_min','Min area m2','number'),$f('area_max','Max area m2','number'),$f('land_area_min','Min land area','number'),$f('location','Location / city'),$f('furnished','Furnished','select','',['Any','Yes','No']),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'jobs'=>[$f('job_title','Job title'),$f('company','Company'),$f('employment_type','Employment type','select','',['Full time','Part time','Contract','Temporary','Internship']),$f('remote_mode','Work mode','select','',['On-site','Hybrid','Remote']),$f('experience','Experience level','select','',['Entry','Mid','Senior','Lead / Manager']),$f('location','Location'),$f('salary_min','Min salary','number'),$f('salary_max','Max salary','number'),$f('keywords','Skills / keywords')],
        'services'=>[$f('service_type','Service type'),$f('provider','Provider / company'),$f('location','Location / service area'),$f('availability','Availability'),$f('rating_min','Min rating','number'),$f('price_min','Min price','number'),$f('price_max','Max price','number'),$f('keywords','Requirements / keywords')],
        'digital_agency'=>[$f('service_type','Service','select','',['Web design','WordPress','WooCommerce','E-commerce','SEO','PPC','Branding','App development','AI automation']),$f('technology','Technology / platform'),$f('company','Agency / company'),$f('location','Location'),$f('remote_mode','Delivery','select','',['Local','Remote','Hybrid']),$f('budget_min','Min budget','number'),$f('budget_max','Max budget','number'),$f('keywords','Industry / requirements')],
        'electronics'=>[$f('category','Product category'),$f('brand','Brand'),$f('model','Model'),$f('condition','Condition','select','',['New','Used','Refurbished']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number'),$f('keywords','Specifications')],
        'computers'=>[$f('category','Computer type','select','',['Laptop','Desktop','Mini PC','Monitor','GPU','CPU','Components','Software']),$f('brand','Brand'),$f('model','Model'),$f('cpu','CPU'),$f('ram','RAM'),$f('storage','Storage'),$f('gpu','GPU'),$f('condition','Condition','select','',['New','Used','Refurbished']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'phones'=>[$f('brand','Brand'),$f('model','Model'),$f('storage','Storage'),$f('colour','Colour'),$f('network','Network / SIM'),$f('condition','Condition','select','',['New','Used','Refurbished']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'home_garden'=>[$f('category','Item type'),$f('brand','Brand'),$f('material','Material'),$f('dimensions','Dimensions'),$f('condition','Condition','select','',['New','Used']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'construction'=>[$f('category','Material / equipment type'),$f('brand','Brand'),$f('model','Model'),$f('quantity','Quantity'),$f('condition','Condition','select','',['New','Used','For hire']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'agriculture'=>[$f('category','Machinery / item type'),$f('brand','Brand'),$f('model','Model'),$f('year_min','Min year','number'),$f('hours_max','Max operating hours','number'),$f('condition','Condition','select','',['New','Used']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'transport'=>[$f('service_type','Transport type','select','',['Delivery','Freight','Moving','Taxi / passenger','Vehicle transport','Courier','Storage']),$f('from_location','From'),$f('to_location','To'),$f('vehicle_type','Vehicle type'),$f('weight_max','Max weight / load'),$f('date','Date'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'animals'=>[$f('animal_type','Animal type'),$f('breed','Breed'),$f('age','Age'),$f('gender','Gender','select','',['Male','Female','Any']),$f('pedigree','Pedigree','select','',['Any','Yes','No']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'fashion'=>[$f('category','Category'),$f('brand','Brand'),$f('size','Size'),$f('gender','For','select','',['Women','Men','Kids','Unisex']),$f('colour','Colour'),$f('condition','Condition','select','',['New','Used']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'business'=>[$f('business_type','Business / asset type'),$f('industry','Industry'),$f('revenue_min','Min revenue','number'),$f('profit_min','Min profit','number'),$f('employees_max','Max employees','number'),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'leisure'=>[$f('category','Category / activity'),$f('brand','Brand / organiser'),$f('date','Date'),$f('location','Location'),$f('condition','Condition / ticket type'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'education'=>[$f('course_type','Course / lesson type'),$f('subject','Subject'),$f('level','Level'),$f('language','Teaching language'),$f('delivery','Delivery','select','',['In person','Online','Hybrid']),$f('location','Location'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'health_beauty'=>[$f('service_type','Service / treatment'),$f('provider','Clinic / salon / provider'),$f('location','Location'),$f('availability','Date / availability'),$f('rating_min','Min rating','number'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
        'other'=>[$f('category','Category'),$f('keywords','Keywords'),$f('location','Location'),$f('condition','Condition / type'),$f('price_min','Min price','number'),$f('price_max','Max price','number')],
    ];
}

function aih_filter_terms(array $filters): string {
    $parts=[];foreach($filters as$k=>$v){if(is_array($v))$v=implode(' ',array_filter(array_map('strval',$v)));if(!is_scalar($v))continue;$v=trim((string)$v);if($v==='')continue;$v=substr($v,0,160);$label=str_replace('_',' ',preg_replace('/[^a-z0-9_]/i','',(string)$k));$parts[]=$label.' '.$v;}return trim(implode(' ',$parts));
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
    $q=trim((string)($in['q']??''));$filterMap=is_array($in['sector_filters']??null)?$in['sector_filters']:[];
    // Legacy top-level filters remain supported for existing saved forms/links.
    $legacy=[];foreach(['brand','make','model','keywords','location','condition','price_min','price_max','year_min']as$k)if(isset($in[$k])&&is_scalar($in[$k])&&trim((string)$in[$k])!=='')$legacy[$k]=(string)$in[$k];
    $common=is_array($filterMap['ALL']??null)?$filterMap['ALL']:[];$baseCommon=trim(implode(' ',array_filter([$q,aih_filter_terms($legacy),aih_filter_terms($common)])));
    $countries=aih_selected_countries($in);$sectors=aih_selected_sectors($in);$catalog=aih_country_catalog();$sectorCatalog=aih_sector_catalog();$queries=[];
    foreach($countries as$cc){$country=$catalog[$cc]['name']??$cc;foreach($sectors as$sector){$specific=$sector==='ALL'?[]:(is_array($filterMap[$sector]??null)?$filterMap[$sector]:[]);$criteria=trim(implode(' ',array_filter([$baseCommon,aih_filter_terms($specific)])));if($criteria==='')continue;$sectorWords=$sector==='ALL'?'':($sectorCatalog[$sector]['keywords']??'');$broad=trim($criteria.' '.$sectorWords.' '.$country);$queries[]=['q'=>$broad,'country'=>$cc,'sector'=>$sector,'domain'=>''];$domains=aih_sector_domains($cc,$sector);if($domains){$parts=array_map(fn($d)=>'site:'.$d,array_slice($domains,0,3));$queries[]=['q'=>trim($criteria.' '.$sectorWords.' '.$country.' ('.implode(' OR ',$parts).')'),'country'=>$cc,'sector'=>$sector,'domain'=>implode(',',$domains)];}if(count($queries)>=30)break 2;}}
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

function aih_search_searxng_urls(string $value): array {
    $parts=preg_split('/[\s,;]+/',trim($value))?:[];$out=[];foreach($parts as$u){$u=rtrim(trim((string)$u),'/');if($u!==''&&preg_match('#^https?://#i',$u)&&!in_array($u,$out,true))$out[]=$u;if(count($out)>=3)break;}return $out;
}

function aih_search_brave(string $q,string $key,int $limit): array {
    $key=trim($key);if($key==='')return [];$url='https://api.search.brave.com/res/v1/web/search?'.http_build_query(['q'=>$q,'count'=>max(1,min(20,$limit)),'safesearch'=>'moderate','text_decorations'=>'false']);
    $r=aih_http('GET',$url,null,['Accept: application/json','X-Subscription-Token: '.$key],20);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['web']['results']??[]):[]) as$x){if(!is_array($x))continue;$u=(string)($x['url']??'');if(!preg_match('#^https?://#i',$u))continue;$out[]=['title'=>aih_web_image_plain($x['title']??''),'url'=>$u,'snippet'=>aih_web_image_plain($x['description']??''),'engine'=>'brave'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_wikipedia(string $q,string $lang,int $limit): array {
    $lang=strtolower(preg_replace('/[^a-z]/','',substr($lang,0,2))?:'en');if(strlen($lang)!==2)$lang='en';
    $url='https://'.$lang.'.wikipedia.org/w/api.php?'.http_build_query(['action'=>'query','list'=>'search','srsearch'=>$q,'srlimit'=>max(1,min(12,$limit)),'format'=>'json','utf8'=>1]);
    $r=aih_http('GET',$url,null,['Accept: application/json'],15);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['query']['search']??[]):[]) as$x){if(!is_array($x))continue;$title=trim((string)($x['title']??''));if($title==='')continue;$out[]=['title'=>$title,'url'=>'https://'.$lang.'.wikipedia.org/wiki/'.rawurlencode(str_replace(' ','_',$title)),'snippet'=>aih_web_image_plain($x['snippet']??''),'engine'=>'wikipedia'];if(count($out)>=$limit)break;}return $out;
}



function aih_search_is_developer_query(string $q): bool {
    return (bool)preg_match('/\b(?:php|javascript|typescript|react|next\.?js|vue|svelte|node\.?js|npm|composer|packagist|wordpress|woocommerce|gutenberg|elementor|css|html|sql|mysql|postgres|api|json|github|git|repository|repo|plugin|theme|package|library|framework|sdk|docker|nginx|apache|error|exception|stack trace|debug|code|coding|web dev|developer)\b/ui',$q);
}

function aih_search_is_research_query(string $q): bool {
    return (bool)preg_match('/\b(?:research|paper|study|studies|arxiv|science|scientific|benchmark|latest|current|news|release|202[4-9]|evidence|source|sources|compare|comparison)\b/ui',$q);
}

function aih_search_github(string $q,int $limit=5): array {
    $url='https://api.github.com/search/repositories?'.http_build_query(['q'=>$q,'sort'=>'stars','order'=>'desc','per_page'=>max(1,min(10,$limit))]);
    $r=aih_http('GET',$url,null,['Accept: application/vnd.github+json','User-Agent: Free-AI-Hub-63'],18);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['items']??[]):[]) as$x){if(!is_array($x))continue;$u=(string)($x['html_url']??'');if(!preg_match('#^https://github\.com/#i',$u))continue;$desc=trim((string)($x['description']??''));$bits=[];if($desc!=='')$bits[]=$desc;if(isset($x['stargazers_count']))$bits[]='★ '.number_format((int)$x['stargazers_count']);if(!empty($x['language']))$bits[]=(string)$x['language'];$out[]=['title'=>(string)($x['full_name']??$x['name']??'GitHub repository'),'url'=>$u,'snippet'=>implode(' · ',$bits),'engine'=>'github'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_stackoverflow(string $q,int $limit=5): array {
    $url='https://api.stackexchange.com/2.3/search/advanced?'.http_build_query(['site'=>'stackoverflow','q'=>$q,'pagesize'=>max(1,min(10,$limit)),'order'=>'desc','sort'=>'relevance','filter'=>'default']);
    $r=aih_http('GET',$url,null,['Accept: application/json','User-Agent: Free-AI-Hub-63'],18);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['items']??[]):[]) as$x){if(!is_array($x))continue;$u=(string)($x['link']??'');if(!preg_match('#^https?://stackoverflow\.com/#i',$u))continue;$tags=is_array($x['tags']??null)?implode(', ',array_slice($x['tags'],0,5)):'';$snippet='Score '.(int)($x['score']??0).(!empty($x['is_answered'])?' · answered':'').($tags!==''?' · '.$tags:'');$out[]=['title'=>html_entity_decode(strip_tags((string)($x['title']??'Stack Overflow question')),ENT_QUOTES|ENT_HTML5,'UTF-8'),'url'=>$u,'snippet'=>$snippet,'engine'=>'stackoverflow'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_npm(string $q,int $limit=5): array {
    $url='https://registry.npmjs.org/-/v1/search?'.http_build_query(['text'=>$q,'size'=>max(1,min(10,$limit))]);$r=aih_http('GET',$url,null,['Accept: application/json','User-Agent: Free-AI-Hub-63'],18);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['objects']??[]):[]) as$x){$pkg=is_array($x['package']??null)?$x['package']:[];$name=trim((string)($pkg['name']??''));if($name==='')continue;$u='https://www.npmjs.com/package/'.rawurlencode($name);$desc=trim((string)($pkg['description']??''));$ver=trim((string)($pkg['version']??''));$out[]=['title'=>$name.($ver!==''?' · '.$ver:''),'url'=>$u,'snippet'=>$desc,'engine'=>'npm'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_packagist(string $q,int $limit=5): array {
    $url='https://packagist.org/search.json?'.http_build_query(['q'=>$q,'per_page'=>max(1,min(10,$limit))]);$r=aih_http('GET',$url,null,['Accept: application/json','User-Agent: Free-AI-Hub-63'],18);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['results']??[]):[]) as$x){if(!is_array($x))continue;$name=trim((string)($x['name']??''));if($name==='')continue;$u=(string)($x['url']??('https://packagist.org/packages/'.$name));$out[]=['title'=>$name,'url'=>$u,'snippet'=>trim((string)($x['description']??'')),'engine'=>'packagist'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_wordpress_directory(string $q,int $limit=5): array {
    if(!preg_match('/\b(?:wordpress|woocommerce|gutenberg|elementor|plugin|theme)\b/ui',$q))return [];
    $term=trim(preg_replace('/\b(?:wordpress|woocommerce|gutenberg|elementor|plugin|plugins|theme|themes)\b/ui',' ',$q)??$q);if($term==='')$term=$q;$out=[];$per=max(1,min(6,$limit));
    $pluginUrl='https://api.wordpress.org/plugins/info/1.2/?'.http_build_query(['action'=>'query_plugins','request'=>['search'=>$term,'per_page'=>$per,'page'=>1]]);
    $r=aih_http('GET',$pluginUrl,null,['Accept: application/json','User-Agent: Free-AI-Hub-63'],18);if($r['code']>=200&&$r['code']<300){$j=json_decode((string)$r['body'],true);foreach((is_array($j)?($j['plugins']??[]):[]) as$x){if(!is_array($x))continue;$slug=trim((string)($x['slug']??''));$name=trim((string)($x['name']??$slug));if($slug===''||$name==='')continue;$bits=[];if(!empty($x['short_description']))$bits[]=aih_web_image_plain($x['short_description']);if(isset($x['active_installs']))$bits[]=number_format((int)$x['active_installs']).' active installs';if(!empty($x['version']))$bits[]='v'.(string)$x['version'];$out[]=['title'=>$name.' · WordPress plugin','url'=>'https://wordpress.org/plugins/'.rawurlencode($slug).'/','snippet'=>implode(' · ',$bits),'engine'=>'wordpress.org'];if(count($out)>=$limit)break;}}
    if(count($out)<$limit&&preg_match('/\b(?:theme|themes|wordpress)\b/ui',$q)){$themeUrl='https://api.wordpress.org/themes/info/1.2/?'.http_build_query(['action'=>'query_themes','request'=>['search'=>$term,'per_page'=>$per,'page'=>1]]);$tr=aih_http('GET',$themeUrl,null,['Accept: application/json','User-Agent: Free-AI-Hub-63'],18);if($tr['code']>=200&&$tr['code']<300){$tj=json_decode((string)$tr['body'],true);foreach((is_array($tj)?($tj['themes']??[]):[]) as$x){if(!is_array($x))continue;$slug=trim((string)($x['slug']??''));$name=trim((string)($x['name']??$slug));if($slug===''||$name==='')continue;$bits=[];if(!empty($x['description']))$bits[]=aih_web_image_plain($x['description']);if(isset($x['rating']))$bits[]='rating '.(int)$x['rating'].'/100';if(!empty($x['version']))$bits[]='v'.(string)$x['version'];$out[]=['title'=>$name.' · WordPress theme','url'=>'https://wordpress.org/themes/'.rawurlencode($slug).'/','snippet'=>implode(' · ',$bits),'engine'=>'wordpress.org'];if(count($out)>=$limit)break;}}}
    return array_slice($out,0,$limit);
}

function aih_search_hackernews(string $q,int $limit=5): array {
    $url='https://hn.algolia.com/api/v1/search?'.http_build_query(['query'=>$q,'tags'=>'story','hitsPerPage'=>max(1,min(10,$limit))]);$r=aih_http('GET',$url,null,['Accept: application/json','User-Agent: Free-AI-Hub-63'],18);if($r['code']<200||$r['code']>=300)return [];$j=json_decode((string)$r['body'],true);$out=[];
    foreach((is_array($j)?($j['hits']??[]):[]) as$x){if(!is_array($x))continue;$title=trim((string)($x['title']??''));if($title==='')continue;$u=trim((string)($x['url']??''));if($u===''&&!empty($x['objectID']))$u='https://news.ycombinator.com/item?id='.rawurlencode((string)$x['objectID']);if(!preg_match('#^https?://#i',$u))continue;$snippet='Hacker News'.(isset($x['points'])?' · '.(int)$x['points'].' points':'').(isset($x['num_comments'])?' · '.(int)$x['num_comments'].' comments':'');$out[]=['title'=>$title,'url'=>$u,'snippet'=>$snippet,'engine'=>'hackernews'];if(count($out)>=$limit)break;}return $out;
}

function aih_search_arxiv(string $q,int $limit=5): array {
    if(!function_exists('simplexml_load_string'))return [];$url='https://export.arxiv.org/api/query?'.http_build_query(['search_query'=>'all:'.$q,'start'=>0,'max_results'=>max(1,min(8,$limit)),'sortBy'=>'relevance','sortOrder'=>'descending']);$r=aih_http('GET',$url,null,['Accept: application/atom+xml','User-Agent: Free-AI-Hub-63'],20);if($r['code']<200||$r['code']>=300)return [];
    libxml_use_internal_errors(true);$xml=@simplexml_load_string((string)$r['body']);if(!$xml){libxml_clear_errors();return [];}$out=[];foreach($xml->entry as$entry){$title=trim(preg_replace('/\s+/u',' ',(string)$entry->title)??(string)$entry->title);$summary=trim(preg_replace('/\s+/u',' ',(string)$entry->summary)??(string)$entry->summary);$u=trim((string)$entry->id);if($title===''||!preg_match('#^https?://#i',$u))continue;$out[]=['title'=>$title,'url'=>$u,'snippet'=>(function_exists('mb_substr')?mb_substr($summary,0,420):substr($summary,0,420)),'engine'=>'arxiv'];if(count($out)>=$limit)break;}libxml_clear_errors();return $out;
}

function aih_search_special_sources(string $q,bool $developer=true,bool $research=true,int $limitEach=4): array {
    $rows=[];$engines=[];
    if($developer&&aih_search_is_developer_query($q)){
        foreach([['github','aih_search_github'],['stackoverflow','aih_search_stackoverflow'],['npm','aih_search_npm'],['packagist','aih_search_packagist'],['wordpress.org','aih_search_wordpress_directory']] as[$name,$fn]){$got=$fn($q,$limitEach);if($got){$rows=array_merge($rows,$got);$engines[]=$name;}}
    }
    if($research&&aih_search_is_research_query($q)){
        foreach([['hackernews','aih_search_hackernews'],['arxiv','aih_search_arxiv']] as[$name,$fn]){$got=$fn($q,$limitEach);if($got){$rows=array_merge($rows,$got);$engines[]=$name;}}
    }
    return ['rows'=>$rows,'engines'=>$engines];
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
    $res=aih_ai_chat($messages,null,'research');if(empty($res['ok']))return $rows;$txt=trim((string)$res['text']);$txt=preg_replace('/^```(?:json)?\s*|\s*```$/i','',$txt);$j=json_decode($txt,true);if(!is_array($j))return $rows;
    foreach($j as$x){$i=(int)($x['i']??-1);if(!isset($rows[$i]))continue;foreach(['category','brand','model','location','company','salary','area','condition']as$k)if(isset($x[$k])&&is_scalar($x[$k]))$rows[$i][$k]=(string)$x[$k];foreach(['price','currency','year','mileage']as$k)if(empty($rows[$i][$k])&&isset($x[$k])&&is_scalar($x[$k]))$rows[$i][$k]=(string)$x[$k];}
    foreach($rows as &$row){if(empty($row['make'])&&!empty($row['brand']))$row['make']=$row['brand'];}unset($row);
    return $rows;
}

function aih_search_ai_summary(array $rows,string $query,string $lang): array {
    if(!$rows)return ['text'=>'','provider'=>''];
    $payload=[];foreach(array_slice($rows,0,20)as$r)$payload[]=['title'=>$r['title'],'snippet'=>$r['snippet'],'url'=>$r['url'],'country'=>$r['country']??'','price'=>$r['price']??'','year'=>$r['year']??'','sector'=>$r['sector']??'ALL','location'=>$r['location']??'','company'=>$r['company']??'','area'=>$r['area']??''];
    $prompt='Search query: '.$query."\nResults:\n".json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $res=aih_ai_chat([['role'=>'system','content'=>'Summarize and compare only the supplied sourced results. Do not invent facts. Mention uncertainty and important differences. Reply in language code '.$lang.'. Keep it concise.'],['role'=>'user','content'=>$prompt]],null,'research');
    return !empty($res['ok'])?['text'=>(string)$res['text'],'provider'=>(string)($res['provider_name']??$res['provider']??'')]:['text'=>'','provider'=>''];
}

function aih_search_simple_grounding(string $query,string $lang='en',?array $settings=null,int $limit=10): array {
    $settings=$settings??aih_settings();$q=trim($query);if($q==='')return ['rows'=>[],'engines'=>[]];$rows=[];$engines=[];
    $special=aih_search_special_sources($q,!empty($settings['search']['developer_grounding']),!empty($settings['search']['research_sources']),4);if(!empty($special['rows'])){$rows=array_merge($rows,$special['rows']);$engines=array_merge($engines,$special['engines']);}
    $ddg=aih_search_ddg_batch([['q'=>$q,'country'=>'','domain'=>'','sector'=>'ALL']],max(4,min(8,$limit)));if($ddg){$rows=array_merge($rows,$ddg);$engines[]='duckduckgo';}
    $wiki=aih_search_wikipedia($q,$lang,4);if($wiki){$rows=array_merge($rows,$wiki);$engines[]='wikipedia';}
    foreach(aih_search_searxng_urls((string)($settings['search']['searxng_url']??'')) as$sx){$got=aih_search_searxng($q,$sx,6);if($got){$rows=array_merge($rows,$got);$engines[]='searxng';}}
    $brave=aih_search_brave($q,(string)($settings['search']['brave_search_key']??''),6);if($brave){$rows=array_merge($rows,$brave);$engines[]='brave';}
    $ollama=aih_search_ollama_web($q,(string)($settings['search']['ollama_web_key']??''),6);if($ollama){$rows=array_merge($rows,$ollama);$engines[]='ollama-web';}
    $rows=aih_dedupe_results($rows,max(4,min(28,$limit)));return ['rows'=>$rows,'engines'=>array_values(array_unique($engines))];
}

function aih_run_search(array $input): array {
    $settings=aih_settings();$queries=aih_build_search_queries($input);if(!$queries)return ['ok'=>false,'error'=>'Enter a search query or structured criteria.'];
    $cacheKey=sha1(json_encode(['input'=>$input,'queries'=>$queries,'sx'=>$settings['search']['searxng_url']??'','ow'=>(bool)($settings['search']['ollama_web_key']??''),'br'=>(bool)($settings['search']['brave_search_key']??''),'dev'=>(bool)($settings['search']['developer_grounding']??true),'research'=>(bool)($settings['search']['research_sources']??true)],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));$cachePath=AIH_CACHE.'/search_'.$cacheKey.'.json';
    if(is_file($cachePath) && time()-(int)(filemtime($cachePath)?:0)<600){$cached=aih_read_json($cachePath,[]);if(is_array($cached)&&!empty($cached['ok'])){$cached['cached']=true;return $cached;}}
    $max=max(10,min(120,(int)($settings['search']['max_results']??60)));$rows=[];$ollamaKey=trim((string)($settings['search']['ollama_web_key']??''));$sx=trim((string)($settings['search']['searxng_url']??''));$braveKey=trim((string)($settings['search']['brave_search_key']??''));$engines=[];$researchNotes=[];
    if($ollamaKey!==''){$engines[]='ollama-web';foreach(array_slice($queries,0,8)as$x){foreach(aih_search_ollama_web($x['q'],$ollamaKey,5)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$r['sector']=$x['sector']??'ALL';$rows[]=$r;}}}
    foreach(aih_search_searxng_urls($sx) as$sxUrl){$engines[]='searxng';foreach(array_slice($queries,0,8)as$x){foreach(aih_search_searxng($x['q'],$sxUrl,5)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$r['sector']=$x['sector']??'ALL';$rows[]=$r;}}}
    if($braveKey!==''){$engines[]='brave';foreach(array_slice($queries,0,3)as$x){foreach(aih_search_brave($x['q'],$braveKey,8)as$r){$r['query']=$x['q'];$r['country']=$x['country'];$r['domain']=$x['domain'];$r['sector']=$x['sector']??'ALL';$rows[]=$r;}}}
    $wikiQ=trim((string)($input['q']??($queries[0]['q']??'')));if($wikiQ!==''){$wiki=aih_search_wikipedia($wikiQ,(string)($input['lang']??'en'),6);if($wiki){$engines[]='wikipedia';foreach($wiki as$r){$r['query']=$wikiQ;$r['country']='';$r['domain']='wikipedia.org';$r['sector']=(string)(aih_selected_sectors($input)[0]??'ALL');$rows[]=$r;}}}
    $specialQ=trim((string)($input['q']??($queries[0]['q']??'')));if($specialQ!==''){$special=aih_search_special_sources($specialQ,!empty($settings['search']['developer_grounding']),!empty($settings['search']['research_sources']),6);if(!empty($special['rows'])){$engines=array_merge($engines,$special['engines']);$sector=(string)(aih_selected_sectors($input)[0]??'ALL');foreach($special['rows'] as$r){$r['query']=$specialQ;$r['country']='';$r['domain']=parse_url((string)($r['url']??''),PHP_URL_HOST)?:'';$r['sector']=$sector;$rows[]=$r;}}}
    $p=$settings['providers']??[];$enabled=$p['enabled']??[];$keys=$p['keys']??[];$models=$p['models']??[];
    if(!empty($enabled['gemini'])&&trim((string)($keys['gemini']??''))!==''){$gRows=aih_search_gemini_grounding((string)($queries[0]['q']??($input['q']??'')),trim((string)$keys['gemini']),(string)($models['gemini']??'gemini-3.8-flash'),8);if($gRows){$engines[]='gemini-grounding';foreach($gRows as$r){$r['query']=(string)($input['q']??'');$r['country']='';$r['domain']='';$r['sector']=(string)(aih_selected_sectors($input)[0]??'ALL');$rows[]=$r;}}}
    if(!empty($enabled['groq'])&&trim((string)($keys['groq']??''))!==''){$note=aih_search_groq_browser_note((string)($queries[0]['q']??($input['q']??'')),trim((string)$keys['groq']),(string)($models['groq']??'openai/gpt-oss-20b'));if($note!==''){$engines[]='groq-browser-search';$researchNotes[]=['provider'=>'Groq Browser Search','text'=>$note];}}
    $ddg=aih_search_ddg_batch($queries,5);if($ddg){$engines[]='duckduckgo';$rows=array_merge($rows,$ddg);}
    $rows=aih_dedupe_results($rows,$max);if(!empty($settings['search']['ai_enrich']))$rows=aih_ai_enrich_rows($rows);
    $lang=(string)($input['lang']??'en');$sectorNames=[];foreach(aih_selected_sectors($input) as $sec)$sectorNames[]=$sec==='ALL'?'all sectors':($sec);$summaryQuery=trim((string)($input['q']??'').' | sectors: '.implode(', ',$sectorNames));$summary=aih_search_ai_summary($rows,$summaryQuery,$lang);
    $id=aih_new_id('report');$report=['id'=>$id,'created_at'=>date(DATE_ATOM),'input'=>$input,'queries'=>$queries,'results'=>$rows,'engines'=>$engines,'research_notes'=>$researchNotes,'ai_summary'=>$summary];aih_write_json(AIH_REPORTS.'/'.$id.'.json',$report);
    $warning=count($rows)===0?'No indexed results were returned. DuckDuckGo HTML can be blocked by some hosts; add one or more SearXNG endpoints, Ollama Web Search or a Brave Search key for stronger coverage.':'';
    $response=['ok'=>true,'report_id'=>$id,'results'=>$rows,'queries'=>$queries,'count'=>count($rows),'engines'=>array_values(array_unique($engines)),'research_notes'=>$researchNotes,'ai_summary'=>$summary,'cached'=>false,'warning'=>$warning];aih_write_json($cachePath,$response);return $response;
}
