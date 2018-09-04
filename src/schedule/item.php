<?php
namespace QueueSize\Schedule;

class Item {

    const STATUS_ONLINE = 'online';
    const STATUS_OFFLINE = 'offline';

    const STATUS_ALL = [self::STATUS_ONLINE, self::STATUS_OFFLINE];

    private $status;
    private $ticks;
    private $rateIncoming;
    private $costOutgoing;
    private $idle;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @throws \Exception
     */
    public function setStatus($status)
    {
        if (!in_array($status, self::STATUS_ALL)) {
            throw new \Exception('Invalid status: ' . $status);
        }
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getTicks()
    {
        return $this->ticks;
    }

    /**
     * @param mixed $ticks
     */
    public function setTicks($ticks)
    {
        $this->ticks = $ticks;
    }

    /**
     * @return mixed
     */
    public function getRateIncoming()
    {
        return $this->rateIncoming;
    }

    /**
     * @param mixed $rateIncoming
     */
    public function setRateIncoming($rateIncoming)
    {
        $this->rateIncoming = $rateIncoming;
    }

    /**
     * @return mixed
     */
    public function getCostOutgoing()
    {
        return $this->costOutgoing;
    }

    /**
     * @param mixed $costOutgoing
     */
    public function setCostOutgoing($costOutgoing)
    {
        $this->costOutgoing = $costOutgoing;
    }

    /**
     * @return bool
     */
    public function getIdle()
    {
        return $this->idle;
    }

    /**
     * @param bool $idle
     */
    public function setIdle($idle)
    {
        $this->idle = $idle;
    }

}