<?php
return [
    'app' => [
        'name' => 'Free AI Hub 63',
        'timezone' => 'Europe/London',
        'session_name' => 'free_ai_hub_session',
        'max_upload_bytes' => 1073741824, // 1 GiB
        'retention_seconds' => 172800,    // 48 hours
    ],
    'security' => [
        // bcrypt hash for the initial password requested by the owner.
        // Change it from Settings after first login.
        'initial_password_hash' => '$2y$12$aPwgyzVoajFvqaKfSnm2IO6bUedvb9.kCNtVT/UQYzu1GmMBsGW3e',
    ],
];
