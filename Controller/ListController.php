<?php

namespace JMose\CommandSchedulerBundle\Controller;

use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
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
    private int $lockTimeout;

    /**
     * @param $lockTimeout int
     */
    public function setLockTimeout(int $lockTimeout): void
    {
        $this->lockTimeout = $lockTimeout;
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
        $this->get('session')->getFlashBag()
            ->add('success', $this->translator->trans('flash.deleted', [], 'JMoseCommandScheduler'));

        return $this->redirect($this->generateUrl('jmose_command_scheduler_list'));
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
        #$scheduledCommand = $id;
        $scheduledCommand->setDisabled(!$scheduledCommand->isDisabled());
        $this->getDoctrineManager()->flush();

        return $this->redirect($this->generateUrl('jmose_command_scheduler_list'));
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
        $this->get('session')->getFlashBag()
            ->add('success', $this->translator->trans('flash.execute', [], 'JMoseCommandScheduler'));

        if ($request->query->has('referer')) {
            return $this->redirect($request->getSchemeAndHttpHost().urldecode($request->query->get('referer')));
        }

        return $this->redirect($this->generateUrl('jmose_command_scheduler_list'));
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
        $this->get('session')->getFlashBag()
             ->add('success',
                $this->translator->trans('flash.unlocked', [], 'JMoseCommandScheduler'));

        if ($request->query->has('referer')) {
            return $this->redirect($request->getSchemeAndHttpHost().urldecode($request->query->get('referer')));
        }

        return $this->redirect($this->generateUrl('jmose_command_scheduler_list'));
    }

    /**
     * method checks if there are jobs which are enabled but did not return 0 on last execution or are locked.<br>
     * if a match is found, HTTP status 417 is sent along with an array which contains name, return code and locked-state.
     * if no matches found, HTTP status 200 is sent with an empty array.
     *
     * @throws \JsonException
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
                'LAST_RETURN_CODE' => $command->getLastReturnCode(),
                'B_LOCKED' => $command->getLocked() ? 'true' : 'false',
                'DH_LAST_EXECUTION' => $command->getLastExecution(),
            ];
            }
        }

        $response = new JsonResponse();
        try {
            $response->setContent(json_encode($jsonArray, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
        }
        $response->setStatusCode(count($jsonArray) > 0 ? Response::HTTP_EXPECTATION_FAILED : Response::HTTP_OK);

        return $response;
    }
}
