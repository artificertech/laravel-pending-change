<?php

namespace Artificerkal\LaravelPendingChange;

use Illuminate\Support\ServiceProvider;

class PendingChangeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
