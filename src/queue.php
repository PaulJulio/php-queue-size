<?php
namespace QueueSize;

class Queue {

    private $stack;

    public function __construct() {
        $this->stack = [];
    }

    /**
     * @param Notification $n
     */
    public function enqueue(Notification $n) {
        array_push($this->stack, $n);
    }

    /**
     * @param $tick
     * @return Notification | null
     */
    public function dequeue($tick) {
        if (count($this->stack) < 1) {
            return null;
        }
        $notification = null;
        $foundKey     = null;
        /* Notification $notification */
        foreach ($this->stack as $k => $notification) {
            if (!$notification->isLocked($tick)) {
                $foundKey = $k;
                break;
            }
        }
        if (isset($foundKey)) {
            $notification = array_splice($this->stack, $foundKey, 1)[0];
        }

        return $notification;
    }

    public function purgeExpired($tick, $maxAge) {
        $purged = [];
        $newStack = [];
        /* Notification $notification */
        while (count($this->stack) > 0) {
            $notification = array_shift($this->stack);
            if ($notification->getAge($tick) >= $maxAge) {
                array_push($purged, $notification);
            } else {
                array_push($newStack, $notification);
            }
        }
        $this->stack = $newStack;
        return $purged;
    }

    public function getCount() {
        return count($this->stack);
    }

}
