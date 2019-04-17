<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TranslationsType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslationsType extends AbstractType
{
    /**
     * @var array
     */
    private $locales;


    /**
     * Constructor.
     *
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        parent::build($view, $value, $options);

        $prefix = $options['prefix'] ?? $options['id'] ?: 'translations';

        $view->vars = array_replace($view->vars, [
            'value'   => $value,
            'locales' => $this->locales,
            'prefix'  => $prefix,
            'name'    => $prefix . '_' . preg_replace('~[^A-Za-z0-9]+~', '', base64_encode(random_bytes(3))),
            'fields'  => $options['fields'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getRowPrefix()
    {
        return 'translations';
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'translations';
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $fieldResolver = new OptionsResolver();
        $fieldResolver
            ->setDefaults([
                'label'         => null,
                'type'          => 'text',
                'options'       => [],
                'property_path' => null,
                'value'         => null,
            ])
            ->setAllowedTypes('label', 'string')
            ->setAllowedTypes('type', 'string')
            ->setAllowedTypes('options', 'array')
            ->setAllowedTypes('property_path', ['null', 'string']);

        $resolver
            ->setDefaults([
                'label_col'  => 0,
                'widget_col' => 12,
                'fields'     => null,
                'prefix'     => null,
            ])
            ->setAllowedTypes('fields', 'array')
            ->setAllowedTypes('prefix', ['null', 'string'])
            ->setNormalizer('fields', function (Options $options, $value) use ($fieldResolver) {
                $normalized = [];
                foreach ($value as $property => $config) {
                    $normalized[$property] = $fieldResolver->resolve(array_replace([
                        'property_path' => $property,
                    ], $config));
                }

                return $normalized;
            });
    }
}