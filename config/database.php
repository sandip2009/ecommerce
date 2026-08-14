<?php

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_DATABASE') ?: 'ecommerce',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
];