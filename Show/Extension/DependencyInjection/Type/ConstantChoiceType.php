<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_slice;
use function call_user_func;
use function count;
use function end;
use function implode;
use function is_array;
use function is_subclass_of;
use function reset;
use function sprintf;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractType
{
    private TranslatorInterface $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if (empty($value)) {
            $view->vars['value'] = '';

            return;
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $constants = [];
        foreach ($value as $const) {
            /**
             * @var TranslatableInterface $label
             * @see ConstantsInterface::getLabel()
             */
            $label = call_user_func($options['class'] . '::getLabel', $const);
            $label = $label->trans($this->translator);

            if (!$options['theme']) {
                $constants[] = $label;

                continue;
            }

            $theme = call_user_func($options['class'] . '::getTheme', $const);

            $constants[] = sprintf('<span class="label label-%s">%s</span>', $theme, $label);
        }

        if (0 === count($constants)) {
            $view->vars['value'] = '';

            return;
        }

        if (1 === count($constants)) {
            $view->vars['value'] = reset($constants);

            return;
        }

        if ($options['theme']) {
            $view->vars['value'] = implode('&nbsp;', $constants);

            return;
        }

        $view->vars['value'] = sprintf(
            '%s %s %s',
            implode(', ', array_slice($constants, 0, count($constants) - 1)),
            $this->translator->trans('value.and', [], 'EkynaUi'),
            end($constants)
        );
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('class')
            ->setDefaults([
                'label'              => 'field.status',
                'label_trans_domain' => 'EkynaUi',
                'theme'              => false,
            ])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('theme', 'bool')
            ->setAllowedValues('class', function ($class) {
                return is_subclass_of($class, ConstantsInterface::class);
            });
    }

    public function getWidgetPrefix(): ?string
    {
        return 'default';
    }

    public static function getName(): string
    {
        return 'constant_choice';
    }
}
