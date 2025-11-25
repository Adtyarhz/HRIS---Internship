<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\HandleCors as Middleware;

class HandleCors extends Middleware
{
    protected array $allowedOrigins = [
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ];

    protected array $allowedOriginsPatterns = [];

    protected array $allowedMethods = ['*'];

    protected array $allowedHeaders = ['*'];

    protected array $exposedHeaders = [];

    protected bool $supportsCredentials = true;

    protected int $maxAge = 0;
}
