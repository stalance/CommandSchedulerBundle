<?php

namespace JMose\CommandSchedulerBundle\Repository;

use Cron\CronExpression;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\TransactionRequiredException;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;

/**
 * Class ScheduledCommandRepository.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class ScheduledCommandRepository extends EntityRepository
{
    /**
     * Find all enabled command ordered by priority.
     *
     * @return array|null
     */
    public function findEnabledCommand(): ?array
    {
        return $this->findBy(['disabled' => false, 'locked' => false], ['priority' => 'DESC']);
    }

    /**
     * findAll override to implement the default orderBy clause.
     *
     * @return array|null
     */
    public function findAll(): ?array
    {
        return $this->findBy([], ['priority' => 'DESC']);
    }

    /**
     * Find all locked commands.
     *
     * @return ScheduledCommand[]
     */
    public function findLockedCommand(): array
    {
        return $this->findBy(['disabled' => false, 'locked' => true], ['priority' => 'DESC']);
    }

    /**
     * Find all failed command.
     *
     * @return ScheduledCommand[]|null
     */
    public function findFailedCommand(): ?array
    {
        return $this->createQueryBuilder('command')
            ->where('command.disabled = :disabled')
            ->andWhere('command.lastReturnCode != :lastReturnCode')
            ->setParameter('lastReturnCode', 0)
            ->setParameter('disabled', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all enabled commands that need to be exceuted ordered by priority.
     *
     * @return array|null
     * @throws \Exception
     * @throws \Exception
     */
    public function findCommandsToExecute(): ?array
    {
        $enabledCommands = $this->findEnabledCommand();
        $commands = [];
        $now = new \DateTime();

        # Get commands which runtime is in the past or
        # execution is forced onetimes via isExecuteImmediately
        foreach ($enabledCommands as $command) {
            if ($command->isExecuteImmediately()) {
                $commands[] = $command;
            } else {
                $cron = new CronExpression($command->getCronExpression());
                try {
                    $nextRunDate = $cron->getNextRunDate($command->getLastExecution());

                    if ($nextRunDate < $now) {
                        $commands[] = $command;
                    }
                } catch (\Exception $e) {
                }

            }
        }

        return $commands;
    }


    /**
     * @param int|bool $lockTimeout
     *
     * @return array
     */
    public function findFailedAndTimeoutCommands(int | bool $lockTimeout = false): array
    {
        // Fist, get all failed commands (return != 0)
        $failedCommands = $this->findFailedCommand();

        // Then, si a timeout value is set, get locked commands and check timeout
        if (false !== $lockTimeout) {
            $lockedCommands = $this->findLockedCommand();
            foreach ($lockedCommands as $lockedCommand) {
                $now = time();
                if ($lockedCommand->getLastExecution()->getTimestamp() + $lockTimeout < $now) {
                    $failedCommands[] = $lockedCommand;
                }
            }
        }

        return $failedCommands;
    }

    /**
     * @param ScheduledCommand $command
     *
     * @return ScheduledCommand | null
     *
     * @throws NonUniqueResultException
     * @throws TransactionRequiredException
     */
    public function getNotLockedCommand(ScheduledCommand $command): ScheduledCommand | null
    {
        $query = $this->createQueryBuilder('command')
            ->where('command.locked = false')
            ->andWhere('command.id = :id')
            ->setParameter('id', $command->getId())
            ->getQuery();

        # https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/transactions-and-concurrency.html
        $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

        return $query->getOneOrNullResult();
    }
}
