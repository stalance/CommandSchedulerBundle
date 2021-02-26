<?php

namespace JMose\CommandSchedulerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command is just for testing.
 */
//#[ConsoleCommand(name: 'scheduler:test', description: 'long running command', hidden: true)]
class TestCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'scheduler:test';
    private SymfonyStyle $io;
    private int $runtime;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Test a long running command')
            ->addArgument('runtime', InputArgument::OPTIONAL, 'Runtime in Seconds', 10)
            ->setHidden(true)
        ;
    }

    /**
     * Initialize parameters and services used in execute function.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->runtime = (int) $input->getArgument('runtime') ?? 10;

        $this->io = new SymfonyStyle($input, $output);
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
        $this->io->info('Start the process for '.$this->runtime.' seconds');

        $i = 0;
        while ($i < $this->runtime) {
            ++$i;
            sleep(1);
            $this->io->info('Output after '.$i.' Seconds');
        }

        return Command::SUCCESS;
    }
}
