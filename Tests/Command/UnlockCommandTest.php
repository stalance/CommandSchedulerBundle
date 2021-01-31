<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use JMose\CommandSchedulerBundle\Command\UnlockCommand;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class UnlockCommandTest.
 */
class UnlockCommandTest extends WebTestCase
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
     * @param array $arguments All the arguments passed when executing the command
     * @param array $inputs    The (optional) answers given to the command when it asks for the value of the missing arguments
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
     * Test scheduler:unlock without --all option.
     */
    public function testUnlockAll()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(UnlockCommand::class, ['--all' => true])->getDisplay();

        $this->assertMatchesRegularExpression('/"two"/', $output);
        $this->assertDoesNotMatchRegularExpression('/"one"/', $output);
        $this->assertDoesNotMatchRegularExpression('/"three"/', $output);

        $this->em->clear();
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);

        $this->assertFalse($two->isLocked());
    }

    /**
     * Test scheduler:unlock with given command name.
     */
    public function testUnlockByName()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(UnlockCommand::class, ['name' => 'two'])->getDisplay();

        $this->assertMatchesRegularExpression('/"two"/', $output);

        $this->em->clear();
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);

        $this->assertFalse($two->isLocked());
    }

    /**
     * Test scheduler:unlock with given command name and timeout.
     */
    public function testUnlockByNameWithTimout()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture with last execution two days ago (2), another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(UnlockCommand::class,
            ['name' => 'two', '--lock-timeout' => 3 * 24 * 60 * 60])
            ->getDisplay();

        $this->assertMatchesRegularExpression('/Skipping/', $output);
        $this->assertMatchesRegularExpression('/"two"/', $output);

        $this->em->clear();
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);

        $this->assertTrue($two->isLocked());
    }
}
