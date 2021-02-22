<?php

/** @noinspection PhpMissingFieldTypeInspection */

namespace JMose\CommandSchedulerBundle\Command;

use Doctrine\Persistence\ObjectManager;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Remove a command.
 */
class RemoveCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'scheduler:remove';
    private ObjectManager $em;

    /**
     * UnlockCommand constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param string          $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, string $managerName)
    {
        $this->em = $managerRegistry->getManager($managerName);

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Remove a scheduled command. Hard delete from Database')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the command to remove')
           ;
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
        $io = new SymfonyStyle($input, $output);

        $commandName = (string) $input->getArgument('name');

        try {
            $command = $this->em->getRepository(ScheduledCommand::class)->findOneBy(
                ['name' => $commandName]
            );

            $this->em->remove($command);
            $this->em->flush();

            $io->success(sprintf('The Command %s is deleted successfully', $commandName));

            return Command::SUCCESS;
        } catch (\Exception) {
            $io->error(sprintf('Could not find/delete the command %s', $commandName));

            return Command::FAILURE;
        }
    }
}
