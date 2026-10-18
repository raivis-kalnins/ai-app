<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Pure helper tests only: never bootstrap or change the real installation's storage.
require dirname(__DIR__) . '/includes/auth.php';
require dirname(__DIR__) . '/includes/i18n.php';
$checks = 0;
function check(bool $condition, string $label): void {
    global $checks;
    if (!$condition) { fwrite(STDERR, "FAIL: $label\n"); exit(1); }
    $checks++;
}
$value = bin2hex(random_bytes(16));
$hash = password_hash($value, PASSWORD_DEFAULT);
check(aih_password_hash_usable($hash), 'Unique hash accepted');
check(!aih_password_hash_usable(''), 'Blank hash rejected');
check(!aih_password_hash_usable('not-a-hash'), 'Malformed hash rejected');
check(aih_auth_verify($value, ['password_hash' => $hash]), 'Correct input accepted');
check(!aih_auth_verify(bin2hex(random_bytes(16)), ['password_hash' => $hash]), 'Wrong input rejected');
check(!aih_auth_verify('', ['password_hash' => $hash]), 'Empty input rejected');
check(!aih_auth_verify($value . "\0", ['password_hash' => $hash]), 'Null input rejected');
check(!aih_auth_verify(str_repeat('x', 4097), ['password_hash' => $hash]), 'Oversized input rejected');
check(aih_new_password_error($value) === '', 'Valid new input accepted');
check(aih_new_password_error(str_repeat('x', 14)) !== '', 'Short new input rejected');
check(aih_new_password_error(str_repeat('x', 73)) !== '', 'Bcrypt truncation avoided');
check(aih_new_password_error(str_repeat("\xc4\x81", 14)) !== '', 'Unicode character minimum');
check(aih_new_password_error(str_repeat("\xc4\x81", 15)) === '', 'Unicode passphrase accepted');
check(aih_new_password_error(str_repeat("\xc4\x81", 37)) !== '', 'Unicode byte maximum');
foreach (array_keys(aih_languages()) as $language) {
    foreach (['ui_login_label','ui_change_login_label','ui_reveal_login_label','ui_conceal_login_label','ui_current_login_label','ui_confirm_login_label','ui_login_change_help','ui_login_setup_notice'] as $key) {
        check(aih_t($key, $language) !== $key && aih_t($key, $language) !== '', "Translation $language/$key");
    }
}
foreach (glob(dirname(__DIR__) . '/plugins/*/plugin.json') ?: [] as $file) {
    check(is_array(json_decode((string)file_get_contents($file), true)), 'Plugin manifest ' . basename(dirname($file)));
}
echo "$checks checks passed. No runtime settings were read or changed.\n";
