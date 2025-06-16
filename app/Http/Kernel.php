<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        // ...existing middlewares...
        'user.type' => \App\Http\Middleware\CheckUserType::class,
        'check.technician' => \App\Http\Middleware\CheckTechnician::class,
    ];
}