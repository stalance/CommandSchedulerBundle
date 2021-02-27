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
     * @param array|null $profiling
     * @param \Exception|null $exception
     */
    #[Pure]
    public function __construct(
        private ScheduledCommand $command,
        private int $result,
        private ?OutputInterface $log = null,
        private ?array $profiling = null,
        private ?\Exception $exception = null)
    {
        parent::__construct($command);
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

    public function getProfiling(): ?array
    {
        return $this->profiling;
    }

    public function getRuntime(): ?\DateInterval
    {
        return $this->profiling["runtime"] ?? null;
    }

    /**
     * @return \Exception|null
     */
    public function getException(): ?\Exception
    {
        return $this->exception;
    }

}
