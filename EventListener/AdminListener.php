<?php

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\AdminBundle\Controller\DashboardController;
use Ekyna\Bundle\AdminBundle\Controller\ResourceControllerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class AdminListener
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminListener
{
    /**
     * @var AclOperatorInterface
     */
    private $aclOperator;

    /**
     * Constructor.
     *
     * @param AclOperatorInterface $aclOperator
     */
    public function __construct(AclOperatorInterface $aclOperator)
    {
        $this->aclOperator = $aclOperator;
    }

    /**
     * Kernel controller event handler.
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof ResourceControllerInterface
            || $controller[0] instanceof DashboardController) {
            $this->aclOperator->loadAcls();
        }
    }
}
