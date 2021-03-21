<?php

namespace Dukecity\CommandSchedulerBundle\Entity;

use Carbon\Carbon;
use Cron\CronExpression as CronExpressionLib;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Dukecity\CommandSchedulerBundle\Validator\Constraints as AssertDukecity;

/**
 * @ORM\Entity(repositoryClass="Dukecity\CommandSchedulerBundle\Repository\ScheduledCommandRepository")
 * @ORM\Table(name="scheduled_command")
 * @UniqueEntity("name")
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
//#[ORM\Entity(repositoryClass="Dukecity\CommandSchedulerBundle\Repository\ScheduledCommandRepository")]
//#[ORM\Table(name="scheduled_command")]
class ScheduledCommand
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;

    /**
     * @ORM\Version
     * @ORM\Column(type="integer")
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/transactions-and-concurrency.html
     */
    private int $version;

    /**
     * The creation date
     *
     * @var ?DateTime
     * @ORM\Column(type="datetime", name="created_at", nullable=true)
     */
    private ?DateTime $createdAt = null;

    /**
     * @var string
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=150, nullable=false, unique=true)
     */
    private string $name;

    /**
     * @var string
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private string $command;

    /**
     * @var ?string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $arguments = null;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     * @Assert\NotBlank
     * @AssertDukecity\CronExpression
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     */
    private string $cronExpression;

    /**
     * @Assert\Type("\DateTime")
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
     * @Assert\Type("integer")
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $priority;

    /**
     * If true, command will be execute next time regardless cron expression.
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $executeImmediately = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $disabled = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $locked = false;

    /**
     * Init new ScheduledCommand.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        #$this->setLastExecution(new DateTime());
        $this->lastExecution = null;
        $this->setLocked(false);
        $this->priority = 0;
        $this->version = 1;
    }

    #[Pure]
    public function __toString(): string
    {
        return $this->getName();
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
     * @param DateTime $lastExecution
     *
     * @return ScheduledCommand
     */
    public function setLastExecution(DateTime $lastExecution): ScheduledCommand
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
    public function getPriority(): int
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
     * @return ?DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return DateTime
     */
    public function setCreatedAt(DateTime $createdAt): DateTime
    {
        $this->createdAt = $createdAt;
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

    /**
     * Get a human readable format of the next run of the scheduled command
     * @example 3 minutes
     * @return string|null
     */
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
