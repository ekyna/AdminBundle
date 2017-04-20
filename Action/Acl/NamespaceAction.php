<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Acl;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NamespaceAction
 * @package Ekyna\Bundle\AdminBundle\Action\Acl
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NamespaceAction extends AbstractAction
{
    protected const NAME = 'admin_acl_namespace';

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
        $value     = $this->request->request->getBoolean('value');

        $config = $this->manager->getNamespaceRegistry()->find($namespace);

        if ($this->manager->setNamespace($subject, $namespace, $value)) {
            $this->manager->flush();
        }

        $data = [
            'inheritance' => null !== $subject->getAclParentSubject(),
            'namespaces'  => [
                $this->manager->getNamespace($subject, $config),
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
                'name'     => 'admin_%s_acl_namespace',
                'path'     => '/acl/namespace',
                'resource' => true,
                'methods'  => 'POST',
            ],
        ];
    }
}
