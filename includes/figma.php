<?php
declare(strict_types=1);

const AIH_FIGMA_TOKEN_FILE = AIH_STORAGE . '/figma_oauth.json';

function aih_figma_default_redirect_uri(): string {
    $secure = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    $scheme = $secure ? 'https' : 'http';
    $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $script = str_replace('\\','/',(string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = rtrim(dirname($script), '/.');
    $path = ($dir === '' ? '/' : $dir . '/');
    return $scheme . '://' . $host . $path;
}

function aih_figma_config(array $settings): array {
    $f = is_array($settings['integrations']['figma'] ?? null) ? $settings['integrations']['figma'] : [];
    return [
        'client_id' => trim((string)($f['client_id'] ?? '')),
        'client_secret' => trim((string)($f['client_secret'] ?? '')),
        'redirect_uri' => trim((string)($f['redirect_uri'] ?? '')) ?: aih_figma_default_redirect_uri(),
        'scopes' => trim((string)($f['scopes'] ?? 'current_user:read,file_content:read,file_metadata:read,projects:read,folders:read')),
        'team_id' => trim((string)($f['team_id'] ?? '')),
    ];
}

function aih_figma_token_read(): array {
    $v = aih_read_json(AIH_FIGMA_TOKEN_FILE, []);
    return is_array($v) ? $v : [];
}
function aih_figma_token_save(array $v): void { aih_write_json(AIH_FIGMA_TOKEN_FILE, $v); }
function aih_figma_token_clear(): void { if (is_file(AIH_FIGMA_TOKEN_FILE)) @unlink(AIH_FIGMA_TOKEN_FILE); }

function aih_figma_public_status(array $settings): array {
    $cfg = aih_figma_config($settings); $tok = aih_figma_token_read();
    return [
        'configured' => $cfg['client_id'] !== '' && $cfg['client_secret'] !== '',
        'connected' => !empty($tok['access_token']),
        'redirect_uri' => $cfg['redirect_uri'],
        'scopes' => $cfg['scopes'],
        'expires_at' => (int)($tok['expires_at'] ?? 0),
        'user' => is_array($tok['user'] ?? null) ? $tok['user'] : [],
    ];
}

function aih_figma_scopes_normalize(string $raw): string {
    $allowed = [
        'current_user:read','file_content:read','file_metadata:read','file_comments:read',
        'file_versions:read','file_dev_resources:read','library_assets:read','library_content:read',
        'team_library_content:read','selections:read','folders:read','folder_metadata:read','projects:read','project_metadata:read'
    ];
    $parts = preg_split('/[\s,]+/', trim($raw)) ?: [];
    $out=[]; foreach($parts as $s){$s=trim($s);if($s!==''&&in_array($s,$allowed,true)&&!in_array($s,$out,true))$out[]=$s;}
    if(!$out)$out=['current_user:read','file_content:read','file_metadata:read'];
    if(!in_array('current_user:read',$out,true)) array_unshift($out,'current_user:read');
    return implode(',',$out);
}

function aih_figma_save_credentials(array $input): array {
    $s=aih_settings(); if(!isset($s['integrations'])||!is_array($s['integrations']))$s['integrations']=[];
    $old=is_array($s['integrations']['figma']??null)?$s['integrations']['figma']:[];
    $clientId=trim((string)($input['figma_client_id']??($old['client_id']??'')));
    $secret=trim((string)($input['figma_client_secret']??''));
    $redirect=trim((string)($input['figma_redirect_uri']??($old['redirect_uri']??'')));
    $scopes=aih_figma_scopes_normalize((string)($input['figma_scopes']??($old['scopes']??'')));
    $s['integrations']['figma']=[
        'client_id'=>$clientId,
        'client_secret'=>$secret!==''?$secret:(string)($old['client_secret']??''),
        'redirect_uri'=>$redirect!==''?$redirect:aih_figma_default_redirect_uri(),
        'scopes'=>$scopes,
        'team_id'=>trim((string)($input['figma_team_id']??($old['team_id']??''))),
    ];
    aih_save_settings($s); return $s;
}

function aih_figma_authorize_url(array $settings): string {
    $cfg=aih_figma_config($settings);
    if($cfg['client_id']===''||$cfg['client_secret']==='') throw new RuntimeException('Add Figma Client ID and Client Secret first.');
    $state=bin2hex(random_bytes(24));
    $verifier=rtrim(strtr(base64_encode(random_bytes(48)),'+/','-_'),'=');
    $challenge=rtrim(strtr(base64_encode(hash('sha256',$verifier,true)),'+/','-_'),'=');
    $_SESSION['figma_oauth']=['state'=>$state,'verifier'=>$verifier,'redirect_uri'=>$cfg['redirect_uri'],'created'=>time()];
    return 'https://www.figma.com/oauth?'.http_build_query([
        'client_id'=>$cfg['client_id'],'redirect_uri'=>$cfg['redirect_uri'],'scope'=>$cfg['scopes'],
        'state'=>$state,'response_type'=>'code','code_challenge'=>$challenge,'code_challenge_method'=>'S256'
    ],'','&',PHP_QUERY_RFC3986);
}

function aih_figma_exchange_code(array $settings,string $code,string $state): array {
    $oauth=is_array($_SESSION['figma_oauth']??null)?$_SESSION['figma_oauth']:[];
    if($code===''||$state===''||empty($oauth['state'])||!hash_equals((string)$oauth['state'],$state)) throw new RuntimeException('Figma OAuth state mismatch. Please connect again.');
    if(time()-(int)($oauth['created']??0)>600) throw new RuntimeException('Figma OAuth session expired. Please connect again.');
    $cfg=aih_figma_config($settings); $redirect=(string)($oauth['redirect_uri']??$cfg['redirect_uri']);
    $body=http_build_query(['redirect_uri'=>$redirect,'code'=>$code,'grant_type'=>'authorization_code','code_verifier'=>(string)($oauth['verifier']??'')],'','&',PHP_QUERY_RFC3986);
    $auth='Authorization: Basic '.base64_encode($cfg['client_id'].':'.$cfg['client_secret']);
    $r=aih_http('POST','https://api.figma.com/v1/oauth/token',$body,['Content-Type: application/x-www-form-urlencoded','Accept: application/json',$auth],30);
    $j=json_decode($r['body'],true);
    if($r['code']<200||$r['code']>=300||!is_array($j)||empty($j['access_token'])) throw new RuntimeException('Figma token exchange failed (HTTP '.$r['code'].').');
    $tok=['access_token'=>(string)$j['access_token'],'refresh_token'=>(string)($j['refresh_token']??''),'token_type'=>(string)($j['token_type']??'bearer'),'user_id_string'=>(string)($j['user_id_string']??''),'expires_at'=>time()+max(60,(int)($j['expires_in']??7776000)),'updated_at'=>date(DATE_ATOM)];
    unset($_SESSION['figma_oauth']); aih_figma_token_save($tok);
    $me=aih_figma_test($settings,true); return $me;
}

function aih_figma_refresh(array $settings): array {
    $cfg=aih_figma_config($settings);$tok=aih_figma_token_read();$refresh=trim((string)($tok['refresh_token']??''));
    if($refresh==='') throw new RuntimeException('Figma refresh token is missing. Reconnect Figma.');
    $auth='Authorization: Basic '.base64_encode($cfg['client_id'].':'.$cfg['client_secret']);
    $body=http_build_query(['refresh_token'=>$refresh,'grant_type'=>'refresh_token'],'','&',PHP_QUERY_RFC3986);
    $r=aih_http('POST','https://api.figma.com/v1/oauth/token',$body,['Content-Type: application/x-www-form-urlencoded','Accept: application/json',$auth],30);
    if($r['code']<200||$r['code']>=300){
        $legacy=http_build_query(['refresh_token'=>$refresh],'','&',PHP_QUERY_RFC3986);
        $r=aih_http('POST','https://api.figma.com/v1/oauth/refresh',$legacy,['Content-Type: application/x-www-form-urlencoded','Accept: application/json',$auth],30);
    }
    $j=json_decode($r['body'],true);if($r['code']<200||$r['code']>=300||!is_array($j)||empty($j['access_token']))throw new RuntimeException('Figma token refresh failed. Reconnect Figma.');
    $tok['access_token']=(string)$j['access_token'];if(!empty($j['refresh_token']))$tok['refresh_token']=(string)$j['refresh_token'];$tok['expires_at']=time()+max(60,(int)($j['expires_in']??7776000));$tok['updated_at']=date(DATE_ATOM);aih_figma_token_save($tok);return $tok;
}

function aih_figma_access_token(array $settings): string {
    $tok=aih_figma_token_read();if(empty($tok['access_token']))throw new RuntimeException('Figma is not connected.');
    if((int)($tok['expires_at']??0)>0&&(int)$tok['expires_at']<time()+120)$tok=aih_figma_refresh($settings);
    return (string)$tok['access_token'];
}

function aih_figma_test(array $settings,bool $saveUser=true): array {
    $token=aih_figma_access_token($settings);
    $r=aih_http('GET','https://api.figma.com/v1/me',null,['Authorization: Bearer '.$token,'Accept: application/json'],25);
    $j=json_decode($r['body'],true);if($r['code']<200||$r['code']>=300||!is_array($j))throw new RuntimeException('Figma connection test failed (HTTP '.$r['code'].').');
    $user=['id'=>(string)($j['id']??''),'handle'=>(string)($j['handle']??''),'email'=>(string)($j['email']??''),'img_url'=>(string)($j['img_url']??'')];
    if($saveUser){$tok=aih_figma_token_read();$tok['user']=$user;$tok['tested_at']=date(DATE_ATOM);aih_figma_token_save($tok);}
    return ['ok'=>true,'user'=>$user,'status'=>aih_figma_public_status($settings)];
}


function aih_figma_api_get(array $settings,string $url): array {
    $token=aih_figma_access_token($settings);
    $r=aih_http('GET',$url,null,['Authorization: Bearer '.$token,'Accept: application/json'],35);
    $j=json_decode((string)$r['body'],true);
    if($r['code']<200||$r['code']>=300||!is_array($j)){
        $msg=is_array($j)?(string)($j['message']??$j['err']??''):'Figma API request failed';
        throw new RuntimeException(trim($msg)!==''?$msg:'Figma API HTTP '.$r['code']);
    }
    return $j;
}

function aih_figma_list_projects(array $settings,string $teamId): array {
    $teamId=preg_replace('/[^0-9]/','',$teamId);
    if($teamId==='') throw new RuntimeException('Figma Team ID is required.');
    $rows=[];$mode='folders-v2';
    try{
        $j=aih_figma_api_get($settings,'https://api.figma.com/v2/teams/'.rawurlencode($teamId).'/folders');
        foreach((array)($j['folders']??[]) as $x) if(is_array($x)) $rows[]=['id'=>(string)($x['id']??''),'name'=>(string)($x['name']??'Folder'),'type'=>'folder'];
    }catch(Throwable $e){
        $mode='projects-v1';
        $j=aih_figma_api_get($settings,'https://api.figma.com/v1/teams/'.rawurlencode($teamId).'/projects');
        foreach((array)($j['projects']??[]) as $x) if(is_array($x)) $rows[]=['id'=>(string)($x['id']??''),'name'=>(string)($x['name']??'Project'),'type'=>'project'];
    }
    return ['ok'=>true,'items'=>$rows,'mode'=>$mode,'team_id'=>$teamId];
}

function aih_figma_list_project_files(array $settings,string $id,string $type='folder'): array {
    $id=preg_replace('/[^0-9A-Za-z_-]/','',$id);
    if($id==='') throw new RuntimeException('Figma folder/project ID is required.');
    $j=$type==='project'
        ? aih_figma_api_get($settings,'https://api.figma.com/v1/projects/'.rawurlencode($id).'/files')
        : aih_figma_api_get($settings,'https://api.figma.com/v2/folders/'.rawurlencode($id).'/files');
    $rows=[];
    foreach((array)($j['files']??[]) as $x) if(is_array($x)) $rows[]=[
        'key'=>(string)($x['key']??''),'name'=>(string)($x['name']??'Figma file'),
        'thumbnail_url'=>(string)($x['thumbnail_url']??$x['thumbnailUrl']??''),
        'last_modified'=>(string)($x['last_modified']??$x['lastModified']??''),
    ];
    return ['ok'=>true,'files'=>$rows,'source_type'=>$type,'source_id'=>$id];
}

function aih_figma_parse_file_key(string $value): string {
    $value=trim($value);
    if(preg_match('#figma\\.com/(?:design|file|make)/([0-9A-Za-z]{22,128})#i',$value,$m)) return $m[1];
    if(preg_match('/^[0-9A-Za-z]{22,128}$/',$value)) return $value;
    return '';
}

function aih_figma_file_summary(array $settings,string $key): array {
    $key=aih_figma_parse_file_key($key);
    if($key==='') throw new RuntimeException('Paste a valid Figma design/file URL or file key.');
    $j=aih_figma_api_get($settings,'https://api.figma.com/v1/files/'.rawurlencode($key).'?depth=3');
    $pages=[];$nodes=[];
    foreach((array)($j['document']['children']??[]) as $page){
        if(!is_array($page))continue;$pageRow=['id'=>(string)($page['id']??''),'name'=>(string)($page['name']??'Page'),'frames'=>[]];
        foreach(array_slice((array)($page['children']??[]),0,60) as $n){if(!is_array($n))continue;$frame=['id'=>(string)($n['id']??''),'name'=>(string)($n['name']??''),'type'=>(string)($n['type']??'')];$pageRow['frames'][]=$frame;$nodes[]=$frame;}
        $pages[]=$pageRow;
    }
    return ['ok'=>true,'key'=>$key,'name'=>(string)($j['name']??'Figma file'),'thumbnail_url'=>(string)($j['thumbnailUrl']??''),'last_modified'=>(string)($j['lastModified']??''),'editor_type'=>(string)($j['editorType']??''),'pages'=>$pages,'nodes'=>$nodes];
}
