<?php

namespace SymfonyContrib\Bundle\CronBundle\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Cron\CronExpression;

/**
 * Repeatable task.
 */
class Cron
{
    const GROUP_DEFAULT = 'default';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $job;

    /**
     * @var string
     */
    protected $desc;

    /**
     * @var string
     */
    protected $runInterval;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var int
     */
    protected $weight;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $durationLast;

    /**
     * @var int
     */
    protected $durationMax;

    /**
     * @var int
     */
    protected $durationAvg;

    /**
     * @var int
     */
    protected $runCount;

    /**
     * @var \DateTime
     */
    protected $lastRan;

    /**
     * @var \DateTime
     */
    protected $nextRun;

    /**
     * @var string
     */
    protected $owner;


    public function __construct()
    {
        $this->desc         = '';
        $this->group        = self::GROUP_DEFAULT;
        $this->weight       = 0;
        $this->enabled      = true;
        $this->status       = '';
        $this->durationMax  = 0;
        $this->durationAvg  = 0;
        $this->durationLast = 0;
        $this->runCount     = 0;
        $this->owner        = 'SymfonyContrib:CronBundle';
    }

    /**
     * Doctrine lifecycle callback.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->setNextRun($this->calcNextRun());
    }

    /**
     * Doctrine lifecycle callback.
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        // Update data when on run.
        if ($args->hasChangedField('status') && $this->getStatus() === 'running') {
            $this->setLastRan(new \DateTime());
            $this->setNextRun($this->calcNextRun());
            $this->setRunCount($this->runCount + 1);
        } else if ($args->hasChangedField('lastRan') || $args->hasChangedField('runInterval')) {
            // Set next run when interval or last run has changed.
            $this->setNextRun($this->calcNextRun());
        }

        // Update duration fields when durationLast is changed.
        if ($args->hasChangedField('durationLast')) {
            $this->updateDurations();
        }
    }

    public function calcNextRun()
    {
        $cron = CronExpression::factory($this->runInterval);
        return $cron->getNextRunDate();
    }

    public function updateDurations()
    {
        $last = $this->getDurationLast();
        $avg  = $this->getDurationAvg();
        // Update average duration.
        $this->setDurationAvg($last == 0 ? $last : round((($avg + $last) / 2)));
        // Update max duration is longer than current.
        if ($last > $this->durationMax) {
            $this->setDurationMax($last);
        }
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @param int $durationAvg
     */
    public function setDurationAvg($durationAvg)
    {
        $this->durationAvg = $durationAvg;
    }

    /**
     * @return int
     */
    public function getDurationAvg()
    {
        return $this->durationAvg;
    }

    /**
     * @param int $durationLast
     */
    public function setDurationLast($durationLast)
    {
        $this->durationLast = $durationLast;
    }

    /**
     * @return int
     */
    public function getDurationLast()
    {
        return $this->durationLast;
    }

    /**
     * @param int $durationMax
     */
    public function setDurationMax($durationMax)
    {
        $this->durationMax = $durationMax;
    }

    /**
     * @return int
     */
    public function getDurationMax()
    {
        return $this->durationMax;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $runInterval
     */
    public function setRunInterval($runInterval)
    {
        $this->runInterval = $runInterval;
    }

    /**
     * @return string
     */
    public function getRunInterval()
    {
        return $this->runInterval;
    }

    /**
     * @param \DateTime $lastRan
     */
    public function setLastRan(\DateTime $lastRan)
    {
        $this->lastRan = $lastRan;
    }

    /**
     * @return \DateTime
     */
    public function getLastRan()
    {
        return $this->lastRan;
    }

    /**
     * @param string $job
     */
    public function setJob($job)
    {
        $this->job = $job;
    }

    /**
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param int $runCount
     */
    public function setRunCount($runCount)
    {
        $this->runCount = $runCount;
    }

    /**
     * @return int
     */
    public function getRunCount()
    {
        return $this->runCount;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group ?: self::GROUP_DEFAULT;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param \DateTime $nextRun
     */
    public function setNextRun(\DateTime $nextRun)
    {
        $this->nextRun = $nextRun;
    }

    /**
     * @return \DateTime
     */
    public function getNextRun()
    {
        return $this->nextRun;
    }

}
