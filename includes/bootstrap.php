<?php
declare(strict_types=1);

const AIH_VERSION = '0.1.0';
const AIH_ROOT = __DIR__ . '/..';
const AIH_STORAGE = AIH_ROOT . '/storage';
const AIH_UPLOADS = AIH_STORAGE . '/uploads';
const AIH_GENERATED = AIH_STORAGE . '/generated';
const AIH_REPORTS = AIH_STORAGE . '/reports';
const AIH_CACHE = AIH_STORAGE . '/cache';
const AIH_LOGS = AIH_STORAGE . '/logs';

function aih_config(): array {
    static $cfg = null;
    if (is_array($cfg)) return $cfg;
    $file = AIH_ROOT . '/config.local.php';
    if (!is_file($file)) $file = AIH_ROOT . '/config.example.php';
    $cfg = require $file;
    date_default_timezone_set((string)($cfg['app']['timezone'] ?? 'Europe/London'));
    return $cfg;
}

function aih_path(string $bucket, string $name=''): string {
    $map = [
        'storage'=>AIH_STORAGE, 'uploads'=>AIH_UPLOADS, 'generated'=>AIH_GENERATED,
        'reports'=>AIH_REPORTS, 'cache'=>AIH_CACHE, 'logs'=>AIH_LOGS,
    ];
    if (!isset($map[$bucket])) throw new InvalidArgumentException('Unknown storage bucket');
    $base = $map[$bucket];
    return $name === '' ? $base : $base . DIRECTORY_SEPARATOR . $name;
}

function aih_bootstrap_storage(): void {
    foreach ([AIH_STORAGE,AIH_UPLOADS,AIH_GENERATED,AIH_REPORTS,AIH_CACHE,AIH_LOGS] as $d) {
        if (!is_dir($d)) @mkdir($d, 0770, true);
    }
    $deny = "Require all denied\nDeny from all\n";
    if (!is_file(AIH_STORAGE.'/.htaccess')) @file_put_contents(AIH_STORAGE.'/.htaccess', $deny);
    if (!is_file(AIH_STORAGE.'/settings.json')) {
        $cfg = aih_config();
        aih_write_json(AIH_STORAGE.'/settings.json', [
            'password_hash' => (string)($cfg['security']['initial_password_hash'] ?? ''),
            'providers' => [
                'order' => ['ollama_local','groq','gemini','openrouter','ollama_cloud','pollinations','custom_openai'],
                'enabled' => [
                    'ollama_local'=>true,'groq'=>true,'gemini'=>true,'openrouter'=>true,
                    'ollama_cloud'=>false,'pollinations'=>false,'custom_openai'=>false,
                ],
                'keys' => ['groq'=>'','gemini'=>'','openrouter'=>'','ollama_cloud'=>'','pollinations'=>'','custom_openai'=>''],
                'models' => [
                    'ollama_local'=>'qwen3:8b',
                    'groq'=>'openai/gpt-oss-20b',
                    'gemini'=>'gemini-3.8-flash',
                    'openrouter'=>'openrouter/free',
                    'ollama_cloud'=>'gpt-oss:20b-cloud',
                    'pollinations'=>'openai-fast',
                    'custom_openai'=>'',
                ],
                'ollama_local_url'=>'http://127.0.0.1:11434/v1/chat/completions',
                'custom_openai_url'=>'',
            ],
            'search' => [
                'searxng_url'=>'',
                'ollama_web_key'=>'',
                'max_results'=>40,
                'ai_enrich'=>true,
            ],
            'images' => [
                'provider'=>'pollinations',
                'pollinations_model'=>'flux',
                'sdwebui_url'=>'http://127.0.0.1:7860',
            ],
            'system_prompt' => 'You are a careful assistant for web development, WordPress, app development and structured research. Never invent source facts. When source data is missing, say it is missing.',
            'created_at' => date(DATE_ATOM),
        ]);
    }
    if (!is_file(AIH_STORAGE.'/provider_state.json')) aih_write_json(AIH_STORAGE.'/provider_state.json', []);
}

