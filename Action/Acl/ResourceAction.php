<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Acl;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResourceAction
 * @package Ekyna\Bundle\AdminBundle\Action\Acl
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceAction extends AbstractAction
{
    protected const NAME = 'admin_acl_resource';

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
        $resource  = $this->request->request->get('resource');
        $value     = $this->request->request->getBoolean('value');

        $config = $this->manager->getResourceRegistry()->find($namespace . '.' . $resource);

        if ($this->manager->setResource($subject, $namespace, $resource, $value)) {
            $this->manager->flush();
        }

        $data = [
            'inheritance' => null !== $subject->getAclParentSubject(),
            'namespaces'  => [
                [
                    'name'      => $namespace,
                    'resources' => [
                        $this->manager->getResource($subject, $config),
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
                'name'     => 'admin_%s_acl_resource',
                'path'     => '/acl/resource',
                'resource' => true,
                'methods'  => 'POST',
            ],
        ];
    }
}
