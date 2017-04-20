<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;

/**
 * Class UrlType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UrlType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'value'  => (string)$value,
            'target' => $options['target'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('target', null)
            ->setAllowedTypes('target', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'url';
    }
}
