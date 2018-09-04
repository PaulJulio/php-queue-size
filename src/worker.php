<?php
namespace QueueSize;
use QueueSize\Schedule\Item as Item;

class Worker {

    const STATE_IDLE    = 'idle';
    const STATE_WORKING = 'working';

    /* Queue $queue */
    private $queue;
    /* Simulator $simulator */
    private $simulator;
    private $tickResources = 0;
    private $state         = self::STATE_IDLE;

    public function __construct(Queue $queue, Simulator $simulator) {
        $this->queue = $queue;
        $this->simulator = $simulator;
    }

    public function tick() {
        ++$this->tickResources;
    }

    public function work($cost, $tick, $action) {
        if ($this->state === self::STATE_WORKING) {
            return true;
        }
        if ($this->tickResources - $cost <= 0) {
            $this->state = self::STATE_IDLE;
            return false;
        }
        $this->state = self::STATE_WORKING;
        $notification = $this->queue->dequeue($tick);
        if (!isset($notification)) {
            $this->state = self::STATE_IDLE;
            return false;
        }
        if ($action === Item::STATUS_ONLINE) {
            try {
                $notification->deliver($tick);
                $this->simulator->collectDelivered($notification);
            } catch (\Exception $e) {
                $this->state = self::STATE_IDLE;
                return true;
            }
        } elseif ($action === Item::STATUS_OFFLINE) {
            $notification->retry($tick);
            $this->queue->enqueue($notification);
        } else {
            throw new \Exception('Invalid action: ' . $action);
        }
        $this->tickResources -= $cost;
        $this->state = self::STATE_IDLE;
        return true;
    }

    public function idle() {
        if ($this->state === self::STATE_WORKING) {
            return false;
        }
        $this->tickResources = 0;
        $this->state = self::STATE_IDLE;
        return true;
    }

    public function getState() {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getTickResources()
    {
        return $this->tickResources;
    }

}