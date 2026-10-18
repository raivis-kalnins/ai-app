<?php
declare(strict_types=1);

/** A published default is never an acceptable login on an updated installation. */
function aih_password_hash_usable(string $hash): bool {
    if ($hash === '' || (password_get_info($hash)['algoName'] ?? 'unknown') === 'unknown') return false;
    // SHA-256 fingerprint of the retired, bundled v0.6.8 hash, used ONLY to reject it.
    $retired = 'a509188a325c9771570198b6a240b489b480aaeba55f7bc3bb1cf0530a0d39f4';
    return !hash_equals($retired, hash('sha256', $hash));
}

function aih_auth_verify(string $candidate, array $settings): bool {
    $hash = (string)($settings['password_hash'] ?? '');
    return $candidate !== '' && strlen($candidate) <= 4096 && !str_contains($candidate, "\0")
        && aih_password_hash_usable($hash) && password_verify($candidate, $hash);
}

/** Validate before hashing: bcrypt must not silently truncate a longer input. */
function aih_new_password_error(string $candidate): string {
    if (!preg_match('/\A.{15,}\z/us', $candidate) || strlen($candidate) > 72 || str_contains($candidate, "\0")) {
        return 'Use at least 15 characters, no more than 72 UTF-8 bytes, and no null characters.';
    }
    return '';
}

function aih_auth_mark_logged_in(string $hash, bool $rotateCsrf = true): void {
    if (!aih_password_hash_usable($hash)) throw new RuntimeException('Login is not configured.');
    if (session_status() !== PHP_SESSION_ACTIVE || !session_regenerate_id(true)) {
        throw new RuntimeException('Could not renew the login session.');
    }
    $_SESSION['auth_ok'] = true;
    $_SESSION['auth_password_fp'] = hash('sha256', $hash);
    if ($rotateCsrf || empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/** The source is REMOTE_ADDR, never an untrusted forwarded header. */
function aih_auth_attempt_key(string $scope): string {
    return hash('sha256', $scope . '|' . (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/** Server-side counter: opening a new session/cookie jar cannot reset it. */
function aih_auth_attempt_reserve(string $scope = 'login'): bool {
    $key = aih_auth_attempt_key($scope);
    $allowed = false;
    aih_update_json(AIH_STORAGE . '/auth_attempts.json', function ($rows) use ($key, &$allowed) {
        $rows = is_array($rows) ? $rows : [];
        $now = time();
        foreach ($rows as $id => $row) {
            if (!is_array($row) || (int)($row['until'] ?? 0) <= $now) unset($rows[$id]);
        }
        if (!isset($rows[$key])) {
            // Fail closed rather than evicting active counters under a flood.
            if (count($rows) >= 5000) return $rows;
            $rows[$key] = ['count' => 0, 'until' => $now + 900];
        }
        if ((int)$rows[$key]['count'] < 10) {
            $rows[$key]['count']++;
            $allowed = true;
        }
        return $rows;
    }, []);
    return $allowed;
}

function aih_auth_attempt_clear(string $scope = 'login'): void {
    $key = aih_auth_attempt_key($scope);
    aih_update_json(AIH_STORAGE . '/auth_attempts.json', function ($rows) use ($key) {
        $rows = is_array($rows) ? $rows : [];
        unset($rows[$key]);
        return $rows;
    }, []);
}
