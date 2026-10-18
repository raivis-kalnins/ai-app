<?php
return [
    'app' => [
        'name' => 'Free AI Hub 63',
        'timezone' => 'Europe/London',
        'session_name' => 'free_ai_hub_session',
        'max_upload_bytes' => 1073741824, // 1 GiB
        'chat_retention_seconds' => 2592000, // 30 days
        'file_retention_seconds' => 172800,   // 48 hours for uploads, generated files and reports
        'cache_retention_seconds' => 86400,   // 24 hours
    ],
    'security' => [
        // No shared login is shipped. Set it with: php tools/set-password.php
        // An optional server-only AIH_INITIAL_PASSWORD_HASH seeds a NEW install.
        'initial_password_hash' => getenv('AIH_INITIAL_PASSWORD_HASH') ?: '',
    ],
];
