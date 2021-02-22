<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use JMose\CommandSchedulerBundle\Command\RemoveCommand;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class UnlockCommandTest.
 */
class RemoveCommandTest extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var EntityManager
     */
    private EntityManager $em;
    /**
     * @var CommandTester|null
     */
    private CommandTester | null $commandTester;

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
     * @param array  $inputs       The (optional) answers given to the command
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
     * Test scheduler:remove with given command name.
     */
    public function testRemove()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        # Remove command
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'two'])->getDisplay();
        $this->assertStringContainsString('successfully', $output);

        # Not in DB anymore
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);
        $this->assertNull($two);

        # Fails now
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'two'])->getDisplay();
        $this->assertStringContainsString('Could not', $output);
    }
}
