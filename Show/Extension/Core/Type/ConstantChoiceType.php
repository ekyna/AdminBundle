<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


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
    public function build(View $view, $value, array $options = [])
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
            $label = $this->translator->trans(
                call_user_func($options['class'] . '::getLabel', $const)
            );

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
            $this->translator->trans('ekyna_core.value.and'),
            end($constants)
        );
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('class')
            ->setDefaults([
                'label' => 'ekyna_core.field.status',
                'theme' => false,
            ])
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('theme', 'bool')
            ->setAllowedValues('class', function ($class) {
                return is_subclass_of($class, ConstantsInterface::class);
            });
    }
}
