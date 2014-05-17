<?php

namespace Ekyna\Bundle\AdminBundle\Security;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Bundle\AdminBundle\Acl\AclEditor;

/**
 * ResourceAccessVoter.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceAccessVoter implements ResourceAccessVoterInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Acl\AclEditor
     */
    private $aclEditor;


    /**
     * Constructor.
     * 
     * @param SecurityContextInterface $securityContext
     * @param ConfigurationRegistry    $configurationRegistry
     * @param AclEditor                $aclEditor
     */
    public function __construct(
        SecurityContextInterface $securityContext, 
        ConfigurationRegistry    $configurationRegistry,
        AclEditor                $aclEditor
    ) {
        $this->securityContext       = $securityContext;
        $this->configurationRegistry = $configurationRegistry;
        $this->aclEditor             = $aclEditor;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccessGranted($resource, $permission)
    {
        if (! $this->aclEditor->getPermissionMap()->contains($permission)) {
            throw new \InvalidArgumentException(sprintf('Unknown permission "%s".', $permission));
        }

        $config = $this->configurationRegistry->findConfiguration($resource);

        return $this->securityContext->isGranted($permission, $config->getObjectIdentity());
    }
}
