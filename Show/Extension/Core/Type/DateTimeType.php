<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DateTimeType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DateTimeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        if ($value && !$value instanceof \DateTime) {
            throw new InvalidArgumentException("Expected instance of \DateTime");
        }

        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'time'        => $options['time'],
            'date_format' => $options['date_format'],
            'time_format' => $options['time_format'],
            'locale'      => $options['locale'],
            'timezone'    => $options['timezone'],
            'format'      => $options['format'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'time'        => true,
                'date_format' => 'short',
                'time_format' => 'short',
                'locale'      => null,
                'timezone'    => null,
                'format'      => null,
            ])
            ->setAllowedTypes('time', 'bool')
            ->setAllowedTypes('date_format', 'string')
            ->setAllowedTypes('time_format', 'string')
            ->setAllowedTypes('locale', ['null', 'string'])
            ->setAllowedTypes('timezone', ['null', 'string'])
            ->setAllowedTypes('format', ['null', 'string'])
            ->setNormalizer('time_format', function (Options $options, $value) {
                if (!$options['time']) {
                    $value = 'none';
                }

                return $value;
            });
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'datetime';
    }
}