<?php

namespace JMose\CommandSchedulerBundle\Controller;

use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Form\Type\ScheduledCommandType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DetailController.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class DetailController extends AbstractBaseController
{
    /**
     * Handle display of new/existing ScheduledCommand object.
     * This action should not be invoke directly.
     *
     * @param ScheduledCommand $scheduledCommand
     * @param Form|null        $scheduledCommandForm
     *
     * @return Response
     */
    public function indexAction(ScheduledCommand $scheduledCommand, Form $scheduledCommandForm = null): Response
    {
        // new scheduledCommand
        if (null === $scheduledCommandForm) {
            $scheduledCommandForm = $this->createForm(ScheduledCommandType::class, $scheduledCommand);
        }

        return $this->render(
            '@JMoseCommandScheduler/Detail/index.html.twig',
            [
                'scheduledCommandForm' => $scheduledCommandForm->createView(),
            ]
        );
    }

    /**
     * Initialize a new ScheduledCommand object and forward to the index action (view).
     *
     * @return Response
     */
    public function initNewScheduledCommandAction(): Response
    {
        $scheduledCommand = new ScheduledCommand();

        return $this->forward(
            self::class.'::indexAction',
            [
                'scheduledCommand' => $scheduledCommand,
            ]
        );
    }

    /**
     * Get a ScheduledCommand object with its id and forward it to the index action (view).
     *
     * @param ScheduledCommand|null $scheduledCommand
     *
     * @return Response
     */
    public function initEditScheduledCommandAction(ScheduledCommand | null $scheduledCommand): Response
    {
        return $this->forward(
            self::class.'::indexAction',
            [
                'scheduledCommand' => $scheduledCommand,
            ]
        );
    }

    /**
     * Handle save after form is submit and forward to the index action (view).
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function saveAction(Request $request): RedirectResponse | Response
    {
        $entityManager = $this->getDoctrineManager();

        // Init and populate form object
        $commandDetail = $request->request->get('command_scheduler_detail');

        if ('' != $commandDetail['id']) {
            $scheduledCommand = $entityManager->getRepository(ScheduledCommand::class)
                ->find($commandDetail['id']);
        } else {
            $scheduledCommand = new ScheduledCommand();
        }

        $scheduledCommandForm = $this->createForm(ScheduledCommandType::class, $scheduledCommand);
        $scheduledCommandForm->handleRequest($request);

        if ($scheduledCommandForm->isSubmitted() && $scheduledCommandForm->isValid()) {
            // check if we have an xml-read error for commands
            if ('error' == $scheduledCommand->getCommand()) {
                $this->get('session')->getFlashBag()
                    ->add('error', 'ERROR: please check php bin/console list --format=xml');

                return $this->redirectToRoute('jmose_command_scheduler_list');
            }

            // Handle save to the database
            if (null === $scheduledCommand->getId()) {
                $entityManager->persist($scheduledCommand);
            }
            $entityManager->flush();

            // Add a flash message and do a redirect to the list
            $this->get('session')->getFlashBag()
                ->add('success', $this->translator->trans('flash.success', [], 'JMoseCommandScheduler'));

            return $this->redirectToRoute('jmose_command_scheduler_list');
        }

        // Redirect to indexAction with the form object that has validation errors
        return $this->forward(
            self::class.'::indexAction',
            [
                'scheduledCommand' => $scheduledCommand,
                'scheduledCommandForm' => $scheduledCommandForm,
            ]
        );
    }
}
