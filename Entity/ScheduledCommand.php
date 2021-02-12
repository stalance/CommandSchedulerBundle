<?php

namespace JMose\CommandSchedulerBundle\Entity;

use Cron\CronExpression as CronExpressionLib;
use DateTime;

/**
 * Entity ScheduledCommand.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class ScheduledCommand
{
    private ?int $id = null;

    private ?string $name = null;

    private ?string $command = null;

    private ?string $arguments = null;

    /**
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     */
    private ?string $cronExpression = null;

    private ?DateTime $lastExecution = null;

    private ?int $lastReturnCode = null;

    // Log's file name (without path).
    private ?string $logFile = null;

    private ?int $priority = 0;

    // If true, command will be execute next time regardless cron expression.
    private bool $executeImmediately = false;

    private ?bool $disabled = null;

    private ?bool $locked = null;

    /**
     * Init new ScheduledCommand.
     */
    public function __construct()
    {
        $this->setLastExecution(new DateTime());
        $this->setLocked(false);
        $this->priority = 0;
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param $id
     *
     * @return ScheduledCommand
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ScheduledCommand
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get command.
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * Set command.
     *
     * @param string $command
     *
     * @return ScheduledCommand
     */
    public function setCommand(string $command): static
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get arguments.
     */
    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    /**
     * Set arguments.
     *
     * @param string $arguments
     *
     * @return ScheduledCommand
     */
    public function setArguments(string $arguments): static
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get cronExpression.
     */
    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    /**
     * Set cronExpression.
     *
     * @param string $cronExpression
     *
     * @return ScheduledCommand
     */
    public function setCronExpression(string $cronExpression): static
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    /**
     * Get lastExecution.
     */
    public function getLastExecution(): ?DateTime
    {
        return $this->lastExecution;
    }

    /**
     * Set lastExecution.
     *
     * @param \DateTimeInterface|null $lastExecution
     *
     * @return ScheduledCommand
     */
    public function setLastExecution(\DateTimeInterface $lastExecution = null): static
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    /**
     * Get logFile.
     */
    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    /**
     * Set logFile.
     *
     * @param string $logFile
     *
     * @return ScheduledCommand
     */
    public function setLogFile(string $logFile): static
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * Get lastReturnCode.
     */
    public function getLastReturnCode(): ?int
    {
        return $this->lastReturnCode;
    }

    /**
     * Set lastReturnCode.
     *
     * @param int|null $lastReturnCode
     *
     * @return ScheduledCommand
     */
    public function setLastReturnCode(?int $lastReturnCode): static
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    /**
     * Get priority.
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return ScheduledCommand
     */
    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get executeImmediately.
     */
    public function isExecuteImmediately(): bool
    {
        return $this->executeImmediately;
    }

    /**
     * Get executeImmediately.
     */
    public function getExecuteImmediately(): bool
    {
        return $this->executeImmediately;
    }

    /**
     * Set executeImmediately.
     *
     * @param $executeImmediately
     *
     * @return ScheduledCommand
     */
    public function setExecuteImmediately(bool $executeImmediately): static
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    /**
     * Get disabled.
     */
    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * Get disabled.
     */
    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return ScheduledCommand
     */
    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Locked Getter.
     */
    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * locked Getter.
     */
    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * locked Setter.
     *
     * @param bool $locked
     *
     * @return ScheduledCommand
     */
    public function setLocked(bool $locked): static
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return DateTime|null
     * @throws \Exception
     */
    public function getNextRunDate(): ?DateTime
    {
        return (new CronExpressionLib($this->getCronExpression()))->getNextRunDate();
    }
}
