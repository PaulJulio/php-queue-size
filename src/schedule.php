<?php
namespace QueueSize;
use \QueueSize\Schedule\Item as Item;

class Schedule implements \Iterator {
    private $position;
    private $elements;

    public function __construct()
    {
        $this->position = 0;
        $this->elements = [];
    }

    public function current()
    {
        return $this->elements[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->elements[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function addScheduleItem(Item $item) {
        array_push($this->elements, $item);
    }

}
