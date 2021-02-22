<?php

/** @noinspection PhpMissingFieldTypeInspection */

namespace JMose\CommandSchedulerBundle\Command;

use Carbon\Carbon;
use Doctrine\Persistence\ObjectManager;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * This class is for listing all commands.
 */
class ListCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'scheduler:list';
    private ObjectManager $em;

    /**
     * MonitorCommand constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param string          $managerName
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        string $managerName
    ) {
        $this->em = $managerRegistry->getManager($managerName);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('List scheduled commands')
            ->setHelp('This class is for listing all active commands.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commands = $this->em->getRepository(ScheduledCommand::class)->findAll();

        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['Name', 'Command', 'Arguments',
            'LastReturnCode', 'Locked', 'LastExecution', 'NextExecution' ]);

        foreach ($commands as $command) {
            $lockedInfo = match ($command->getLocked())
            {
                true => '<error>LOCKED</error>',
                default => ''
            };

            $lastReturnInfo = match ($command->getLastReturnCode()) {
                '', false, null => '',
                0 => '<info>0 (success)</info>',
                // no break
                default => '<error>'.$command->getLastReturnCode().' (error)</error>'
            };

            $nextRunDate = $command->getNextRunDate();
            $table->addRow([
                $command->getName(),
                $command->getCommand(),
                $command->getArguments(),
                $lastReturnInfo,
                $lockedInfo,
                $command->getLastExecution()->format('Y-m-d H:i').' ('
                .Carbon::instance($command->getLastExecution())->diffForHumans().')',
                $nextRunDate->format('Y-m-d H:i').' ('
                .Carbon::instance($nextRunDate)->diffForHumans().')',
                ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
