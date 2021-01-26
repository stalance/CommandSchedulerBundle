<?php

namespace JMose\CommandSchedulerBundle\Controller;

use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\TranslatorInterface as ComponentTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as ContractsTranslatorInterface;

/**
 * Class BaseController.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
abstract class AbstractBaseController extends AbstractController
{
    private string $managerName;

    /**
     * @var ContractsTranslatorInterface|ComponentTranslatorInterface
     */
    protected ComponentTranslatorInterface | ContractsTranslatorInterface $translator;

    /**
     * @param $managerName string
     */
    public function setManagerName(string $managerName)
    {
        $this->managerName = $managerName;
    }

    /**
     * @param ContractsTranslatorInterface|ComponentTranslatorInterface $translator
     */
    public function setTranslator(ContractsTranslatorInterface | ComponentTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return ObjectManager
     */
    protected function getDoctrineManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager($this->managerName);
    }
}
