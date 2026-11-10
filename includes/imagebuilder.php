<?php
declare(strict_types=1);

const IB_STYLES = [
  'none'         => ['', ''],
  'photo'        => ['ultra realistic photo, natural light, sharp focus, high detail', 'cartoon, painting, deformed'],
  'cinematic'    => ['cinematic still, dramatic lighting, shallow depth of field, film grain', ''],
  'illustration' => ['digital illustration, clean lines, vibrant colors, highly detailed', ''],
  'anime'        => ['anime style, detailed, vibrant colors, studio quality', ''],
  '3d'           => ['3d render, soft studio lighting, high detail, smooth materials', ''],
  'logo'         => ['minimal vector logo, flat design, simple shapes, white background', 'photo, gradient noise'],
  'card'         => ['warm bokeh light, cozy, elegant composition, soft golden tones', 'text, watermark'],
];
// [w,h] sync providers (SDXL/Forge) / [w,h] AI Horde (reizinājumi ar 64, anonīmi mazāki)
const IB_SIZES = [
  '1:1'  => [[1024,1024],[512,512]],
  '2:3'  => [[832,1216],[512,768]],
  '3:2'  => [[1216,832],[768,512]],
  '16:9' => [[1344,768],[768,448]],
  '9:16' => [[768,1344],[448,768]],
];
const IB_NEG = 'blurry, low quality, watermark, text, extra fingers, deformed, ugly';

function ib_dir(): string {
  $d = dirname(__DIR__) . '/storage/generated';
  if (!is_dir($d)) mkdir($d, 0775, true);
  return $d;
}
// Atslēgas: env vai konstantes config.local.php (pielāgo savam settings.json, ja vajag)
function ib_cfg(string $k): string {
  $v = getenv($k);
  if ($v) return (string)$v;
  return defined($k) ? (string)constant($k) : '';
}
function ib_http(string $url, string $method = 'GET', array $headers = [], ?string $body = null, int $timeout = 90): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
    CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
  ]);
  if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
  $res = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$code, $res === false ? '' : (string)$res];
}
function ib_save(string $bin): ?string {
  $info = @getimagesizefromstring($bin);
  if (!$info) return null;
  $ext = ['image/png'=>'png','image/webp'=>'webp','image/jpeg'=>'jpg'][$info['mime']] ?? null;
  if (!$ext) return null;
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  file_put_contents(ib_dir() . '/' . $name, $bin);
  return $name;
}

/* ---------- Providers ---------- */
function ib_forge(array $p, int $seed): ?string {
  $u = rtrim(ib_cfg('FORGE_URL'), '/');
  if (!$u) return null;
  [$c, $r] = ib_http("$u/sdapi/v1/txt2img", 'POST', ['Content-Type: application/json'], json_encode([
    'prompt' => $p['prompt'], 'negative_prompt' => $p['neg'], 'width' => $p['w'], 'height' => $p['h'],
    'steps' => 28, 'cfg_scale' => 7, 'seed' => $seed,
  ]), 300);
  $j = json_decode($r, true);
  return ($c === 200 && !empty($j['images'][0])) ? ib_save(base64_decode($j['images'][0])) : null;
}
function ib_cloudflare(array $p, int $seed): ?string {
  $acc = ib_cfg('CF_ACCOUNT_ID'); $tok = ib_cfg('CF_API_TOKEN');
  if (!$acc || !$tok) return null;
  [$c, $r] = ib_http("https://api.cloudflare.com/client/v4/accounts/$acc/ai/run/@cf/bytedance/stable-diffusion-xl-lightning",
    'POST', ["Authorization: Bearer $tok", 'Content-Type: application/json'],
    json_encode(['prompt' => $p['prompt'], 'negative_prompt' => $p['neg'], 'width' => $p['w'], 'height' => $p['h'], 'num_steps' => 8, 'seed' => $seed]));
  return $c === 200 ? ib_save($r) : null;
}
function ib_pollinations(array $p, int $seed): ?string {
  $q = http_build_query(['width' => $p['w'], 'height' => $p['h'], 'seed' => $seed, 'model' => 'flux', 'nologo' => 'true']);
  $key = ib_cfg('POLLINATIONS_KEY');
  [$c, $r] = ib_http('https://image.pollinations.ai/prompt/' . rawurlencode($p['prompt']) . '?' . $q, 'GET',
    $key ? ["Authorization: Bearer $key"] : [], null, 120);
  return $c === 200 ? ib_save($r) : null;
}
function ib_horde_submit(array $p, int $seed, int $n): ?string {
  $key = ib_cfg('HORDE_KEY') ?: '0000000000';
  [$c, $r] = ib_http('https://aihorde.net/api/v2/generate/async', 'POST',
    ["apikey: $key", 'Client-Agent: free-ai-hub:0.8:raivis', 'Content-Type: application/json'],
    json_encode([
      'prompt' => $p['prompt'] . ' ### ' . $p['neg'],
      'params' => ['sampler_name' => 'k_euler_a', 'cfg_scale' => 7, 'width' => $p['hw'], 'height' => $p['hh'],
                   'steps' => 25, 'n' => $n, 'seed' => (string)$seed, 'karras' => true],
      'nsfw' => false, 'censor_nsfw' => true, 'r2' => true, 'slow_workers' => true,
    ]));
  $j = json_decode($r, true);
  return in_array($c, [200, 202], true) ? ($j['id'] ?? null) : null;
}
function ib_horde_status(string $id): array {
  if (!preg_match('/^[a-f0-9\-]{20,64}$/i', $id)) return ['status' => 'error', 'error' => 'Bad job id'];
  [$c, $r] = ib_http("https://aihorde.net/api/v2/generate/check/$id", 'GET', ['Client-Agent: free-ai-hub:0.8:raivis'], null, 20);
  $j = json_decode($r, true);
  if ($c !== 200 || !$j) return ['status' => 'error', 'error' => 'Horde check failed'];
  if (empty($j['done'])) return ['status' => 'queued', 'queue' => $j['queue_position'] ?? 0, 'wait' => $j['wait_time'] ?? 0];
  [$c, $r] = ib_http("https://aihorde.net/api/v2/generate/status/$id", 'GET', ['Client-Agent: free-ai-hub:0.8:raivis'], null, 30);
  $j = json_decode($r, true);
  $files = [];
  foreach (($j['generations'] ?? []) as $g) {
    if (!empty($g['censored']) || empty($g['img'])) continue;
    if (!preg_match('#^https://#', $g['img'])) continue;
    [$cc, $bin] = ib_http($g['img'], 'GET', [], null, 60);
    if ($cc === 200 && ($f = ib_save($bin))) $files[] = $f;
  }
  return $files ? ['status' => 'done', 'files' => $files] : ['status' => 'error', 'error' => 'Horde returned no image (censored/failed)'];
}

