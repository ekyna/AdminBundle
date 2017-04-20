<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Ekyna\Bundle\ApiBundle\Action\SearchAction;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResourceSearchTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Form\Extension
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceSearchTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('search_action', SearchAction::class);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ResourceSearchType::class];
    }
}
