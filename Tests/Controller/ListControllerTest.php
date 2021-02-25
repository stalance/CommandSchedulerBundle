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
 * Class ListControllerTest.
 */
class ListControllerTest extends WebTestCase
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
     * Test list display.
     */
    public function testIndex()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $crawler = $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(4, $crawler->filter('a[href^="/command-scheduler/action/toggle/"]')->count());
    }

    /**
     * Test permanent deletion on command.
     */
    public function testRemove()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        $command = $this->em->getRepository(ScheduledCommand::class)->findOneById(1);


        //toggle off
        $crawler = $this->client->request('GET', '/command-scheduler/action/remove/1');

        $this->assertEquals(3, $crawler->filter('a[href^="/command-scheduler/action/toggle/"]')->count());
    }

    /**
     * Test On/Off toggle on list.
     */
    public function testToggle()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        //toggle off
        $crawler = $this->client->request('GET', '/command-scheduler/action/toggle/1');
        $this->assertEquals(1, $crawler->filter(
            'a[href="/command-scheduler/action/toggle/1"] > span[class="text-danger glyphicon glyphicon-off"]')
            ->count());

        //toggle on
        $crawler = $this->client->request('GET', '/command-scheduler/action/toggle/1');
        $this->assertEquals(0, $crawler->filter('a[href="/command-scheduler/action/toggle/1"] > span[class="text-danger glyphicon glyphicon-off"]')->count());
    }

    /**
     * Test Execute now button on list.
     */
    public function testExecute()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        //call execute now button
        $crawler = $this->client->request('GET', '/command-scheduler/action/execute/1');
        //$this->assertEquals(1, $crawler->filter('a[data-href="/command-scheduler/action/execute/1"] > span[class="text-muted glyphicon glyphicon-play"]')->count());
        $this->assertTrue(false);
        //$this->assertEquals(1, $crawler->filterXPath('//div[contains("Command will be executed during the next")')->count());
    }

    /**
     * Test unlock button on list.
     */
    public function testUnlock()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        // One command is locked in fixture (2)
        $crawler = $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(1, $crawler->filter('a[data-href="/command-scheduler/action/unlock/2"]')->count());

        $crawler = $this->client->request('GET', '/command-scheduler/action/unlock/2');
        $this->assertEquals(0, $crawler->filter('a[data-href="/command-scheduler/action/unlock/2"]')->count());
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
