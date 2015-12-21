<?php

namespace SymfonyContrib\Bundle\CronBundle;

use Doctrine\Orm\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use SymfonyContrib\Bundle\CronBundle\Entity\Cron;
use SymfonyContrib\Bundle\CronBundle\Entity\Repository\CronRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Executes application cron tasks according to configuration.
 */
class CronExecuter extends ContainerAware
{
    /** @var EntityManager */
    protected $em;

    /** @var CronRepository */
    protected $repo;

    /** @var OutputInterface */
    protected $commandOutput;

    /** @var  Logger */
    protected $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the Doctrine entity manager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em = $this->em ?: $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Get the Cron entity repository.
     *
     * @return CronRepository
     */
    public function getRepo()
    {
        return $this->repo = $this->repo ?: $this->getEntityManager()->getRepository('CronBundle:Cron');
    }

    /**
     * Execute scheduled cron entries that are due.
     */
    public function runDue()
    {
        $due = $this->getRepo()->findDue();

        foreach ($due as $cron) {
            $this->runCron($cron);
        }
    }

    /**
     * Execute all known cron entries.
     *
     * @param bool $includeDisabled
     */
    public function runAll($includeDisabled = false)
    {
        $repo = $this->getRepo();
        $all  = $includeDisabled ? $repo->findAll() : $repo->findAllEnabled();

        foreach ($all as $cron) {
            $this->runCron($cron);
        }
    }

    /**
     * Execute a cron by name.
     *
     * @param $name
     */
    public function runByName($name)
    {
        $cron = $this->getRepo()->findOneBy(['name' => $name]);
        $this->runCron($cron);
    }

    /**
     * Execute a cron task.
     *
     * @param Cron $cron
     *
     * @throws \Exception
     */
    public function runCron(Cron $cron)
    {
        $em = $this->getEntityManager();

        $this->outputLine('<comment>' . $cron->getName() . ' is  running</comment>');

        $cron->setStatus('running');
        $em->flush($cron);

        $timeStart = microtime(true);
        try {
            $job     = $cron->getJob();
            $service = substr($job, 0, strpos($job, ':'));
            $method  = substr($job, strpos($job, ':') + 1);

            $service = $this->container->get($service);

            if ($service && $method) {
                $return = $service->{$method}($cron);
            } else {
                throw new \Exception('Missing service or method.');
            }

            $cron->setStatus('completed');
        } catch (\Exception $e) {
            $cron->setStatus('failed');
            print $e->getMessage();
            $this->logger->error('Cron failed: ' . $e->getMessage());
        }

        $timeEnd = microtime(true);
        $cron->setDurationLast(round(($timeEnd - $timeStart) * 1000));
        $em->flush($cron);

        $color = $cron->getStatus() === 'completed' ? 'info' : 'error';
        $this->outputLine("<$color>" . $cron->getName() . ' has ' . $cron->getStatus() . "</$color>");
    }

    /**
     * @param OutputInterface $commandOutput
     *
     * @return CronExecuter
     */
    public function setCommandOutput($commandOutput)
    {
        $this->commandOutput = $commandOutput;

        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getCommandOutput()
    {
        return $this->commandOutput;
    }

    public function outputLine($line)
    {
        if (!$this->commandOutput) {
            return;
        }

        $this->commandOutput->writeln($line);
    }
}
