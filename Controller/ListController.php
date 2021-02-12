<?php

namespace JMose\CommandSchedulerBundle\Controller;

use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListController.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class ListController extends AbstractBaseController
{
    private int $lockTimeout = 3600;
    private LoggerInterface $logger;

    /**
     * @param $lockTimeout int
     */
    public function setLockTimeout(int $lockTimeout): void
    {
        $this->lockTimeout = $lockTimeout;
    }

    /**
     * @param $logger LoggerInterface
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function indexAction(): Response
    {
        $scheduledCommands = $this->getDoctrineManager()->getRepository(
            'JMoseCommandSchedulerBundle:ScheduledCommand'
        )->findAll();

        return $this->render(
            '@JMoseCommandScheduler/List/index.html.twig',
            ['scheduledCommands' => $scheduledCommands]
        );
    }

    /**
     * @param ScheduledCommand $scheduledCommand
     *
     * @return RedirectResponse
     */
    public function removeAction(ScheduledCommand $scheduledCommand): RedirectResponse
    {
        $entityManager = $this->getDoctrineManager();
        $entityManager->remove($scheduledCommand);
        $entityManager->flush();

        // Add a flash message and do a redirect to the list
        $this->addFlash('success', $this->translator->trans('flash.deleted', [], 'JMoseCommandScheduler'));

        return $this->redirectToRoute('jmose_command_scheduler_list');
    }

    /**
     * Toggle enabled/disabled.
     *
     * @param ScheduledCommand $scheduledCommand
     *
     * @return RedirectResponse
     */
    public function toggleAction(ScheduledCommand $scheduledCommand): RedirectResponse
    {
        $scheduledCommand->setDisabled(!$scheduledCommand->isDisabled());
        $this->getDoctrineManager()->flush();

        return $this->redirectToRoute('jmose_command_scheduler_list');
    }

    /**
     * @param ScheduledCommand $scheduledCommand
     * @param Request          $request
     *
     * @return RedirectResponse
     */
    public function executeAction(ScheduledCommand $scheduledCommand, Request $request): RedirectResponse
    {
        $scheduledCommand->setExecuteImmediately(true);
        $this->getDoctrineManager()->flush();

        // Add a flash message and do a redirect to the list
        $this->addFlash('success', $this->translator->trans('flash.execute', [], 'JMoseCommandScheduler'));

        if ($request->query->has('referer')) {
            return $this->redirect($request->getSchemeAndHttpHost().urldecode($request->query->get('referer')));
        }

        return $this->redirectToRoute('jmose_command_scheduler_list');
    }

    /**
     * @param ScheduledCommand $scheduledCommand
     * @param Request          $request
     *
     * @return RedirectResponse
     */
    public function unlockAction(ScheduledCommand $scheduledCommand, Request $request): RedirectResponse
    {
        $scheduledCommand->setLocked(false);
        $this->getDoctrineManager()->flush();

        // Add a flash message and do a redirect to the list
        $this->addFlash('success', $this->translator->trans('flash.unlocked', [], 'JMoseCommandScheduler'));

        if ($request->query->has('referer')) {
            return $this->redirect($request->getSchemeAndHttpHost().urldecode($request->query->get('referer')));
        }

        return $this->redirectToRoute('jmose_command_scheduler_list');
    }

    /**
     * method checks if there are jobs which are enabled but did not return 0 on last execution or are locked.
     * if a match is found, HTTP status 417 is sent along with an array which contains name, return code and locked-state.
     * if no matches found, HTTP status 200 is sent with an empty array.
     *
     * @return JsonResponse
     */
    public function monitorAction(): JsonResponse
    {
        $failedCommands = $this->getDoctrineManager()
            ->getRepository(ScheduledCommand::class)
            ->findFailedAndTimeoutCommands($this->lockTimeout);

        $jsonArray = [];
        if (is_iterable($failedCommands)) {
            foreach ($failedCommands as $command) {
                $jsonArray[$command->getName()] = [
                'NAME' => $command->getName(),
                'COMMAND' => $command->getCommand(),
                'ARGUMENTS' => $command->getArguments(),
                'LAST_RETURN_CODE' => $command->getLastReturnCode(),
                'B_LOCKED' => $command->getLocked() ? 'true' : 'false',
                'DH_LAST_EXECUTION' => $command->getLastExecution(),
                'DH_NEXT_EXECUTION' => $command->getNextRunDate(),
                'LOGFILE' => $command->getLogFile(),
                ];
            }

            $this->logger->debug('MonitorCommand found locked or timed out commands', ['amount' => count($failedCommands)]);
        } else {
            // HTTP_OK: no failed or timeout commands
            return new JsonResponse();
        }

        $response = new JsonResponse();
        try {
            $response->setContent(json_encode($jsonArray, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            $this->logger->error('MonitorCommand failed', ['message' => $e->getMessage()]);
        }

        // StatusCode 417 (error)
        return $response->setStatusCode(Response::HTTP_EXPECTATION_FAILED);
    }
}
