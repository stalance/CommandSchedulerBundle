<?php

namespace JMose\CommandSchedulerBundle\Event;

use JetBrains\PhpStorm\Pure;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerCommandPostExecutionEvent extends AbstractSchedulerCommandEvent
{
    /**
     * List of failed commands.
     *
     * @param ScheduledCommand $command
     * @param int $result
     * @param OutputInterface|null $log
     */
    #[Pure]
    public function __construct(
        private ScheduledCommand $command,
        private int $result,
        private ?OutputInterface $log = null)
    {
        return parent::__construct($command);
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @return OutputInterface|null
     */
    public function getLog(): ?OutputInterface
    {
        return $this->log;
    }
}
