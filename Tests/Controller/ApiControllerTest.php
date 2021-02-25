<?php

namespace JMose\CommandSchedulerBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiControllerTest.
 */
class ApiControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;
    private EntityManager $em;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Test list all command URL with should return json.
     */
    public function testList()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        // List 4 Commands
        $this->client->request('GET', '/command-scheduler/api/list');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);
        $this->assertEquals(4, count($jsonArray));
        $this->assertSame('one', $jsonArray['one']['NAME']);
    }

    /**
     * Test monitoring URL with json.
     */
    public function testMonitorWithErrors()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $this->client->request('GET', '/command-scheduler/monitor');
        $this->assertEquals(Response::HTTP_EXPECTATION_FAILED, $this->client->getResponse()->getStatusCode());

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);
        $this->assertEquals(2, count($jsonArray));
    }

    /**
     * Test monitoring URL with json.
     */
    public function testMonitorWithoutErrors()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $two = $this->em->getRepository(ScheduledCommand::class)->find(2);
        $four = $this->em->getRepository(ScheduledCommand::class)->find(4);
        $two->setLocked(false);
        $four->setLastReturnCode(0);
        try {
            $this->em->flush();
        } catch (OptimisticLockException | ORMException $e) {
        }

        $this->client->followRedirects(true);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $this->client->request('GET', '/command-scheduler/monitor');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);
        $this->assertEquals(0, count($jsonArray));
    }
}
