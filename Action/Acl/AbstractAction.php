<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Acl;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction as BaseAction;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AbstractAction
 * @package Ekyna\Bundle\AdminBundle\Action\Acl
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAction extends BaseAction implements AdminActionInterface // TODO ApiActionInterface
{
    protected AclManagerInterface $manager;
    protected AuthorizationCheckerInterface $authorization;


    /**
     * Constructor.
     *
     * @param AclManagerInterface           $manager
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(AclManagerInterface $manager, AuthorizationCheckerInterface $authorization)
    {
        $this->manager = $manager;
        $this->authorization = $authorization;
    }

    /**
     * Asserts the use has super admin privilege.
     */
    protected function assertSuperAdmin(): void
    {
        if (!$this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException();
        }
    }
}