function aih_read_json(string $path, mixed $default=[]): mixed {
    if (!is_file($path)) return $default;
    $h = @fopen($path,'rb'); if (!$h) return $default;
    try { @flock($h, LOCK_SH); $raw = stream_get_contents($h); @flock($h, LOCK_UN); }
    finally { fclose($h); }
    $v = json_decode((string)$raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $v : $default;
}

function aih_write_json(string $path, mixed $value): void {
    $dir = dirname($path); if (!is_dir($dir)) @mkdir($dir,0770,true);
    $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
    file_put_contents($tmp, json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL, LOCK_EX);
    @chmod($tmp,0660);
    rename($tmp,$path);
}

function aih_update_json(string $path, callable $fn, mixed $default=[]): mixed {
    $lock = @fopen($path.'.lock','c+');
    if (!$lock) throw new RuntimeException('Storage lock failed');
    try {
        flock($lock,LOCK_EX);
        $v = $fn(aih_read_json($path,$default));
        aih_write_json($path,$v);
        flock($lock,LOCK_UN);
        return $v;
    } finally { fclose($lock); }
}

function aih_settings(): array {
    $v = aih_read_json(AIH_STORAGE.'/settings.json', []);
    return is_array($v) ? $v : [];
}
function aih_save_settings(array $v): void { aih_write_json(AIH_STORAGE.'/settings.json', $v); }

function aih_start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $cfg = aih_config();
    $secure = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    session_name((string)($cfg['app']['session_name'] ?? 'free_ai_hub_session'));
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);
    session_start();
}

function aih_headers(bool $html=true): void {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if ($html) header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}

function aih_e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function aih_is_logged_in(): bool { return !empty($_SESSION['auth_ok']); }
function aih_require_login(): void { if (!aih_is_logged_in()) { http_response_code(401); exit('Unauthorized'); } }
function aih_csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return (string)$_SESSION['csrf']; }
function aih_check_csrf(?string $token): void { if (!$token || !hash_equals(aih_csrf(),$token)) { http_response_code(403); exit('Invalid CSRF token'); } }
function aih_json(array $data, int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); exit; }
function aih_json_input(): array { $raw=file_get_contents('php://input'); $v=json_decode((string)$raw,true); return is_array($v)?$v:[]; }

function aih_safe_name(string $name): string {
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9._-]+/','_', $name) ?: 'file';
    return substr($name,0,180);
}
function aih_new_id(string $prefix='f'): string { return $prefix.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(5)); }

function aih_cleanup_expired(bool $force=false): array {
    $cfg = aih_config();
    $retention = max(3600,(int)($cfg['app']['retention_seconds'] ?? 172800));
    $stamp = AIH_STORAGE.'/last_cleanup.txt';
    if (!$force && is_file($stamp) && (time()-(int)@file_get_contents($stamp)) < 900) return ['deleted'=>0,'bytes'=>0,'skipped'=>true];
    @file_put_contents($stamp,(string)time(),LOCK_EX);
    $deleted=0; $bytes=0; $cut=time()-$retention;
    foreach ([AIH_UPLOADS,AIH_GENERATED,AIH_REPORTS,AIH_CACHE] as $dir) {
        foreach (glob($dir.'/*') ?: [] as $path) {
            if (!is_file($path)) continue;
            $mt=@filemtime($path) ?: time();
            if ($mt < $cut) { $sz=@filesize($path) ?: 0; if (@unlink($path)) { $deleted++; $bytes+=$sz; } }
        }
    }
    return ['deleted'=>$deleted,'bytes'=>$bytes,'skipped'=>false];
}

function aih_storage_stats(): array {
    $out=['files'=>0,'bytes'=>0,'by_bucket'=>[]];
    foreach (['uploads'=>AIH_UPLOADS,'generated'=>AIH_GENERATED,'reports'=>AIH_REPORTS,'cache'=>AIH_CACHE] as $k=>$dir) {
        $n=0;$b=0;
        foreach (glob($dir.'/*') ?: [] as $f) if (is_file($f)) {$n++;$b+=(int)(filesize($f)?:0);} 
        $out['by_bucket'][$k]=['files'=>$n,'bytes'=>$b]; $out['files']+=$n; $out['bytes']+=$b;
    }
    return $out;
}

function aih_audit(string $event, array $context=[]): void {
    $row=['at'=>date(DATE_ATOM),'event'=>$event,'ip'=>substr((string)($_SERVER['REMOTE_ADDR']??''),0,64),'context'=>$context];
    $path=AIH_LOGS.'/audit.json';
    aih_update_json($path,function($rows)use($row){$rows=is_array($rows)?$rows:[];$rows[]=$row;return array_slice($rows,-500);},[]);
}

aih_bootstrap_storage();
aih_start_session();
aih_cleanup_expired(false);
