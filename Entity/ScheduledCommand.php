<?php

namespace Dukecity\CommandSchedulerBundle\Entity;

use Carbon\Carbon;
use Cron\CronExpression as CronExpressionLib;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Dukecity\CommandSchedulerBundle\Repository\ScheduledCommandRepository")
 * @ORM\Table(name="scheduled_command")
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
//#[ORM\Entity(repositoryClass="Dukecity\CommandSchedulerBundle\Repository\ScheduledCommandRepository")]
//#[ORM\Table(name="scheduled_command")]
class ScheduledCommand
{
    /**
     * @var ?int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Version
     * @ORM\Column(type="integer")
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/transactions-and-concurrency.html
     */
    private ?int $version;

    /**
     * @var ?string
     *
     * @ORM\Column(type="string", length=150)
     */
    private ?string $name = null;

    /**
     * @var ?string
     *
     * @ORM\Column(type="string", length=200)
     */
    private ?string $command = null;

    /**
     * @var ?string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $arguments = null;

    /**
     * @var ?string
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     */
    private ?string $cronExpression = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $lastExecution = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $lastReturnCode = null;

    /**
     * Log's file name (without path).
     *
     * @var ?string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private ?string $logFile = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $priority;

    /**
     * If true, command will be execute next time regardless cron expression.
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $executeImmediately = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private ?bool $disabled = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private ?bool $locked = false;

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
     * @param int $id
     *
     * @return ScheduledCommand
     */
    public function setId(int $id): ScheduledCommand
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
    public function setName(string $name): ScheduledCommand
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
    public function setCommand(string $command): ScheduledCommand
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
     * @param string|null $arguments
     *
     * @return ScheduledCommand
     */
    public function setArguments(?string $arguments): ScheduledCommand
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
    public function setCronExpression(string $cronExpression): ScheduledCommand
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
     * @param \DateTime|null $lastExecution
     *
     * @return ScheduledCommand
     */
    public function setLastExecution(\DateTime | null $lastExecution = null): ScheduledCommand
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
    public function setLogFile(string $logFile): ScheduledCommand
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
    public function setLastReturnCode(?int $lastReturnCode): ScheduledCommand
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
    public function setPriority(int $priority): ScheduledCommand
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
     * @param bool $executeImmediately
     *
     * @return ScheduledCommand
     */
    public function setExecuteImmediately(bool $executeImmediately): ScheduledCommand
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
    public function setDisabled(bool $disabled): ScheduledCommand
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
    public function setLocked(bool $locked): ScheduledCommand
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Returns the next run time of the scheduled command
     * @param bool $checkExecuteImmediately Check if immediately execution is set
     *
     * @return DateTime|null
     *
     * @throws \Exception
     */
    public function getNextRunDate(bool $checkExecuteImmediately = true): ?DateTime
    {
        if($this->getDisabled() || $this->getLocked())
        {return null;}

        if ($checkExecuteImmediately && $this->getExecuteImmediately()) {
            return new DateTime();
        }

        return (new CronExpressionLib($this->getCronExpression()))->getNextRunDate();
    }


    public function getNextRunDateForHumans(): ?string
    {
        try{
            return Carbon::instance($this->getNextRunDate())->diffForHumans();
        }
        catch (\Exception)
        {}

        return null;
    }
}
