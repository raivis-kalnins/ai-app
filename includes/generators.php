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
    if($type==='instant-prototype')return [
        'index.html'=>'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($name).'</title><style>*{box-sizing:border-box}body{margin:0;font:16px/1.55 Inter,system-ui,sans-serif;background:#f5f7fb;color:#172033}.shell{min-height:100vh}.nav{display:flex;justify-content:space-between;align-items:center;padding:20px clamp(20px,5vw,70px);background:#fff;border-bottom:1px solid #e4e9f1}.brand{font-weight:850}.btn{border:0;border-radius:12px;padding:12px 17px;background:#315efb;color:#fff;font-weight:750}.hero{display:grid;grid-template-columns:1.12fr .88fr;gap:44px;align-items:center;max-width:1180px;margin:auto;padding:90px 28px}.eyebrow{color:#315efb;font-weight:800;letter-spacing:.12em;font-size:12px}.hero h1{font-size:clamp(42px,7vw,76px);line-height:1.02;letter-spacing:-.05em;margin:12px 0 20px}.hero p{color:#66758a;font-size:18px;max-width:720px}.mock{background:#fff;border:1px solid #dce4ee;border-radius:24px;box-shadow:0 28px 70px rgba(29,49,82,.12);padding:20px}.mock .bar{height:12px;width:40%;background:#e9eef7;border-radius:99px;margin-bottom:20px}.card{border:1px solid #e4e9f1;border-radius:18px;padding:22px;margin:12px 0;background:#fafcff}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;max-width:1180px;margin:0 auto;padding:0 28px 70px}.grid .card{margin:0}@media(max-width:780px){.hero{grid-template-columns:1fr;padding-top:52px}.grid{grid-template-columns:1fr}}</style></head><body><div class="shell"><nav class="nav"><span class="brand">'.htmlspecialchars($name).'</span><button class="btn" onclick="document.querySelector(\'.mock\').scrollIntoView({behavior:\'smooth\'})">Explore</button></nav><main><section class="hero"><div><span class="eyebrow">INSTANT PROTOTYPE</span><h1>'.htmlspecialchars($name).'</h1><p>'.htmlspecialchars($desc?:'A polished responsive product concept ready for fast iteration.').'</p><button class="btn" onclick="alert(\'Prototype action\')">Try prototype</button></div><div class="mock"><div class="bar"></div><div class="card"><strong>Interactive preview</strong><p>Use this single-file prototype to validate layout, flow and visual direction before production development.</p></div><div class="card"><strong>Fast iteration</strong><p>Edit HTML, CSS and JavaScript directly or ask AI to refine the concept.</p></div></div></section><section class="grid"><div class="card"><strong>Responsive</strong><p>Desktop, tablet and mobile-friendly.</p></div><div class="card"><strong>Accessible</strong><p>Simple semantic structure and clear controls.</p></div><div class="card"><strong>Portable</strong><p>Single HTML file with no build step.</p></div></section></main></div></body></html>',
        'README.md'=>"# $name\n\nInstant single-file HTML/CSS/JS prototype with live preview in Free AI Hub 63.\n",
    ];
    if($type==='figma-prototype')return [
        'index.html'=>'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($name).'</title><style>:root{--ink:#111827;--muted:#6b7280;--accent:#7c3aed;--bg:#f7f7fb}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.5 Inter,system-ui,sans-serif}.frame{max-width:1240px;margin:auto;padding:24px}.toolbar{display:flex;gap:9px;align-items:center;margin-bottom:18px}.dot{width:10px;height:10px;border-radius:50%;background:#d1d5db}.canvas{background:white;border:1px solid #e5e7eb;border-radius:26px;box-shadow:0 28px 70px rgba(17,24,39,.10);overflow:hidden}.top{display:flex;justify-content:space-between;align-items:center;padding:22px 26px;border-bottom:1px solid #eee}.top b{font-size:18px}.top button,.cta{border:0;border-radius:12px;background:var(--accent);color:#fff;padding:11px 16px;font-weight:750}.content{display:grid;grid-template-columns:1.05fr .95fr;gap:34px;padding:56px 40px}.content h1{font-size:clamp(38px,6vw,68px);letter-spacing:-.05em;line-height:1.02;margin:0 0 18px}.content p{color:var(--muted);font-size:17px}.preview{background:linear-gradient(145deg,#ede9fe,#eef2ff);border-radius:22px;padding:22px;min-height:360px;display:grid;place-items:center}.device{width:min(310px,82%);background:#fff;border-radius:24px;padding:18px;box-shadow:0 20px 55px rgba(124,58,237,.18)}.device div{height:58px;border-radius:14px;background:#f3f4f6;margin:10px 0}@media(max-width:760px){.content{grid-template-columns:1fr;padding:34px 22px}}</style></head><body><div class="frame"><div class="toolbar"><i class="dot"></i><i class="dot"></i><i class="dot"></i><span>Figma-inspired prototype preview</span></div><div class="canvas"><header class="top"><b>'.htmlspecialchars($name).'</b><button>Prototype</button></header><section class="content"><div><h1>From design idea to clickable prototype.</h1><p>'.htmlspecialchars($desc?:'Use Figma screenshots, design notes or component specs as input, then iterate quickly in a responsive browser preview.').'</p><button class="cta" onclick="alert(\'Prototype interaction\')">Open flow</button></div><div class="preview"><div class="device"><strong>Design system</strong><div></div><div></div><div></div></div></div></section></div></div></body></html>',
        'README.md'=>"# $name\n\nFigma-style single-file responsive prototype starter. Paste Figma notes/screenshots into AI Chat with the Figma Prototype Studio add-on enabled to refine it.\n",
    ];
    if($type==='music-app-prototype')return [
        'index.html'=>'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($name).'</title><style>*{box-sizing:border-box}body{margin:0;background:#0d1020;color:#f7f8ff;font:15px/1.5 Inter,system-ui,sans-serif}.app{min-height:100vh;display:grid;grid-template-columns:220px 1fr}.side{padding:24px;background:#12172a;border-right:1px solid #252c45}.side h2{margin-top:0}.side button{width:100%;margin:6px 0;padding:11px;border:0;border-radius:10px;background:#1c2440;color:#dfe6ff;text-align:left}.main{padding:28px}.top{display:flex;justify-content:space-between;gap:16px;align-items:center}.transport{display:flex;gap:9px}.transport button{width:44px;height:44px;border-radius:50%;border:0;background:#6d5cff;color:#fff;font-size:18px}.studio{display:grid;grid-template-columns:1.4fr .6fr;gap:18px;margin-top:22px}.card{background:#151b31;border:1px solid #29314f;border-radius:18px;padding:18px}.staff{height:230px;border-radius:14px;background:repeating-linear-gradient(to bottom,#fff0 0,#fff0 31px,#7c86a5 32px,#fff0 33px);position:relative}.note{position:absolute;width:17px;height:12px;background:#a99cff;border-radius:50%;transform:rotate(-12deg)}.note:after{content:"";position:absolute;width:2px;height:42px;background:#a99cff;right:0;bottom:5px}.padgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:9px}.pad{aspect-ratio:1;border-radius:12px;border:1px solid #36405f;background:#202945;cursor:pointer}.pad:active{background:#6d5cff}@media(max-width:760px){.app{grid-template-columns:1fr}.side{display:none}.studio{grid-template-columns:1fr}.main{padding:18px}}</style></head><body><div class="app"><aside class="side"><h2>'.htmlspecialchars($name).'</h2><button>Score</button><button>Tracks</button><button>Chords</button><button>Export</button></aside><main class="main"><div class="top"><div><small>MUSIC PROTOTYPE</small><h1>'.htmlspecialchars($name).'</h1><p>'.htmlspecialchars($desc?:'A fast browser prototype for composing, arranging, playback and music-learning experiences.').'</p></div><div class="transport"><button onclick="alert(\'Play demo\')">▶</button><button>■</button></div></div><div class="studio"><section class="card"><h3>Score canvas</h3><div class="staff"><i class="note" style="left:18%;top:93px"></i><i class="note" style="left:35%;top:126px"></i><i class="note" style="left:54%;top:77px"></i><i class="note" style="left:72%;top:110px"></i></div></section><section class="card"><h3>Pads</h3><div class="padgrid">'.str_repeat('<button class="pad" onclick="this.style.background=\'#6d5cff\'"></button>',12).'</div></section></div></main></div></body></html>',
        'README.md'=>"# $name\n\nMusic/audio web-app prototype with responsive studio UI. Use the Music Studio add-on for score, chords, MIDI/MusicXML and Web Audio planning.\n",
    ];
    if($type==='react-ts')return [
        'package.json'=>json_encode(['scripts'=>['dev'=>'vite','build'=>'tsc -b && vite build','preview'=>'vite preview'],'dependencies'=>['react'=>'latest','react-dom'=>'latest'],'devDependencies'=>['@vitejs/plugin-react'=>'latest','vite'=>'latest','typescript'=>'latest','@types/react'=>'latest','@types/react-dom'=>'latest']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
        'index.html'=>'<div id="root"></div><script type="module" src="/src/main.tsx"></script>',
        'src/main.tsx'=>"import React from 'react';\nimport {createRoot} from 'react-dom/client';\nimport './style.css';\nimport App from './App';\ncreateRoot(document.getElementById('root')!).render(<App/>);\n",
        'src/App.tsx'=>"export default function App(){return <main><h1>".addslashes($name)."</h1><p>".addslashes($desc)."</p></main>}\n",
        'src/style.css'=>'body{font-family:Inter,system-ui,sans-serif;margin:0;background:#f7f9fc;color:#172033}main{max-width:960px;margin:auto;padding:64px 24px}',
        'vite.config.ts'=>"import {defineConfig} from 'vite';import react from '@vitejs/plugin-react';export default defineConfig({plugins:[react()]});",
        'README.md'=>"# $name\n\nReact + TypeScript + Vite starter.\n\n`npm install && npm run dev`\n",
    ];
    if($type==='next-supabase')return [
        'package.json'=>json_encode(['scripts'=>['dev'=>'next dev','build'=>'next build','start'=>'next start','lint'=>'next lint'],'dependencies'=>['next'=>'latest','react'=>'latest','react-dom'=>'latest','@supabase/supabase-js'=>'latest'],'devDependencies'=>['typescript'=>'latest','@types/node'=>'latest','@types/react'=>'latest','@types/react-dom'=>'latest']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
        'app/layout.tsx'=>"import './globals.css';export const metadata={title:'".addslashes($name)."'};export default function RootLayout({children}:{children:React.ReactNode}){return <html lang=\"en\"><body>{children}</body></html>}",
        'app/page.tsx'=>"export default function Page(){return <main><span>Next.js + Node.js + Supabase</span><h1>".addslashes($name)."</h1><p>".addslashes($desc)."</p><a href=\"/api/health\">API health</a></main>}",
        'app/api/health/route.ts'=>"export async function GET(){return Response.json({ok:true,stack:'Next.js + Node.js route handlers + Supabase'})}",
        'app/globals.css'=>'*{box-sizing:border-box}body{margin:0;font:16px/1.5 system-ui;background:#f8fafc;color:#172033}main{max-width:900px;margin:auto;padding:72px 24px}a{color:#2563eb}',
        'lib/supabase.ts'=>"import {createClient} from '@supabase/supabase-js';export const supabase=createClient(process.env.NEXT_PUBLIC_SUPABASE_URL||'',process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY||'');",
        '.env.example'=>"NEXT_PUBLIC_SUPABASE_URL=\nNEXT_PUBLIC_SUPABASE_ANON_KEY=\n",
        'README.md'=>"# $name\n\nFree-first app stack: Next.js + React + TypeScript + Node.js route handlers + Supabase.\n\n1. `npm install`\n2. Copy `.env.example` to `.env.local` and add your Supabase project values.\n3. `npm run dev`\n",
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

    if($type==='figma-bootstrap-gutenberg'){
        $themeJson=json_encode([
            'version'=>3,
            'settings'=>[
                'layout'=>['contentSize'=>'1240px','wideSize'=>'1440px'],
                'color'=>['palette'=>[
                    ['slug'=>'primary','color'=>'#315efb','name'=>'Primary'],
                    ['slug'=>'ink','color'=>'#172033','name'=>'Ink'],
                    ['slug'=>'surface','color'=>'#f6f8fb','name'=>'Surface'],
                ]],
                'typography'=>['fluid'=>true],
                'spacing'=>['units'=>['px','rem','em','%','vw','vh']],
            ],
            'styles'=>['spacing'=>['blockGap'=>'1.5rem']],
        ],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $front='<!-- wp:template-part {"slug":"header"} /--><!-- wp:pattern {"slug":"'.$safe.'/hero"} /--><!-- wp:group {"className":"container aih-section","layout":{"type":"constrained"}} --><div class="wp-block-group container aih-section"><!-- wp:post-content /--></div><!-- /wp:group --><!-- wp:template-part {"slug":"footer"} /-->';
        $footer='<!-- wp:group {"className":"bg-dark text-white mt-5","layout":{"type":"constrained"}} --><div class="wp-block-group bg-dark text-white mt-5"><!-- wp:paragraph {"className":"container py-5"} --><p class="container py-5">'.htmlspecialchars($name,ENT_QUOTES).' - Bootstrap Gutenberg theme</p><!-- /wp:paragraph --></div><!-- /wp:group -->';
        $hero="<?php /** Title: Figma Hero / Slug: $safe/hero / Categories: aih-builder */ ?>\n".
            '<!-- wp:group {"className":"aih-section bg-light","layout":{"type":"constrained"}} --><div class="wp-block-group aih-section bg-light"><!-- wp:columns {"className":"container align-items-center"} --><div class="wp-block-columns container align-items-center"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":1} --><h1 class="wp-block-heading">'.htmlspecialchars($name,ENT_QUOTES).'</h1><!-- /wp:heading --><!-- wp:paragraph --><p>'.htmlspecialchars($desc?:'Built from a connected Figma design with Bootstrap 5 and native Gutenberg blocks.',ENT_QUOTES).'</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Get started</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:group {"className":"ratio ratio-4x3 rounded-4 bg-white shadow-sm"} --><div class="wp-block-group ratio ratio-4x3 rounded-4 bg-white shadow-sm"></div><!-- /wp:group --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->';
        return [
            "$safe/style.css"=>"/*\nTheme Name: $name\nDescription: $desc\nVersion: 1.0.0\nRequires at least: 6.5\n*/\n:root{--aih-primary:#315efb;--aih-dark:#172033}body{font-family:system-ui,-apple-system,Segoe UI,sans-serif}.wp-site-blocks{overflow:hidden}.aih-section{padding:clamp(3rem,6vw,6rem) 0}",
            "$safe/theme.json"=>$themeJson,
            "$safe/functions.php"=>"<?php\nif(!defined('ABSPATH'))exit;\nadd_action('wp_enqueue_scripts',function(){wp_enqueue_style('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',[],'5.3.3');wp_enqueue_style('$safe',get_stylesheet_uri(),['bootstrap'],'1.0.0');wp_enqueue_script('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',[], '5.3.3',true);});\nadd_action('init',function(){register_block_pattern_category('aih-builder',['label'=>__('AIH Builder','$safe')]);});\n",
            "$safe/templates/index.html"=>'<!-- wp:template-part {"slug":"header"} /--><!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} --><main class="wp-block-group"><!-- wp:query {"query":{"perPage":10,"postType":"post"}} --><div class="wp-block-query"><!-- wp:post-template --><!-- wp:post-title {"isLink":true} /--><!-- wp:post-excerpt /--><!-- /wp:post-template --></div><!-- /wp:query --></main><!-- /wp:group --><!-- wp:template-part {"slug":"footer"} /-->',
            "$safe/templates/front-page.html"=>$front,
            "$safe/parts/header.html"=>'<!-- wp:group {"className":"border-bottom bg-white","layout":{"type":"constrained"}} --><div class="wp-block-group border-bottom bg-white"><!-- wp:group {"className":"container py-3 d-flex justify-content-between align-items-center","layout":{"type":"flex","justifyContent":"space-between"}} --><div class="wp-block-group container py-3 d-flex justify-content-between align-items-center"><!-- wp:site-title /--><!-- wp:navigation /--></div><!-- /wp:group --></div><!-- /wp:group -->',
            "$safe/parts/footer.html"=>$footer,
            "$safe/patterns/hero.php"=>$hero,
            "$safe/README.md"=>"# $name\n\nBootstrap 5 + native Gutenberg block theme scaffold. Connect a Figma file in Free AI Hub and rebuild with AI enabled to map pages, frames, tokens and layout into theme templates and block patterns.\n\n## Builder approach\n- Native Gutenberg blocks and patterns (portable, no lock-in)\n- Bootstrap 5 utilities/components\n- theme.json design tokens\n- Figma source metadata included in AI build context\n",
        ];
    }

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

function aih_package_project_files(string $type,string $name,array $files,string $provider='starter'): array {
    if(!$files)return ['ok'=>false,'error'=>'No project files were generated.'];
    $slug=trim((string)preg_replace('/[^a-z0-9]+/','-',strtolower($name)),'-')?:'project';$fn=aih_new_id($type).'-'.$slug.'.zip';$path=AIH_GENERATED.'/'.$fn;
    if(!aih_zip_files($path,$files))return ['ok'=>false,'error'=>'ZIP creation failed. Enable ZipArchive or Phar support.'];
    $previewFile='';
    if(in_array($type,['instant-prototype','figma-prototype','music-app-prototype'],true)){
        $html='';if(isset($files['index.html']))$html=(string)$files['index.html'];else foreach($files as$k=>$v)if(str_ends_with(strtolower((string)$k),'.html')){$html=(string)$v;break;}
        if($html!==''){$previewFile=aih_new_id('preview').'-'.$slug.'.html';file_put_contents(AIH_GENERATED.'/'.$previewFile,$html);}
    }
    return ['ok'=>true,'file'=>$fn,'provider'=>$provider,'files'=>array_keys($files),'preview_file'=>$previewFile];
}

function aih_package_project_text(array $in): array {
    $type=(string)($in['type']??'react-ts');$name=trim((string)($in['name']??'My Project'))?:'My Project';$text=(string)($in['ai_text']??'');
    if(strlen($text)>2_000_000)return ['ok'=>false,'error'=>'Generated project response is too large.'];
    $files=aih_parse_ai_files($text);if(!$files)return ['ok'=>false,'error'=>'Could not parse generated project files.'];
    return aih_package_project_files($type,$name,$files,(string)($in['provider']??'Browser AI'));
}

function aih_generate_project(array $in): array {
    $type=(string)($in['type']??'react-ts');$name=trim((string)($in['name']??'My Project'))?:'My Project';$desc=trim((string)($in['description']??''));$useAi=!empty($in['use_ai']);$files=[];$provider='starter';$figmaContext='';if($type==='figma-bootstrap-gutenberg'){require_once __DIR__.'/figma.php';$fk=trim((string)($in['figma_file_key']??$in['figma_file_url']??''));if($fk!==''){try{$fs=aih_figma_file_summary(aih_settings(),$fk);$figmaContext=' Figma source: '.json_encode(['key'=>$fs['key'],'name'=>$fs['name'],'pages'=>$fs['pages']],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);$desc=trim($desc.$figmaContext);}catch(Throwable $e){$desc=trim($desc.' Figma import warning: '.$e->getMessage());}}}
    if($useAi){$previewType=in_array($type,['instant-prototype','figma-prototype','music-app-prototype'],true);$extra=$previewType?" The project MUST include a polished, responsive, self-contained index.html with all CSS and JavaScript inline so it can run immediately in a sandboxed live preview. Make the prototype visually strong, usable on mobile, and include realistic interactions without external credentials.":"";$prompt="Create a complete $type project named $name. Requirements: $desc.$extra Output ONLY files using markers exactly like: ### FILE: path/to/file.ext followed by file content. Include README. Keep dependencies minimal, current, secure and performance-focused.";$res=aih_ai_chat([['role'=>'system','content'=>'You are a senior production engineer. Generate secure, maintainable project files.'],['role'=>'user','content'=>$prompt]],null,'code');if(!empty($res['ok'])){$files=aih_parse_ai_files((string)$res['text']);$provider=(string)$res['provider_name'];}}
    if(!$files)$files=aih_starter_files($type,$name,$desc);
    return aih_package_project_files($type,$name,$files,$provider);
}
