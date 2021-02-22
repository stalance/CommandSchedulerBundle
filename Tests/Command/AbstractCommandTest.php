<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AddCommandTest.
 */
class AbstractCommandTest extends WebTestCase
{
    use FixturesTrait;

    /** @var EntityManager */
    protected EntityManager $em;

    /** @var CommandTester|null */
    protected CommandTester | null $commandTester;

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
    protected function executeCommand(string $commandClass, array $arguments = [], array $inputs = []): CommandTester
    {
        // this uses a special testing container that allows you to fetch private services
        $cmd = self::$container->get($commandClass);
        $cmd->setApplication(new Application('Test'));

        $commandTester = new CommandTester($cmd);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);

        return $commandTester;
    }
}
