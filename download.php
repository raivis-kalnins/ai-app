<?php
declare(strict_types=1);
require __DIR__.'/includes/bootstrap.php';
aih_headers(false);aih_require_login();
$bucket=(string)($_GET['bucket']??'');$file=aih_safe_name((string)($_GET['file']??''));
if(!in_array($bucket,['uploads','generated','reports'],true)||$file===''){http_response_code(400);exit('Bad request');}
$path=aih_path($bucket,$file);if(!is_file($path)){http_response_code(404);exit('Not found');}
$size=(int)(filesize($path)?:0);$max=(int)(aih_config()['app']['max_upload_bytes']??1073741824);if($size>$max){http_response_code(413);exit('File exceeds configured 1 GB limit');}
header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.str_replace('"','',$file).'"');header('Content-Length: '.$size);header('Accept-Ranges: bytes');
$h=fopen($path,'rb');if(!$h){http_response_code(500);exit('Open failed');}while(!feof($h)){echo fread($h,8*1024*1024);flush();}fclose($h);
