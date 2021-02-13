<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Command\StartSchedulerCommand;
use JMose\CommandSchedulerBundle\Command\StopSchedulerCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class StartStopSchedulerCommandTest.
 */
class StartStopSchedulerCommandTest extends WebTestCase
{
    use FixturesTrait;

    /**
     * This helper method abstracts the boilerplate code needed to test thetes
     * execution of a command.
     *
     * @param string $commandClass
     * @param array  $arguments    All the arguments passed when executing the command
     * @param array  $inputs       The (optional) answers given to the command when it asks for the value of the missing arguments
     *
     * @return CommandTester
     */
    private function executeCommand(string $commandClass, array $arguments = [], array $inputs = []): CommandTester
    {
        // this uses a special testing container that allows you to fetch private services
        $command = self::$container->get($commandClass);
        $command->setApplication(new Application('Test'));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);

        return $commandTester;
    }

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
