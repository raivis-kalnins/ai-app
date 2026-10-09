<?php
declare(strict_types=1);

if(!defined('AIH_PLUGINS')) define('AIH_PLUGINS', AIH_ROOT . '/plugins');

function aih_plugins_bootstrap(): void {
    if(!is_dir(AIH_PLUGINS)) @mkdir(AIH_PLUGINS,0770,true);
    if(!is_file(AIH_PLUGINS.'/.htaccess')) @file_put_contents(AIH_PLUGINS.'/.htaccess',"Require all denied\nDeny from all\n");
    if(!is_file(AIH_STORAGE.'/plugin_state.json')) aih_write_json(AIH_STORAGE.'/plugin_state.json',['enabled'=>['wordpress-expert'=>true,'code-reviewer'=>true,'research-compare'=>true]]);
}

function aih_plugin_state(): array {
    $s=aih_read_json(AIH_STORAGE.'/plugin_state.json',['enabled'=>[]]);
    if(!is_array($s))$s=[];if(!is_array($s['enabled']??null))$s['enabled']=[];return $s;
}
function aih_plugin_state_save(array $s): void { aih_write_json(AIH_STORAGE.'/plugin_state.json',$s); }

function aih_plugin_manifest_from_dir(string $dir): ?array {
    $file=rtrim($dir,'/').'/plugin.json';if(!is_file($file))return null;
    $m=aih_read_json($file,[]);if(!is_array($m))return null;
    $id=preg_replace('/[^a-z0-9_-]/','',strtolower((string)($m['id']??'')));
    if($id===''||$id!==basename($dir))return null;
    $name=trim((string)($m['name']??''));if($name==='')return null;
    $m['id']=$id;$m['name']=$name;$m['version']=trim((string)($m['version']??'1.0.0'))?:'1.0.0';
    $m['description']=trim((string)($m['description']??''));$m['category']=trim((string)($m['category']??'Other'))?:'Other';
    $m['icon']=trim((string)($m['icon']??strtoupper(substr($name,0,2))))?:'AI';
    $m['author']=trim((string)($m['author']??'Free AI Hub 63'))?:'Free AI Hub 63';
    $m['permissions']=is_array($m['permissions']??null)?array_values(array_slice(array_filter(array_map('strval',$m['permissions'])),0,12)):[];
    $m['quick_actions']=is_array($m['quick_actions']??null)?array_values(array_slice($m['quick_actions'],0,8)):[];
    $m['ai_context']=substr(trim((string)($m['ai_context']??'')),0,8000);
    $m['setup_url']=trim((string)($m['setup_url']??''));if($m['setup_url']!==''&&!preg_match('#^https?://#i',$m['setup_url']))$m['setup_url']='';
    $m['bundled']=is_file($dir.'/.bundled');
    return $m;
}

function aih_plugins_catalog(): array {
    aih_plugins_bootstrap();$state=aih_plugin_state();$out=[];
    foreach(glob(AIH_PLUGINS.'/*',GLOB_ONLYDIR)?:[] as$dir){$m=aih_plugin_manifest_from_dir($dir);if(!$m)continue;$m['enabled']=!empty($state['enabled'][$m['id']]);$out[$m['id']]=$m;}
    uasort($out,fn($a,$b)=>[$a['category'],$a['name']]<=>[$b['category'],$b['name']]);return $out;
}

function aih_plugin_toggle(string $id,bool $enabled): array {
    $id=preg_replace('/[^a-z0-9_-]/','',strtolower($id));$cat=aih_plugins_catalog();if(!isset($cat[$id]))throw new RuntimeException('Plugin not found');
    $s=aih_plugin_state();$s['enabled'][$id]=$enabled;aih_plugin_state_save($s);return ['ok'=>true,'id'=>$id,'enabled'=>$enabled];
}

function aih_plugin_remove(string $id): array {
    $id=preg_replace('/[^a-z0-9_-]/','',strtolower($id));$cat=aih_plugins_catalog();if(!isset($cat[$id]))throw new RuntimeException('Plugin not found');
    if(!empty($cat[$id]['bundled']))throw new RuntimeException('Bundled plugins cannot be removed. Disable them instead.');
    $dir=AIH_PLUGINS.'/'.$id;aih_rm_tree($dir);$s=aih_plugin_state();unset($s['enabled'][$id]);aih_plugin_state_save($s);return ['ok'=>true];
}

function aih_rm_tree(string $dir): void {
    if(!is_dir($dir))return;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as$f){$p=$f->getPathname();$f->isDir()?@rmdir($p):@unlink($p);}@rmdir($dir);
}

