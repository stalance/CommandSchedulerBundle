<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace JMose\CommandSchedulerBundle\Command;

use JMose\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;
use Carbon\Carbon;
use Cron\CronExpression as CronExpressionLib;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class MonitorCommand : This class is used for monitoring scheduled commands if they run for too long or failed to execute.
 *
 * @author  Daniel Fischer <dfischer000@gmail.com>
 */
class MonitorCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'scheduler:monitor';
    private ObjectManager $em;
    private EventDispatcherInterface $eventDispatcher;
    private ParameterBagInterface $params;

    /**
     * MonitorCommand constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $managerRegistry
     * @param string                   $managerName
     * @param int | bool               $lockTimeout
     * @param array                    $receiver
     * @param string                   $mailSubject
     * @param bool                     $sendMailIfNoError
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $managerRegistry,
        string $managerName,
        private int | bool $lockTimeout,
        private array $receiver,
        private string $mailSubject,
        private bool $sendMailIfNoError = false
    ) {
        $this->em = $managerRegistry->getManager($managerName);
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Monitor scheduled commands')
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Display result instead of send mail')
            ->setHelp('This class is for monitoring all active commands.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // If not in dump mode and none receiver is set, exit.
        $dumpMode = (bool) $input->getOption('dump');
        if (!$dumpMode && 0 === count($this->receiver)) {
            $output->writeln('Please add receiver in configuration');

            return Command::FAILURE;
        }

        // Fist, get all failed or potential timeout
        $failedCommands = $this->em->getRepository('JMoseCommandSchedulerBundle:ScheduledCommand')
            //->findFailedAndTimeoutCommands($this->lockTimeout);
        ->findAll();

        // Commands in error
        if (count($failedCommands) > 0) {
            // if --dump option, don't send mail
            if ($dumpMode) {
                $this->dump($output, $failedCommands);
            } else {
                $this->eventDispatcher->dispatch(new SchedulerCommandFailedEvent($failedCommands));
            }
        } elseif ($dumpMode) {
            $output->writeln('No errors found.');
        } /*elseif ($this->params->get('sendMailIfNoError')) {
            $this->sendMails('No errors found.');
        }*/

        return Command::SUCCESS;
    }

    /**
     * Print a table of locked Commands to console.
     *
     * @param $output
     * @param array $failedCommands
     *
     * @throws \Exception
     */
    private function dump($output, array $failedCommands): void
    {
        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['Name', 'LastReturnCode', 'Locked', 'LastExecution', 'NextExecution']);

        foreach ($failedCommands as $command) {
            $lockedInfo = match ($command->getLocked()) {
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
                $lastReturnInfo,
                $lockedInfo,
                $command->getLastExecution()->format('Y-m-d H:i').' ('
                    .Carbon::instance($command->getLastExecution())->diffForHumans().')',
                $nextRunDate->format('Y-m-d H:i').' ('
                    .Carbon::instance($nextRunDate)->diffForHumans().')',
            ]);
        }

        $table->render();
    }
}
