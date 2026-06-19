<?php

return [
    \app\admin\middleware\CorsMiddleware::class,
    \app\admin\middleware\InstallMiddleware::class,
    \app\admin\middleware\AuthMiddleware::class,
];
