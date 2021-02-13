<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use JMose\CommandSchedulerBundle\Command\MonitorCommand;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class MonitorCommandTest.
 */
class MonitorCommandTest extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var EntityManager
     */
    private EntityManager $em;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * This helper method abstracts the boilerplate code needed to test thetes
     * execution of a command.
     *
     * @param string $commandClass
     * @param array  $arguments    All the arguments passed when executing the command
     * @param array  $inputs       The (optional) answers given to the command when it
     *                             asks for the value of the missing arguments
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
