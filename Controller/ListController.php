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
 * @author Julien Guyon <julienguyon@hotmail.com>
 */
class ListController extends AbstractBaseController
{
    private int $lockTimeout = 3600;
    private LoggerInterface $logger;

    /**
     * @param int $lockTimeout
     */
    public function setLockTimeout(int $lockTimeout): void
    {
        $this->lockTimeout = $lockTimeout;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function indexAction(): Response
    {
        $scheduledCommands = $this->getDoctrineManager()->getRepository(
            #'JMoseCommandSchedulerBundle:ScheduledCommand'
            ScheduledCommand::class
        )->findAll();

        #dump(count($scheduledCommands));

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
        $this->addFlash('success', $this->translator->trans('flash.execute', ["%name%" => $scheduledCommand->getName()], 'JMoseCommandScheduler'));

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
}
