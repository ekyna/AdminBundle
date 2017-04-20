<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Decimal\Decimal;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NumberType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NumberType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if ($value instanceof Decimal) {
            $view->vars['value'] = $value->toString();
        }

        $view->vars['append'] = $options['append'];
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('append', '')
            ->setAllowedTypes('append', 'string');
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'number';
    }
}
