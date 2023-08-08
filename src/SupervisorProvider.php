<?php

namespace Flamix\Supervisor;

use Illuminate\Support\ServiceProvider;

class SupervisorProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Flamix\Supervisor\Console\Commands\Supervisor::class,
            ]);
        }
    }
}
