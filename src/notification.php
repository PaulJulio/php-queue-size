<?php
namespace QueueSize;

class Notification {

    private $tickCreate = null;
    private $tickDeliver = null;
    private $tickLock = null; // when not null, this message is locked out of consideration until indicated tick is met
    private $retries = 0;
    private $maxRetryTicks = 3600; // when tick is one second, this is a one hour retry period
    private $retryBase = 60; // when tick is one second, this causes retries to be in minutes

    public function __construct($tick) {
        $this->tickCreate = $tick;
    }

    public function isDelivered() {
        return isset($this->tickDeliver);
    }

    public function deliver($tick) {
        if ($this->isDelivered()) {
            throw new \Exception('Attempting to redeliver notification');
        }
        $this->tickDeliver = $tick;
    }

    public function isLocked($tick = null) {
        if (!isset($this->tickLock)) {
            return false;
        }
        if (!isset($tick)) {
            return isset($this->tickLock);
        }
        return $tick <= $this->tickLock;
    }

    public function retry($tick) {
        $lockTime = $this->retryBase * pow(2, $this->retries);
        if ($lockTime > $this->maxRetryTicks) {
            $lockTime = $this->maxRetryTicks;
        }
        ++$this->retries;
        $this->tickLock = $tick + $lockTime;
    }

    public function getTickCreate() {
        return $this->tickCreate;
    }

    public function getTickDeliver() {
        return $this->tickDeliver;
    }

    public function getRetries() {
        return $this->retries;
    }

    public function getTickLock() {
        return $this->tickLock;
    }

    public function getAge($tick) {
        return $tick - $this->getTickCreate();
    }

}