<?php

namespace Nip\Profiler;

/**
 * Class Profile
 * @package Nip\Profiler
 */
class Profile
{
    public $id = null;

    public $name = null;

    public $columns = ['type', 'time', 'memory'];

    /**
     * @var null|int
     */
    protected $startedMicrotime = null;

    /**
     * @var null|int
     */
    protected $endedMicrotime = null;

    /**
     * @var null|int
     */
    protected $startedMemory = null;

    /**
     * @var null
     */
    protected $endedMemory = null;

    /**
     * @var null|int
     */
    protected $time = null;

    /**
     * @var null|int
     */
    protected $memory = null;

    /**
     * Profile constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->setId($id);
        $this->name = $id;
        $this->start();
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->{$name};
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function start()
    {
        $this->startedMicrotime = microtime(true);
        $this->startedMemory = memory_get_usage();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    public function end()
    {
        // Ensure that the query profile has not already ended
        if ($this->hasEnded()) {
            return;
        }
        $this->endTimers();
        $this->calculateResources();
    }

    public function endTimers()
    {
        $this->endedMicrotime = microtime(true);
        $this->endedMemory = memory_get_usage();
    }

    public function calculateResources()
    {
        $this->time = $this->calculateElapsedSecs();
        $this->memory = $this->calculateUsedMemory();
    }

    /**
     * @return bool
     */
    public function hasEnded()
    {
        return $this->endedMicrotime !== null;
    }

    /**
     * @return int|null
     */
    public function getStartMicrotime()
    {
        return $this->startedMicrotime;
    }

    /**
     * @return int|null
     */
    public function getEndMicrotime()
    {
        return $this->endedMicrotime;
    }

    /**
     * @return bool|int|null
     */
    public function calculateElapsedSecs()
    {
        if (null === $this->endedMicrotime) {
            return false;
        }

        return $this->endedMicrotime - $this->startedMicrotime;
    }

    /**
     * @return int|null
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return bool|string
     */
    public function calculateUsedMemory()
    {
        if (null === $this->endedMemory) {
            return false;
        }

        return number_format(($this->endedMemory - $this->startedMemory) / 1024).' KB';
    }

    /**
     * @return null|int
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    protected function setId($id)
    {
        $this->id = $id;
    }
}
