<?php

namespace Flamix\Supervisor\Console\Commands;

use Illuminate\Console\Command;

/**
 * php artisan flamix:supervisor
 */
class Supervisor extends Command
{
    private array $commands = [];

    protected $signature = 'flamix:supervisor';
    protected $description = 'Check if command running and if not - start!';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        dump('Commands:', $this->commands);
    }
}
