<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use DateTime;
use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;

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
    public function build(View $view, $value, array $options = []): void
    {
        if ($value && !$value instanceof DateTime) {
            throw new InvalidArgumentException('Expected instance of \DateTime');
        }

        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'time'        => $options['time'],
            'date_format' => $options['date_format'],
            'time_format' => $options['time_format'],
        ]);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'time'        => true,
                'date_format' => 'short',
                'time_format' => 'short',
            ])
            ->setAllowedTypes('time', 'bool')
            ->setAllowedTypes('date_format', 'string')
            ->setAllowedTypes('time_format', 'string')
            ->setNormalizer('time_format', function (Options $options, $value) {
                if (!$options['time']) {
                    $value = 'none';
                }

                return $value;
            });
    }

    public static function getName(): string
    {
        return 'datetime';
    }
}
