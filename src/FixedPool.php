<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/11/2
 * Time: 17:45
 */

namespace Jenner\SimpleFork;


class FixedPool extends AbstractPool
{
    /**
     * @var int max process count
     */
    protected $max;

    /**
     * @param int $max
     */
    public function __construct($max = 10)
    {
        $this->max = $max;
    }

    public function execute(Process $process)
    {
        Utils::checkOverwriteRunMethod(get_class($process));
        if ($this->aliveCount() > $this->max) {
            $process->start();
        }
        array_push($this->processes, $process);
    }

    /**
     * wait for all process done
     *
     * @param bool $block block the master process
     * to keep the sub process count all the time
     * @param int $interval check time interval
     */
    public function wait($block = false, $interval = 100)
    {
        do {
            parent::wait(false);
            if ($this->aliveCount() < $this->max) {
                foreach ($this->processes as $process) {
                    if ($process->hasStarted()) continue;
                    $process->start();
                    if ($this->aliveCount() >= $this->max) break;
                }
            }
            $block ? usleep($interval) : null;
        } while ($block);
    }

}