function aih_plugin_install_zip(string $tmp,string $original='plugin.zip'): array {
    if(!is_file($tmp))throw new RuntimeException('Plugin ZIP missing');
    $work=AIH_CACHE.'/plugin-'.bin2hex(random_bytes(6));$cleanupRoot=$work;@mkdir($work,0770,true);
    $allowed=['json','md','txt','png','jpg','jpeg','webp','svg'];$entries=[];
    try{
        if(class_exists('ZipArchive')){
            $z=new ZipArchive();if($z->open($tmp)!==true)throw new RuntimeException('Invalid ZIP');
            for($i=0;$i<$z->numFiles;$i++){$n=(string)$z->getNameIndex($i);$entries[]=$n;if(str_contains($n,'..')||str_starts_with($n,'/')||str_contains($n,"\\"))throw new RuntimeException('Unsafe ZIP path');if(str_ends_with($n,'/'))continue;$ext=strtolower(pathinfo($n,PATHINFO_EXTENSION));if(!in_array($ext,$allowed,true))throw new RuntimeException('Plugin ZIP contains unsupported file type: '.$ext);}
            if(!$z->extractTo($work))throw new RuntimeException('Could not extract plugin');$z->close();
        } else {
            try{$archive=$work.'/package.zip';if(!copy($tmp,$archive))throw new RuntimeException('Could not stage ZIP');$phar=new PharData($archive);foreach(new RecursiveIteratorIterator($phar)as$f){$prefix='phar://'.$archive.'/';$n=str_replace($prefix,'',$f->getPathname());$entries[]=$n;if(str_contains($n,'..')||str_starts_with($n,'/')||str_contains($n,"\\"))throw new RuntimeException('Unsafe ZIP path');$ext=strtolower(pathinfo($n,PATHINFO_EXTENSION));if($ext!==''&&!in_array($ext,$allowed,true))throw new RuntimeException('Plugin ZIP contains unsupported file type: '.$ext);} $extract=$work.'/extract';@mkdir($extract,0770,true);$phar->extractTo($extract,null,true);$work=$extract;}catch(Throwable $e){throw new RuntimeException('ZIP support unavailable or archive invalid: '.$e->getMessage());}
        }
        $manifestFiles=[];$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($work,FilesystemIterator::SKIP_DOTS));foreach($it as$f)if($f->isFile()&&$f->getFilename()==='plugin.json')$manifestFiles[]=$f->getPathname();
        if(count($manifestFiles)!==1)throw new RuntimeException('Plugin ZIP must contain exactly one plugin.json');
        $manifest=aih_read_json($manifestFiles[0],[]);$id=preg_replace('/[^a-z0-9_-]/','',strtolower((string)($manifest['id']??'')));if($id===''||strlen($id)>64)throw new RuntimeException('Invalid plugin id');
        $root=dirname($manifestFiles[0]);$dest=AIH_PLUGINS.'/'.$id;if(is_file($dest.'/.bundled'))throw new RuntimeException('Bundled plugin IDs cannot be replaced.');if(is_dir($dest))aih_rm_tree($dest);@mkdir($dest,0770,true);
        $copy=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);foreach($copy as$f){$rel=substr($f->getPathname(),strlen($root)+1);$to=$dest.'/'.$rel;if($f->isDir())@mkdir($to,0770,true);else{if(!is_dir(dirname($to)))@mkdir(dirname($to),0770,true);copy($f->getPathname(),$to);}}
        $m=aih_plugin_manifest_from_dir($dest);if(!$m){aih_rm_tree($dest);throw new RuntimeException('Invalid plugin manifest');}
        $s=aih_plugin_state();$s['enabled'][$id]=false;aih_plugin_state_save($s);aih_audit('plugin_installed',['id'=>$id,'file'=>$original]);return ['ok'=>true,'plugin'=>$m];
    } finally { aih_rm_tree($cleanupRoot); }
}

function aih_plugins_system_context(): string {
    $parts=[];foreach(aih_plugins_catalog()as$p){if(empty($p['enabled'])||trim((string)$p['ai_context'])==='')continue;$parts[]='['.$p['name']." add-on]\n".$p['ai_context'];}
    return $parts?"\n\nEnabled add-ons:\n".implode("\n\n",$parts):'';
}

function aih_plugins_quick_actions(): array {
    $out=[];foreach(aih_plugins_catalog()as$p){if(empty($p['enabled']))continue;foreach($p['quick_actions']as$a){if(!is_array($a))continue;$label=substr(trim((string)($a['label']??'')),0,80);$prompt=substr(trim((string)($a['prompt']??'')),0,4000);if($label!==''&&$prompt!=='')$out[]=['plugin'=>$p['id'],'plugin_name'=>$p['name'],'label'=>$label,'prompt'=>$prompt];if(count($out)>=12)break 2;}}return $out;
}

aih_plugins_bootstrap();
