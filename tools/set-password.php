<?php
declare(strict_types=1);

// This tool is deliberately not a web-accessible password-reset endpoint.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$flags = array_slice($argv, 1);
$allowed = ['--stdin', '--hash-only', '--help'];
foreach ($flags as $flag) {
    if (!in_array($flag, $allowed, true)) {
        fwrite(STDERR, "Unknown option. Never pass a password as a command-line argument. Use --help.\n");
        exit(2);
    }
}
if (in_array('--help', $flags, true)) {
    echo "Usage: php tools/set-password.php [--hash-only] [--stdin]\n\n";
    echo "Default: hidden prompts, then set/reset this installation's login.\n";
    echo "--hash-only: print only a new hash; do not read/change application storage.\n";
    echo "--stdin: read two newline-terminated copies from standard input (automation).\n";
    echo "Use 15+ characters, maximum 72 UTF-8 bytes. Never put a password in shell history.\n";
    exit;
}

function aih_cli_read_hidden(string $prompt): string {
    if (!function_exists('stream_isatty') || !stream_isatty(STDIN)) {
        throw new RuntimeException('A terminal is required. Use --stdin only with a secure input source.');
    }
    if (!function_exists('shell_exec')) {
        throw new RuntimeException('Hidden terminal input is unavailable. Use --stdin with a secure input source.');
    }
    if (PHP_OS_FAMILY === 'Windows') {
        // Read-Host masks input. Base64 is only an internal Unicode transport, not storage.
        $program = '$s=Read-Host ' . "'" . str_replace("'", "''", $prompt) . "'" . ' -AsSecureString;' .
            '$p=[Runtime.InteropServices.Marshal]::SecureStringToBSTR($s);' .
            'try{$v=[Runtime.InteropServices.Marshal]::PtrToStringBSTR($p);' .
            '[Console]::Write([Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($v)))}' .
            'finally{[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($p)}';
        if (!function_exists('iconv')) throw new RuntimeException('Windows hidden input requires the PHP iconv extension.');
        $encoded = base64_encode(iconv('UTF-8', 'UTF-16LE', $program));
        $result = shell_exec('powershell.exe -NoProfile -EncodedCommand ' . $encoded);
        $plain = is_string($result) ? base64_decode(trim($result), true) : false;
        if (!is_string($plain)) throw new RuntimeException('Could not read hidden input from PowerShell.');
        return $plain;
    }
    $state = trim((string)shell_exec('stty -g 2>/dev/null'));
    if ($state === '' || !preg_match('/^[a-fA-F0-9:;= -]+$/', $state)) {
        throw new RuntimeException('Could not secure terminal input. Use --stdin with a secure input source.');
    }
    // Restore the terminal even if PHP exits unexpectedly.
    register_shutdown_function(static function () use ($state): void {
        shell_exec('stty ' . escapeshellarg($state) . ' 2>/dev/null');
    });
    fwrite(STDERR, $prompt . ': ');
    shell_exec('stty -echo 2>/dev/null');
    try {
        $line = fgets(STDIN);
        if ($line === false) throw new RuntimeException('Input cancelled.');
        return rtrim($line, "\r\n");
    } finally {
        shell_exec('stty ' . escapeshellarg($state) . ' 2>/dev/null');
        fwrite(STDERR, "\n");
    }
}

try {
    require_once dirname(__DIR__) . '/includes/auth.php';
    if (in_array('--stdin', $flags, true)) {
        $a = fgets(STDIN, 8194);
        $b = fgets(STDIN, 8194);
        if ($a === false || $b === false) throw new RuntimeException('Provide the new value twice on separate lines.');
        $new = rtrim($a, "\r\n");
        $confirm = rtrim($b, "\r\n");
    } else {
        $new = aih_cli_read_hidden('New password');
        $confirm = aih_cli_read_hidden('Confirm new password');
    }
    $error = aih_new_password_error($new);
    if ($error !== '') throw new RuntimeException($error);
    if (!hash_equals($new, $confirm)) throw new RuntimeException('The entries do not match.');
    $hash = password_hash($new, PASSWORD_DEFAULT);
    unset($new, $confirm, $a, $b);
    if (in_array('--hash-only', $flags, true)) {
        fwrite(STDOUT, $hash . PHP_EOL);
        exit;
    }
    // Keep provider keys, uploads, chat history and all unrelated settings intact.
    define('AIH_SKIP_AUTO_CLEANUP', true);
    require dirname(__DIR__) . '/includes/bootstrap.php';
    aih_update_json(AIH_STORAGE . '/settings.json', static function ($settings) use ($hash) {
        if (!is_array($settings) || $settings === []) {
            throw new RuntimeException('Invalid settings. Restore your backup before resetting the login.');
        }
        $settings['password_hash'] = $hash;
        return aih_migrate_settings($settings);
    }, []);
    aih_remember_revoke_all();
    aih_write_json(AIH_STORAGE . '/auth_attempts.json', []);
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
    fwrite(STDOUT, "Login updated. Other sessions and remembered logins are no longer valid.\n");
} catch (Throwable $error) {
    fwrite(STDERR, 'Error: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
