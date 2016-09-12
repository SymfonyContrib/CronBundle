<?php

namespace SymfonyContrib\Bundle\CronBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to run cron tasks.
 */
class CronRunCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('cron:run')
            ->setDescription('Run cron jobs. Default is to only run jobs that are due.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of a specific cron job to run.')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Run all known enabled cron jobs.')
            ->addOption('include-disabled', null, InputOption::VALUE_NONE, 'Include disabled cron jobs.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cronExecutor = $this->getContainer()->get('cron.executor');
        $cronExecutor->setCommandOutput($output);
        $response = '';

        if ($name = $input->getArgument('name')) {
            $cronExecutor->runByName($name);
        } elseif ($input->getOption('all')) {
            $cronExecutor->runAll($input->getOption('include-disabled'));
        } else {
            $cronExecutor->runDue();
        }

        $output->writeln($response);
    }
}
