<?php

namespace Flamix\Supervisor\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * php artisan flamix:supervisor
 */
class Supervisor extends Command
{
    protected $signature = 'flamix:supervisor';
    protected $description = 'Check if command running and if not - start!';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $commands = config('queue.supervisor', []);

        if (empty($commands)) {
            return 'Set queue.supervisor if need.';
        }

        foreach ($commands as $cmd => $count) {
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
//        info($msg, $arg); // Debug in console
        dump($msg, $arg);
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
        return 'php ' . base_path('artisan') . " {$cmd} > /dev/null 2>&1 &"; //  > /dev/null 2>&1 & - run in background
    }

    /**
     * Run artisan command.
     *
     * @param string $cmd
     * @return string
     */
    private function artisanRunManually(string $cmd): string
    {
        $this->log('Force run command: ' . $this->getManuallyCmd($cmd));
        return shell_exec($this->getManuallyCmd($cmd)); // Through Process, the standard timeout is triggered
    }

    /**
     * How many times launched?
     *
     * @param string $cmd
     * @return int
     */
    private function howMatchRunManually(string $cmd): int
    {
        $manually_cmd = $this->getManuallyCmd($cmd);
        $running = $this->runProcess('ps aux | grep "' . $cmd . '"');
        $arRunning = explode("\n", $running);

        // How many times we faund?
        for ($i = $cmd_count = 0; count($arRunning) > $i; $i++) {
            if (str_contains($arRunning[$i], $manually_cmd)) {
                $cmd_count++;
            }
        }

        $this->log('[New] Is running ' . $manually_cmd . ': ' . $cmd_count, $arRunning);

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
