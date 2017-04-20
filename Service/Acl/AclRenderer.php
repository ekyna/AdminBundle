<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Acl;

use Ekyna\Bundle\AdminBundle\Action\Acl as Action;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclManagerInterface;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Twig\Environment;

/**
 * Class AclRenderer
 * @package Ekyna\Bundle\AdminBundle\Service\Acl
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AclRenderer
{
    private AclManagerInterface $manager;
    private Environment         $twig;
    private ResourceHelper      $helper;

    public function __construct(AclManagerInterface $manager, Environment $twig, ResourceHelper $helper)
    {
        $this->manager = $manager;
        $this->twig = $twig;
        $this->helper = $helper;
    }

    /**
     * Renders the access control list for the given subject.
     */
    public function renderAclList(ResourceInterface $subject): string
    {
        if (!$subject instanceof AclSubjectInterface) {
            throw new UnexpectedValueException('Expected instance of ' . AclSubjectInterface::class);
        }

        $config = [
            'editable'   => $this->helper->isGranted('ROLE_SUPER_ADMIN'),
            'permission' => $this->helper->generateResourcePath($subject, Action\PermissionAction::class),
            'resource'   => $this->helper->generateResourcePath($subject, Action\ResourceAction::class),
            'namespace'  => $this->helper->generateResourcePath($subject, Action\NamespaceAction::class),
        ];

        return $this->twig->render('@EkynaAdmin/Security/acl.html.twig', [
            'config' => $config,
            'acl'    => $this->manager->getAcl($subject),
        ]);
    }
}
