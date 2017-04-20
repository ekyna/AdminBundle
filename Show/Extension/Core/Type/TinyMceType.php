<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Action\TinymceAction;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;

/**
 * Class TinyMceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TinyMceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        if ($value instanceof TranslationInterface) {
            $value = $value->getTranslatable();
        }

        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'height'       => $options['height'],
            'action'       => $options['action'],
            'field'        => $options['field'],
            'route'        => $options['route'],
            'route_params' => $options['route_params'],
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'height'       => 250,
                'action'       => TinymceAction::class,
                'field'        => null,
                'route'        => null,
                'route_params' => [],
            ])
            ->setAllowedTypes('height', 'int')
            ->setAllowedTypes('action', ['null', 'string'])
            ->setAllowedTypes('field', ['null', 'string'])
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('route_params', 'array');
    }

    public static function getName(): string
    {
        return 'tinymce';
    }
}
