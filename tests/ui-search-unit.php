<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Pure, offline UI/search parser checks. No bootstrap, runtime storage or network.
require dirname(__DIR__).'/includes/i18n.php';
function aih_e(string $value): string { return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
require dirname(__DIR__).'/includes/ui_extras.php';
require dirname(__DIR__).'/includes/search.php';
$checks=0;
function check(bool $ok,string $name): void {
    global $checks;
    if(!$ok){fwrite(STDERR,"FAIL: $name\n");exit(1);}
    $checks++;
}
$base=dirname(__DIR__);
$langs=aih_languages();
check(count($langs)===15,'15 supported languages');
foreach($langs as $code=>$label){
    $path=$base.'/assets/flags/'.$code.'.svg';
    check(is_file($path),'Flag '.$code.' exists');
    $svg=file_get_contents($path);
    check(is_string($svg)&&str_contains($svg,'<svg')&&str_contains($svg,'</svg>'),'Flag '.$code.' valid SVG text');
    if(function_exists('simplexml_load_string'))check(@simplexml_load_string($svg)!==false,'Flag '.$code.' parses as XML');
    $menu=aih_locale_switcher($code);
    check(str_contains($menu,'assets/flags/'.$code.'.svg'),'Picker uses correct '.$code.' flag');
    check(str_contains($menu,'aria-selected="true"'),'Picker marks selected '.$code);
    check(substr_count($menu,'data-language-value=')===15,'Picker offers all languages '.$code);
    $banner=aih_digitalpulse_cta($code);
    check(str_contains($banner,'https://digitalpulse.click/'),'DigitalPulse link '.$code);
    check(str_contains($banner,'<h2>')&&str_contains($banner,'rel="noopener noreferrer"'),'Accessible CTA '.$code);
    check(str_contains(aih_site_footer($code),'digitalpulse.click'),'Footer credit '.$code);
}
$valid=aih_parse_searxng_response(['code'=>200,'body'=>json_encode(['results'=>[
 ['url'=>'https://example.org/a','title'=>'Good','content'=>'<b>test</b>'],
 ['url'=>'javascript:alert(1)','title'=>'Bad','content'=>'invalid'],
 ['url'=>'https://example.org/b','title'=>'Two','content'=>'text'],
]])],6);
check(count($valid)===2,'Unsafe link rejected');
check($valid[0]['snippet']==='test','HTML removed from snippet');
check(count(aih_parse_searxng_response(['code'=>429,'body'=>'{}'],5))===0,'HTTP errors ignored');
check(count(aih_parse_searxng_response(['code'=>200,'body'=>'{{garbage'],5))===0,'Malformed JSON safe');
check(count(aih_parse_searxng_response(['code'=>200,'body'=>'{}'],5))===0,'Missing results safe');
check(count(aih_search_searxng_urls('https://one.example/; https://one.example; https://two.example'))===2,'Duplicate endpoints removed');
check(aih_search_is_developer_query('Fix my WooCommerce plugin'),'Developer source routing');
check(aih_search_is_research_query('Latest AI benchmark'),'Research source routing');
$deduped=aih_dedupe_results([
 ['url'=>'https://example.org/item?test=1','title'=>'Phone £150','snippet'=>'demo'],
 ['url'=>'https://example.org/item?test=2','title'=>'Duplicate','snippet'=>''],
 ['url'=>'https://example.org/other','title'=>'Another','snippet'=>''],
],9);
check(count($deduped)===2,'Deduped results');
check($deduped[0]['currency']==='GBP','Price extraction preserved');
check(aih_analyze_search_report('../etc/passwd')['error']==='Invalid report ID','Invalid report path blocked');
echo "$checks checks passed. No storage data or live providers were accessed.\n";
