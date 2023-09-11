<?php

namespace Flamix\Supervisor\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * php artisan flamix:supervisor
 * php artisan flamix:supervisor --count
 */
class Supervisor extends Command
{
    protected $signature = 'flamix:supervisor {--count}';
    protected $description = 'Check if command running and if not - start!';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $show_count = $this->option('count');
        $commands = config('queue.supervisor', []);

        if (empty($commands)) {
            return 'Set queue.supervisor if need.';
        }

        foreach ($commands as $cmd => $count) {
            // Counting...
            if ($show_count) {
                $this->log("Command: {$cmd}. Count: {$this->howMatchRunManually($cmd, false)}, needed: {$count}");
                continue;
            }

            // Running...
            if ($this->howMatchRunManually($cmd) < $count) {
                $this->artisanRunManually($cmd);
            }
        }
    }

    /**
     * Logging.
     *
     * @param string $msg
     * @param array $arg
     * @return void
     */
    private function log(string $msg, array $arg = [])
    {
        // info($msg, $arg); // Debug in console
        if (!empty($arg)) {
            dump($msg, $arg);
        } else {
            dump($msg);
        }
    }

    /**
     * Full command.
     *
     * queue:listen
     * Will returned php /var/www/apps/data/www/small.app.flamix.solutions/artisan queue:listen
     *
     * @param string $cmd
     * @return string
     */
    private function getManuallyCmd(string $cmd): string
    {
        return 'php ' . base_path('artisan') . ' ' . $cmd;
    }

    /**
     * Run artisan command.
     *
     * @param string $cmd
     * @return null|string
     */
    private function artisanRunManually(string $cmd): ?string
    {
        $full_cmd = $this->getManuallyCmd($cmd) . ' > /dev/null 2>&1 &'; // > /dev/null 2>&1 & - run in background
        $this->log("Force run command: {$full_cmd}");
        return shell_exec($full_cmd);
    }

    /**
     * How many times launched?
     *
     * @param string $cmd
     * @param bool $log
     * @return int
     */
    private function howMatchRunManually(string $cmd, bool $log = true): int
    {
        $manually_cmd = $this->getManuallyCmd($cmd);
        $running = $this->runProcess('ps aux | grep "' . $cmd . '"');
        $arRunning = explode("\n", $running);

        // How many times we found?
        for ($i = $cmd_count = 0; count($arRunning) > $i; $i++) {
            if (str_contains($arRunning[$i], $manually_cmd)) {
                $cmd_count++;
            }
        }

        if ($log) {
            $this->log("[New] Is running {$manually_cmd}: {$cmd_count}", $arRunning);
        }

        return $cmd_count;
    }

    /**
     * Run proccess as a user.
     *
     * @param string $cmd
     * @return string
     */
    private function runProcess(string $cmd)
    {
        $process = Process::fromShellCommandline($cmd);
        $process->run();
        return $process->getOutput();
    }
}
