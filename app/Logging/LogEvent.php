<?php

namespace App\Logging;

use Log;

/**
 * Class LogEvent
 * @package App\Logging
 */
class LogEvent
{
    public $action = '';
    public $parent = null;
    public $startTime = null;
    /** @var LogEvent[]  */
    public $children = [];
    public $runTime = null;

    public function __construct(string $action, ?LogEvent $parent = null, float $startTime = null)
    {
        $this->action = $action;
        $this->parent = $parent;
        $this->startTime = $startTime ?? microtime(true);
        if ($parent !== null) {
            $parent->addChildren($this);
        }
    }

    public function save(): void
    {
        $this->runTime = (float) number_format(microtime(true) - $this->startTime - $this->childrenRunTime(), 5);
    }

    public function addChildren(LogEvent $logEvent): void
    {
        $this->children[] = $logEvent;
    }

    public function childrenRunTime(): float
    {
        return array_reduce($this->children, function ($carry, $row) {
            return $carry + $row->runTime();
        }, 0);
    }

    public function runTime(): float
    {
        return ($this->runTime ?? 0) + $this->childrenRunTime();
    }

    public function getActionName(): string
    {
        if ($this->action === 'root') {
            return $this->action;
        }
        return $this->parent->getActionName() . '->' . $this->action;
    }

    public function log(string $uuid): void
    {
        if (!empty($this->runTime)) {
            Log::info('part run time', [
                'uuid' => $uuid,
                'action' => $this->getActionName(),
                'run_time' => $this->runTime,
            ]);
        }

        foreach ($this->children as $child) {
            $child->log($uuid);
        }
    }
}
