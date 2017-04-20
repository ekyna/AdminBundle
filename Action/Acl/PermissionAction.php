<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Acl;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PermissionAction
 * @package Ekyna\Bundle\AdminBundle\Action\Acl
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PermissionAction extends AbstractAction
{
    protected const NAME = 'admin_acl_permission';

    /**
     * @inheritDoc
     */
    public function __invoke(): Response
    {
        $this->assertSuperAdmin();

        $subject = $this->context->getResource();

        if (!$subject instanceof AclSubjectInterface) {
            throw new RuntimeException('Expected instance of ' . AclSubjectInterface::class);
        }

        $namespace = $this->request->request->get('namespace');
        $resource = $this->request->request->get('resource');
        $permission = $this->request->request->get('permission');
        $value = $this->request->request->getBoolean('value');

        if ($this->manager->setPermission($subject, $namespace, $resource, $permission, $value)) {
            $this->manager->flush();
        }

        $data = [
            'inheritance' => null !== $subject->getAclParentSubject(),
            'namespaces'  => [
                [
                    'name'      => $namespace,
                    'resources' => [
                        [
                            'name'        => $resource,
                            'permissions' => [
                                $this->manager->getPermission($subject, $namespace, $resource, $permission),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return new JsonResponse($data);
    }

    /**
     * @inheritDoc
     */
    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_acl_permission',
                'path'     => '/acl/permission',
                'resource' => true,
                'methods'  => 'POST',
            ],
        ];
    }
}
