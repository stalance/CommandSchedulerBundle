<?php /** @noinspection ALL */

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Command\ExecuteCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ExecuteCommandTest.
 */
class ExecuteCommandTest extends WebTestCase
{
    use FixturesTrait;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        self::bootKernel();

        $em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

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
     * Test scheduler:execute without option.
     */
    public function testExecute()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ExecuteCommand::class, [])->getDisplay();

        $this->assertStringStartsWith('Start : Execute all scheduled command', $output);
        $this->assertMatchesRegularExpression('/debug:container should be executed/', $output);
        $this->assertMatchesRegularExpression('/Execute : debug:container --help/', $output);
        $this->assertMatchesRegularExpression('/Immediately execution asked for : debug:router/', $output);
        $this->assertMatchesRegularExpression('/Execute : debug:router/', $output);

        $output = $this->executeCommand(ExecuteCommand::class)->getDisplay();
        $this->assertMatchesRegularExpression('/Nothing to do/', $output);
    }

    /**
     * Test scheduler:execute without option.
     */
    public function testExecuteWithNoOutput()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ExecuteCommand::class, ['--no-output' => true])->getDisplay();

        $this->assertEquals('', $output);

        $output = $this->executeCommand(ExecuteCommand::class)->getDisplay();
        $this->assertMatchesRegularExpression('/Nothing to do/', $output);
    }

    /**
     * Test scheduler:execute with --dump option.
     */
    public function testExecuteWithDump()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ExecuteCommand::class, ['--dump' => true])->getDisplay();

        $this->assertStringStartsWith('Start : Dump all scheduled command', $output);
        $this->assertMatchesRegularExpression('/Command debug:container should be executed/', $output);
        $this->assertMatchesRegularExpression('/Immediately execution asked for : debug:router/', $output);
    }
}
