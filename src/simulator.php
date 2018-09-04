<?php
namespace QueueSize;

use QueueSize\Schedule\Item;

class Simulator {

    private $schedule;
    private $queue;
    private $delivered;
    private $purged;
    private $maxAge = 72 * 60 * 60;
    private $workers = [];
    private $workUntilEmpty = true;
    private $maxTicks = 7 * 24 * 60 * 60;

    public function __construct(Schedule $schedule) {
        $this->queue = new Queue();
        $this->delivered = [];
        $this->purged = [];
        $this->schedule = $schedule;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }

    public function setMaxAge($maxAge)
    {
        $this->maxAge = $maxAge;
    }

    private function purge($tick) {
        $purged = $this->queue->purgeExpired($tick, $this->maxAge);
        $this->purged = array_merge($this->purged, $purged);
    }

    public function collectDelivered(Notification $notification) {
        $this->delivered[] = $notification;
    }

    public function simulate() {
        $totalTicks = 0;
        /* \QueueSize\Schedule\Item $scheduleItem */
        foreach ($this->schedule as $scheduleItem) {
            $currentCost = $scheduleItem->getCostOutgoing();
            $currentRate = $scheduleItem->getRateIncoming();
            $action = $scheduleItem->getStatus();
            for ($scheduleItemTicks = $scheduleItem->getTicks(); $scheduleItemTicks > 0; $scheduleItemTicks--) {
                set_time_limit(10);
                ++$totalTicks;
                for ($countIncoming = 0; $countIncoming < $currentRate; $countIncoming++) {
                    $this->queue->enqueue(new Notification($totalTicks));
                }
                /* Worker $worker */
                foreach ($this->workers as $worker) {
                    $worker->tick();
                }
                do {
                    $working = false;
                    foreach ($this->workers as $worker) {
                        $working = $worker->work($currentCost, $totalTicks, $action) || $working;
                    }
                } while ($working === true);
                $this->purge($totalTicks);
            }
            if ($scheduleItem->getIdle() === true) {
                do {
                    $idling = true;
                    foreach ($this->workers as $worker) {
                        $idling = $idling && $worker->idle();
                    }
                } while ($idling === false);
            }
        }
        if ($this->workUntilEmpty) {
            $baseCount = $this->queue->getCount();
            while($this->queue->getCount() > 0 && $this->queue->getCount() <= $baseCount) {
                set_time_limit(9);
                if (++$totalTicks > $this->maxTicks) {
                    break;
                }
                for ($countIncoming = 0; $countIncoming < $currentRate; $countIncoming++) {
                    $this->queue->enqueue(new Notification($totalTicks));
                }
                foreach ($this->workers as $worker) {
                    $worker->tick();
                }
                do {
                    $working = false;
                    foreach ($this->workers as $worker) {
                        $working = $worker->work($currentCost, $totalTicks, Item::STATUS_ONLINE) || $working;
                    }
                } while ($working === true);
                $this->purge($totalTicks);
            }
        }
    }

    public function addWorkers($numWorkers) {
        for ($i = 0; $i < $numWorkers; $i++) {
            $this->workers[] = new Worker($this->queue, $this);
        }
    }

    /**
     * @return bool
     */
    public function isWorkUntilEmpty()
    {
        return $this->workUntilEmpty;
    }

    /**
     * @param bool $workUntilEmpty
     */
    public function setWorkUntilEmpty($workUntilEmpty)
    {
        $this->workUntilEmpty = $workUntilEmpty;
    }

    /**
     * @return array
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * @return array
     */
    public function getPurged()
    {
        return $this->purged;
    }

}
