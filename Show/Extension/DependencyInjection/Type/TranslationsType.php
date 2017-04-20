<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Contracts\Translation\TranslatableInterface;

use function array_replace;
use function base64_encode;
use function is_array;
use function preg_replace;
use function random_bytes;

/**
 * Class TranslationsType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslationsType extends AbstractType
{
    /** @var array<string> */
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif (!is_array($value)) {
            $value = [];
        }

        $translations = [];
        foreach ($this->locales as $locale) {
            $translations[$locale] = $value[$locale] ?? null;
        }

        parent::build($view, $translations, $options);

        $prefix = $options['prefix'] ?? $options['id'] ?: 'translations';

        /** @noinspection PhpUnhandledExceptionInspection */
        $name = $prefix . '_' . preg_replace('~[^A-Za-z0-9]+~', '', base64_encode(random_bytes(3)));

        $view->vars = array_replace($view->vars, [
            'locales' => $this->locales,
            'prefix'  => $prefix,
            'name'    => $name,
            'fields'  => $options['fields'],
        ]);
    }

    public function getRowPrefix(): string
    {
        return 'translations';
    }

    public static function getName(): string
    {
        return 'translations';
    }

    protected function configureOptions(OptionsResolver $resolver): void
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
            ->setAllowedTypes('label', ['string', TranslatableInterface::class])
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
            ->setNormalizer('fields',
                function (Options $options, $value)
                use ($fieldResolver) {
                    $normalized = [];
                    foreach ($value as $property => $config) {
                        $normalized[$property] = $fieldResolver->resolve(array_replace([
                            'property_path' => $property,
                        ], $config));
                    }

                    return $normalized;
                }
            );
    }
}
