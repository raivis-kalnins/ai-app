<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('AIH_TEST_NO_BOOTSTRAP',true);
require dirname(__DIR__).'/includes/bootstrap.php';
require dirname(__DIR__).'/includes/providers.php';
require dirname(__DIR__).'/includes/images.php';
function verify(bool $ok,string $name):void {if(!$ok)throw new RuntimeException($name);echo "PASS $name\n";}
$defaults=aih_default_settings();
verify(AIH_PRIVATE_OWNER_HASH==='', 'public release has no shared owner credential');
$old=$defaults;$old['password_hash']=password_hash('test-fixture-not-a-real-owner-password', PASSWORD_DEFAULT);$old['providers']['enabled']['puter']=true;$old['providers']['keys']['pollinations']='saved-secret';$old['search']['brave_search_key']='saved-search';
$old['images']['speed_mode']='fast';$old['images']['provider']='pollinations';
$effective=aih_free_settings($old);
verify($old['providers']['enabled']['puter']===true,'policy does not mutate source settings');
verify($effective['providers']['keys']['pollinations']==='saved-secret','stored inference key preserved');
verify(!$effective['providers']['enabled']['puter'],'Puter disabled');
verify(!$effective['providers']['enabled']['ollama_local'],'shared-host loopback skipped');
verify($effective['images']['sdwebui_url']==='','shared-host image loopback skipped');
verify($effective['search']['brave_search_key']==='','metered search disabled');
verify(aih_aihorde_queue_limit($effective)===0,'long accepted queues preserved');
verify($effective['images']['provider']==='auto','old paid selection uses safe auto');
foreach(['puter','pollinations','cloudflare','huggingface','custom_openai','unknown'] as $id){verify(!aih_free_provider_allowed($id),'blocked '.$id);verify(aih_provider_call($id,[],$old)['code']===403,'direct chat guard '.$id);}
foreach(['cloudflare_image','huggingface_image','siliconflow_image','pollinations','alibaba_qwen_image'] as $id){verify(aih_image_call($id,'test',$old)['code']===403,'direct image guard '.$id);}
verify(aih_free_provider_allowed('aihorde')&&aih_free_provider_allowed('webllm_browser'),'no-key generation available');
foreach(['Create a photo','Skaistu kartīti ar tekstu Jauku dienu!','fantasy art'] as $prompt){$order=aih_image_prompt_order($defaults['images']['order'],$prompt,'auto',$effective);verify($order===['sdwebui','comfyui','aihorde'],'auto always generates');}
verify(aih_image_prompt_order(['free_web'],'photo','free_web',$effective)===['free_web'],'explicit source search retained');
$old['providers']['order']=['puter','puter','unknown'];$old['images']['order']=['aihorde','aihorde','unknown'];$m=aih_migrate_settings($old);
foreach(['providers','images'] as $kind){$a=$m[$kind]['order'];$b=$defaults[$kind]['order'];sort($a);sort($b);verify($a===$b,'migration includes every '.$kind.' exactly once');}
verify($m['password_hash']===$old['password_hash'],'existing owner password preserved');
$remote=$defaults;$remote['images']['sdwebui_url']='https://gpu.example.test';verify(aih_free_settings($remote)['images']['sdwebui_url']===$remote['images']['sdwebui_url'],'explicit external self-hosted endpoint retained');
$httpResponses=[];
function aih_http(...$args):array {global $httpResponses;return array_shift($httpResponses);}
function reply(int $code,string $body=''):array{return ['code'=>$code,'body'=>$body,'headers'=>[],'error'=>''];}
$id='12345678-1234-1234-1234-123456789012';
$httpResponses=[reply(429)];verify(aih_aihorde_status($id,$effective)['pending']===true,'check rate limit preserves job');
$httpResponses=[reply(200,'{"done":true}'),reply(503)];verify(aih_aihorde_status($id,$effective)['pending']===true,'result server error preserves job');
$httpResponses=[reply(200,'{"faulted":true}')];verify(aih_aihorde_status($id,$effective)['ok']===false,'faulted job is terminal');
$httpResponses=[reply(404,'expired')];verify(aih_aihorde_status($id,$effective)['ok']===false,'expired job is terminal');
