<?php
declare(strict_types=1);

function aih_zip_files(string $zipPath,array $files): bool {
    @unlink($zipPath);
    if(class_exists('ZipArchive')){
        $z=new ZipArchive();if($z->open($zipPath,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true)return false;foreach($files as$path=>$content)$z->addFromString($path,$content);$z->close();return true;
    }
    if(class_exists('PharData')){
        try{$z=new PharData($zipPath);foreach($files as$path=>$content)$z[$path]=$content;unset($z);return is_file($zipPath);}catch(Throwable $e){@unlink($zipPath);return false;}
    }
    return false;
}

function aih_starter_files(string $type,string $name,string $desc): array {
    $safe=preg_replace('/[^a-z0-9-]+/','-',strtolower($name));$safe=trim((string)$safe,'-')?:'app';
    if($type==='react-ts')return [
        'package.json'=>json_encode(['scripts'=>['dev'=>'vite','build'=>'tsc -b && vite build'],'dependencies'=>['@vitejs/plugin-react'=>'latest','vite'=>'latest','typescript'=>'latest','react'=>'latest','react-dom'=>'latest'],'devDependencies'=>[]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
        'index.html'=>'<div id="root"></div><script type="module" src="/src/main.tsx"></script>',
        'src/main.tsx'=>"import React from 'react';\nimport {createRoot} from 'react-dom/client';\nimport './style.css';\nimport App from './App';\ncreateRoot(document.getElementById('root')!).render(<App/>);\n",
        'src/App.tsx'=>"export default function App(){return <main><h1>".addslashes($name)."</h1><p>".addslashes($desc)."</p></main>}\n",
        'src/style.css'=>'body{font-family:Inter,system-ui,sans-serif;margin:0;background:#0b1020;color:#eef2ff}main{max-width:960px;margin:auto;padding:64px 24px}',
        'README.md'=>"# $name\n\nReact + TypeScript + Vite starter.\n\n`npm install && npm run dev`\n",
    ];
    if($type==='svelte')return [
        'package.json'=>json_encode(['scripts'=>['dev'=>'vite','build'=>'vite build'],'devDependencies'=>['@sveltejs/vite-plugin-svelte'=>'latest','svelte'=>'latest','vite'=>'latest']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
        'index.html'=>'<div id="app"></div><script type="module" src="/src/main.js"></script>',
        'src/main.js'=>"import {mount} from 'svelte';import App from './App.svelte';mount(App,{target:document.getElementById('app')});",
        'src/App.svelte'=>"<script>let ready=true;</script>\n<main><h1>$name</h1><p>$desc</p>{#if ready}<small>Svelte starter ready.</small>{/if}</main>\n<style>main{max-width:960px;margin:auto;padding:64px 24px;font-family:system-ui}</style>",
        'vite.config.js'=>"import {defineConfig} from 'vite';import {svelte} from '@sveltejs/vite-plugin-svelte';export default defineConfig({plugins:[svelte()]});",
        'README.md'=>"# $name\n\nSvelte + Vite starter.\n",
    ];
    if($type==='alpine-ajax')return [
        'index.html'=>'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($name).'</title><script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script><style>body{font:16px system-ui;max-width:900px;margin:40px auto;padding:20px}</style></head><body><main x-data="app()"><h1>'.htmlspecialchars($name).'</h1><p>'.htmlspecialchars($desc).'</p><button @click="load">AJAX demo</button><pre x-text="result"></pre></main><script>function app(){return{result:\'\',async load(){this.result=await fetch(\'api.php\').then(r=>r.text())}}}</script></body></html>',
        'api.php'=>"<?php header('Content-Type: text/plain'); echo 'AJAX response: '.date(DATE_ATOM);",
        'README.md'=>"# $name\n\nAlpine.js + AJAX/fetch starter with no build step.\n",
    ];
    if($type==='wordpress-plugin'){
        $php="<?php\n/**\n * Plugin Name: $name\n * Description: $desc\n * Version: 1.0.0\n */\nif(!defined('ABSPATH'))exit;\nadd_action('wp_enqueue_scripts',function(){ wp_enqueue_script('$safe',plugins_url('assets/app.js',__FILE__),[], '1.0.0',true); });\n";
        return ["$safe/$safe.php"=>$php,"$safe/assets/app.js"=>"document.documentElement.dataset['$safe']='ready';\n","$safe/readme.txt"=>"=== $name ===\n$desc\n"];
    }
    if($type==='wordpress-theme')return [
        "$safe/style.css"=>"/*\nTheme Name: $name\nVersion: 1.0.0\n*/\nbody{font-family:system-ui;margin:0}",
        "$safe/functions.php"=>"<?php add_action('wp_enqueue_scripts',function(){wp_enqueue_style('$safe',get_stylesheet_uri(),[],'1.0.0');});",
        "$safe/index.php"=>"<?php get_header(); ?><main><?php if(have_posts()):while(have_posts()):the_post(); ?><article><h1><?php the_title(); ?></h1><?php the_content(); ?></article><?php endwhile;endif; ?></main><?php get_footer(); ?>",
        "$safe/header.php"=>'<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo(\'charset\'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>>',
        "$safe/footer.php"=>'<?php wp_footer(); ?></body></html>',
    ];
    return ['index.php'=>"<?php?><!doctype html><html><body><h1>".htmlspecialchars($name)."</h1><p>".htmlspecialchars($desc)."</p></body></html>"];
}

function aih_parse_ai_files(string $text): array {
    $text=preg_replace('/^```.*?\n|```$/ms','',$text);if(!preg_match_all('/###\s*FILE:\s*([^\r\n]+)\R(.*?)(?=\R###\s*FILE:|\z)/s',$text,$m,PREG_SET_ORDER))return [];$out=[];
    foreach($m as$x){$fn=trim($x[1]);$fn=str_replace(['..','\\'],'/', $fn);$fn=ltrim($fn,'/');if($fn!==''&&strlen($fn)<180)$out[$fn]=rtrim($x[2]);}return $out;
}

function aih_generate_project(array $in): array {
    $type=(string)($in['type']??'react-ts');$name=trim((string)($in['name']??'My Project'))?:'My Project';$desc=trim((string)($in['description']??''));$useAi=!empty($in['use_ai']);$files=[];$provider='starter';
    if($useAi){$prompt="Create a complete $type project named $name. Requirements: $desc. Output ONLY files using markers exactly like: ### FILE: path/to/file.ext followed by file content. Include README. Keep dependencies minimal and performance-focused.";$res=aih_ai_chat([['role'=>'system','content'=>'You are a senior production engineer. Generate secure, maintainable project files.'],['role'=>'user','content'=>$prompt]]);if(!empty($res['ok'])){$files=aih_parse_ai_files((string)$res['text']);$provider=(string)$res['provider_name'];}}
    if(!$files)$files=aih_starter_files($type,$name,$desc);$slug=trim((string)preg_replace('/[^a-z0-9]+/','-',strtolower($name)),'-')?:'project';$fn=aih_new_id($type).'-'.$slug.'.zip';$path=AIH_GENERATED.'/'.$fn;if(!aih_zip_files($path,$files))return ['ok'=>false,'error'=>'ZIP creation failed. Enable ZipArchive or Phar support.'];
    return ['ok'=>true,'file'=>$fn,'provider'=>$provider,'files'=>array_keys($files)];
}
