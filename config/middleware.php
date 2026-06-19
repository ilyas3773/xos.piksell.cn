<?php

return [
    'alias' => [],
    'priority' => [],
    'global' => [
        \app\middleware\CorsMiddleware::class,
        \app\index\middleware\InstallMiddleware::class,
    ],
];
