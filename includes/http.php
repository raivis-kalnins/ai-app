<?php
declare(strict_types=1);

function aih_http(string $method, string $url, ?string $body=null, array $headers=[], int $timeout=60): array {
    if(!function_exists('curl_init')){
        $hdr=array_merge(['User-Agent: FreeAIHub63/'.AIH_VERSION],$headers);
        $ctx=stream_context_create(['http'=>['method'=>strtoupper($method),'header'=>implode("\r\n",$hdr)."\r\n",'content'=>$body??'','timeout'=>$timeout,'ignore_errors'=>true,'follow_location'=>1,'max_redirects'=>4]]);
        $data=@file_get_contents($url,false,$ctx);$raw=$http_response_header??[];$code=0;$respHeaders=[];$ctype='';
        foreach($raw as$line){if(preg_match('#^HTTP/\S+\s+(\d+)#i',$line,$m))$code=(int)$m[1];elseif(($p=strpos($line,':'))!==false){$k=strtolower(trim(substr($line,0,$p)));$v=trim(substr($line,$p+1));$respHeaders[$k]=$v;if($k==='content-type')$ctype=$v;}}
        return ['code'=>$code,'body'=>$data===false?'':(string)$data,'error'=>$data===false?'HTTP stream request failed':'','headers'=>$respHeaders,'content_type'=>$ctype];
    }
    $ch=curl_init($url); $respHeaders=[];
    $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>4,CURLOPT_CONNECTTIMEOUT=>12,CURLOPT_TIMEOUT=>$timeout,CURLOPT_USERAGENT=>'FreeAIHub63/'.AIH_VERSION,CURLOPT_HTTPHEADER=>$headers,CURLOPT_HEADERFUNCTION=>function($ch,$line)use(&$respHeaders){$len=strlen($line);$p=strpos($line,':');if($p!==false){$k=strtolower(trim(substr($line,0,$p)));$v=trim(substr($line,$p+1));$respHeaders[$k]=$v;}return $len;}];
    if(strtoupper($method)!=='GET'){$opts[CURLOPT_CUSTOMREQUEST]=strtoupper($method);if($body!==null)$opts[CURLOPT_POSTFIELDS]=$body;}curl_setopt_array($ch,$opts);$data=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);$err=curl_error($ch);$ctype=(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE);curl_close($ch);return ['code'=>$code,'body'=>$data===false?'':(string)$data,'error'=>$err,'headers'=>$respHeaders,'content_type'=>$ctype];
}

function aih_http_json(string $method,string $url,?array $json=null,array $headers=[],int $timeout=60): array {
    $headers=array_merge(['Content-Type: application/json','Accept: application/json'],$headers);
    return aih_http($method,$url,$json===null?null:json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),$headers,$timeout);
}

function aih_multi_get(array $requests, int $timeout=25): array {
    if(!function_exists('curl_multi_init')){ $out=[]; foreach($requests as$req)$out[]=aih_http('GET',(string)$req['url'],null,$req['headers']??[],$timeout); return $out; }
    $mh=curl_multi_init(); $handles=[]; $out=[];
    foreach($requests as $i=>$req){
        $ch=curl_init((string)$req['url']);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>$timeout,CURLOPT_USERAGENT=>'Mozilla/5.0 FreeAIHub63/'.AIH_VERSION,CURLOPT_HTTPHEADER=>$req['headers']??[]]);
        curl_multi_add_handle($mh,$ch); $handles[$i]=$ch;
    }
    do { $status=curl_multi_exec($mh,$running); if($running) curl_multi_select($mh,0.5); } while($running && $status===CURLM_OK);
    foreach($handles as $i=>$ch){$body=curl_multi_getcontent($ch);$out[$i]=['code'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'body'=>(string)$body,'content_type'=>(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE),'error'=>curl_error($ch)];curl_multi_remove_handle($mh,$ch);curl_close($ch);} curl_multi_close($mh);
    ksort($out); return array_values($out);
}
