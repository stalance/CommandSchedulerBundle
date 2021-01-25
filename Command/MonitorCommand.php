<?php

namespace JMose\CommandSchedulerBundle\Command;

use DateTimeInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * @var bool
     */
    private $dumpMode;

    /**
     * MonitorCommand constructor.
     *
     * @param $managerName
     * @param $lockTimeout
     * @param $receiver
     * @param $mailSubject
     * @param $sendMailIfNoError
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $managerName,
        private $lockTimeout,
        private $receiver,
        private $mailSubject,
        private $sendMailIfNoError
    ) {
        $this->em = $managerRegistry->getManager($managerName);

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
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // If not in dump mode and none receiver is set, exit.
        $this->dumpMode = $input->getOption('dump');
        if (!$this->dumpMode && 0 === count($this->receiver)) {
            $output->writeln('Please add receiver in configuration');

            return 1;
        }

        // Fist, get all failed or potential timeout
        $failedCommands = $this->em->getRepository('JMoseCommandSchedulerBundle:ScheduledCommand')
            ->findFailedAndTimeoutCommands($this->lockTimeout);

        // Commands in error
        if (count($failedCommands) > 0) {
            $message = '';
            foreach ($failedCommands as $command) {
                $message .= sprintf(
                    "%s: returncode %s, locked: %s, last execution: %s\n",
                    $command->getName(),
                    $command->getLastReturnCode(),
                    $command->getLocked(),
                    $command->getLastExecution()->format(DateTimeInterface::ATOM)
                );
            }
            // if --dump option, don't send mail
            if ($this->dumpMode) {
                $output->writeln($message);
            } else {
                $this->sendMails($message);
            }
        } elseif ($this->dumpMode) {
            $output->writeln('No errors found.');
        } elseif ($this->sendMailIfNoError) {
            $this->sendMails('No errors found.');
        }

        return 0;
    }

    /**
     * Send message to email receivers.
     *
     * @param string $message message to be sent
     */
    private function sendMails(string $message): void
    {
        // prepare email constants
        $hostname = gethostname();
        $subject = $this->getMailSubject();
        $headers = 'From: cron-monitor@'.$hostname."\r\n".
            'X-Mailer: PHP/'.phpversion();

        foreach ($this->receiver as $rcv) {
            mail(trim($rcv), $subject, $message, $headers);
        }
    }

    /**
     * get the subject for monitor mails.
     *
     * @return string subject
     */
    private function getMailSubject(): string
    {
        $hostname = gethostname();

        return sprintf($this->mailSubject, $hostname, date('Y-m-d H:i:s'));
    }
}
