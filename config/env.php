<?php
return [
    'DB_HOST' => getenv('DB_HOST') ?: '127.0.0.1',
    'DB_NAME' => getenv('DB_NAME') ?: 'gctu_library',
    'DB_USER' => getenv('DB_USER') ?: 'root',
    'DB_PASS' => getenv('DB_PASS') ?: '',
    'MAIL_ENABLED' => getenv('MAIL_ENABLED') === '1',
    'MAIL_HOST' => getenv('MAIL_HOST') ?: '',
    'MAIL_PORT' => (int)(getenv('MAIL_PORT') ?: 587),
    'MAIL_USERNAME' => getenv('MAIL_USERNAME') ?: '',
    'MAIL_PASSWORD' => getenv('MAIL_PASSWORD') ?: '',
    'MAIL_ENCRYPTION' => getenv('MAIL_ENCRYPTION') ?: 'tls',
    'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS') ?: '',
    'MAIL_FROM_NAME' => getenv('MAIL_FROM_NAME') ?: 'GCTU Project Library',
];
