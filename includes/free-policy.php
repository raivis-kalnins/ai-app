<?php
declare(strict_types=1);

// This cPanel edition never calls metered inference, even with saved API keys.
// Keep this server-side allowlist independent of request/settings input.
function aih_free_provider_allowed(string $id): bool {
    return in_array($id, ['ollama_local','lmstudio_local','llamacpp_local','localai_local',
        'webllm_browser','aihorde_text','sdwebui','comfyui','aihorde','free_web'], true);
}
function aih_free_settings(array $s): array {
    foreach (($s['providers']['enabled'] ?? []) as $id => $enabled) {
        if (!aih_free_provider_allowed((string)$id)) $s['providers']['enabled'][$id] = false;
    }
    // Loopback on shared hosting is the host, not the owner's PC. Preserve
    // explicitly configured external local-model servers and all saved secrets.
    foreach (['ollama_local','lmstudio_local','llamacpp_local','localai_local'] as $id) {
        $host = parse_url((string)($s['providers'][$id.'_url'] ?? ''), PHP_URL_HOST);
        if (in_array($host, ['localhost','127.0.0.1','[::1]'], true)) $s['providers']['enabled'][$id] = false;
    }
    foreach (['sdwebui_url','comfyui_url'] as $key) {
        $host = parse_url((string)($s['images'][$key] ?? ''), PHP_URL_HOST);
        if (in_array($host, ['localhost','127.0.0.1','[::1]'], true)) $s['images'][$key] = '';
    }
    if (!aih_free_provider_allowed((string)($s['images']['provider'] ?? 'auto'))) $s['images']['provider'] = 'auto';
    $s['images']['speed_mode'] = 'quality';
    // Optional paid search credentials are not used by this edition.
    $s['search']['brave_search_key'] = '';
    $s['search']['ollama_web_key'] = '';
    return $s;
}
function aih_free_blocked(): array {
    return ['ok'=>false, 'code'=>403, 'error'=>'Blocked by the no-paid-API cPanel policy. Use AI Horde, Browser AI or an explicitly configured self-hosted model.', 'headers'=>[]];
}