/* ---------- Main ---------- */
function ib_generate(array $in): array {
  $raw   = trim((string)($in['prompt'] ?? ''));
  if ($raw === '' || mb_strlen($raw) > 1500) return ['status' => 'error', 'error' => 'Prompt is empty or too long'];
  $style = isset(IB_STYLES[$in['style'] ?? '']) ? $in['style'] : 'none';
  $asp   = isset(IB_SIZES[$in['aspect'] ?? '']) ? $in['aspect'] : '1:1';
  $n     = max(1, min(4, (int)($in['n'] ?? 1)));
  $seed  = (isset($in['seed']) && $in['seed'] !== '') ? (int)$in['seed'] : random_int(1, 2147483000);
  $prov  = (string)($in['provider'] ?? 'auto');
  $neg   = trim(IB_NEG . ', ' . IB_STYLES[$style][1] . ', ' . (string)($in['negative'] ?? ''), ' ,');
  [[$w, $h], [$hw, $hh]] = IB_SIZES[$asp];
  $p = ['prompt' => trim($raw . ', ' . IB_STYLES[$style][0], ' ,'), 'neg' => $neg, 'w' => $w, 'h' => $h, 'hw' => $hw, 'hh' => $hh];

  $order = $prov === 'auto' ? ['forge', 'cloudflare', 'pollinations', 'horde'] : [$prov];
  $tried = [];
  foreach ($order as $o) {
    if ($o === 'horde') {
      $job = ib_horde_submit($p, $seed, $n);
      if ($job) return ['status' => 'queued', 'job' => $job, 'provider' => 'horde', 'seed' => $seed];
      $tried[] = 'horde'; continue;
    }
    $fn = ['forge' => 'ib_forge', 'cloudflare' => 'ib_cloudflare', 'pollinations' => 'ib_pollinations'][$o] ?? null;
    if (!$fn) continue;
    $files = [];
    for ($i = 0; $i < $n; $i++) { if ($f = $fn($p, $seed + $i)) $files[] = $f; else break; }
    if ($files) return ['status' => 'done', 'files' => $files, 'provider' => $o, 'seed' => $seed];
    $tried[] = $o;
  }
  return ['status' => 'error', 'error' => 'All providers failed: ' . implode(', ', $tried)];
}

function ib_serve(string $f): void {
  if (!preg_match('/^[a-f0-9]{16}\.(png|webp|jpg)$/', $f, $m)) { http_response_code(404); exit; }
  $path = ib_dir() . '/' . $f;
  if (!is_file($path)) { http_response_code(404); exit; }
  header('Content-Type: ' . ['png' => 'image/png', 'webp' => 'image/webp', 'jpg' => 'image/jpeg'][$m[1]]);
  header('Cache-Control: private, max-age=3600');
  header('X-Content-Type-Options: nosniff');
  readfile($path); exit;
}
function ib_json(array $d): void {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($d, JSON_UNESCAPED_UNICODE); exit;
}