<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Command\StartSchedulerCommand;
use JMose\CommandSchedulerBundle\Command\StopSchedulerCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class StartStopSchedulerCommandTest.
 */
class StartStopSchedulerCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:start and scheduler:stop.
     */
    public function testStartAndStopScheduler()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.StartSchedulerCommand::PID_FILE;

        $output = $this->executeCommand(StartSchedulerCommand::class)->getDisplay();
        $this->assertStringStartsWith('Command scheduler started in non-blocking mode...', $output);
        $this->assertFileExists($pidFile);

        $output = $this->executeCommand(StopSchedulerCommand::class)->getDisplay();
        $this->assertStringStartsWith('Command scheduler is stopped.', $output);
        $this->assertFileDoesNotExist($pidFile);
    }
}
