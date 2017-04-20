<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TextType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LinkType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        $view->vars['path'] = $options['path'];
        $view->vars['target'] = $options['target'];
        $view->vars['trans_params'] = $options['trans_params'];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('path')
            ->setDefaults([
                'target'       => null,
                'trans_params' => [],
            ])
            ->setAllowedTypes('path', 'string')
            ->setAllowedTypes('trans_params', 'array')
            ->setAllowedTypes('target', ['null', 'string']);
    }

    public static function getName(): string
    {
        return 'link';
    }
}
