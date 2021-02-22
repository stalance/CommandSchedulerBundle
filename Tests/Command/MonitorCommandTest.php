<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Command\MonitorCommand;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class MonitorCommandTest.
 */
class MonitorCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:execute without option.
     */
    public function testExecuteWithError()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(MonitorCommand::class, ['--dump' => true])->getDisplay();

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $this->assertMatchesRegularExpression('/two/', $output);
        $this->assertMatchesRegularExpression('/four/', $output);
    }

    /**
     * Test scheduler:execute without option.
     */
    public function testExecuteWithoutError()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $two = $this->em->getRepository(ScheduledCommand::class)->find(2);
        $four = $this->em->getRepository(ScheduledCommand::class)->find(4);
        $two->setLocked(false);
        $four->setLastReturnCode(0);
        $this->em->flush();

        // None command should be in error status here.

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(MonitorCommand::class, ['--dump' => true])->getDisplay();

        $this->assertStringStartsWith('No errors found.', $output);
    }
}
