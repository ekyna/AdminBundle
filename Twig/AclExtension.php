<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Service\Acl\AclRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AclExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AclExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getFunctions(): array
    {
        return [
            /** @see AclRenderer::renderAclList() */
            new TwigFunction(
                'resource_acl_list',
                [AclRenderer::class, 'renderAclList'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
