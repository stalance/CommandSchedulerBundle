<?php

namespace JMose\CommandSchedulerBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\TranslatorInterface as ComponentTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as ContractsTranslatorInterface;

/**
 * Class BaseController.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
abstract class BaseController extends AbstractController
{
    private string $managerName;

    /**
     * @var ContractsTranslatorInterface|ComponentTranslatorInterface
     */
    protected $translator;

    /**
     * @param $managerName string
     */
    public function setManagerName(string $managerName)
    {
        $this->managerName = $managerName;
    }

    public function setTranslator(ContractsTranslatorInterface|ComponentTranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return ObjectManager
     */
    protected function getDoctrineManager(): \Doctrine\Persistence\ObjectManager
    {
        return $this->getDoctrine()->getManager($this->managerName);
    }
}
