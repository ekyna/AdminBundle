<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MailerFactoryPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MailerFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $busReference = $container
            ->getDefinition('mailer.mailer')
            ->getArgument(1);

        if (!$busReference instanceof Reference) {
            return;
        }

        $container
            ->getDefinition('ekyna_admin.factory.mailer')
            ->setArgument(4, clone $busReference);
    }
}
