<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function call_user_func;
use function is_subclass_of;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractColumnType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        /**
         * @var TranslatableInterface $label
         * @see ConstantsInterface::getLabel()
         */
        $label = call_user_func($options['class'] . '::getLabel', $view->vars['value']);
        $label = $label->trans($this->translator);

        if (!$options['theme']) {
            $view->vars['value'] = $label;

            return;
        }

        /** @see ConstantsInterface::getTheme() */
        $theme = call_user_func($options['class'] . '::getTheme', $view->vars['value']);

        $view->vars['value'] = sprintf('<span class="label label-%s">%s</span>', $theme, $label);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('class')
            ->setDefaults([
                'label' => t('field.status', [], 'EkynaUi'),
                'theme' => false,
            ])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('theme', 'bool')
            ->setAllowedValues('class', function ($class) {
                return is_subclass_of($class, ConstantsInterface::class);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
