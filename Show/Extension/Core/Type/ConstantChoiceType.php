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

        $view->vars['value'] = $label = $this->translator->trans(
            call_user_func($options['class'] . '::getLabel', $value)
        );

        if (!$options['theme']) {
            return;
        }

        $theme = call_user_func($options['class'] . '::getTheme', $value);

        $view->vars['value'] = sprintf('<span class="label label-%s">%s</span>', $theme, $label);
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
