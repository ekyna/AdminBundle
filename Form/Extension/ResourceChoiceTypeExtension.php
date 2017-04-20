<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Ekyna\Bundle\AdminBundle\Action\CreateAction;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;

/**
 * Class ResourceChoiceTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Form\Extension
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceChoiceTypeExtension extends AbstractTypeExtension
{
    private ResourceHelper $helper;


    public function __construct(ResourceHelper $helper)
    {
        $this->helper = $helper;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['disabled']) {
            return;
        }

        if (
            !isset($options['new_route'])
            && $options['allow_new']
            && $this->helper->isGranted(CreateAction::class, $options['class'])
        ) {
            $view->vars['new_route'] = $this->helper->getRoute($options['class'], CreateAction::class);
            $view->vars['new_route_params'] = $options['new_route_params']; // TODO (parent context)
        }

        if (
            !isset($options['list_route'])
            && $options['allow_list']
            && $this->helper->isGranted(ListAction::class, $options['class'])
        ) {
            $view->vars['list_route'] = $this->helper->getRoute($options['class'], ListAction::class);
            $view->vars['list_route_params'] = array_merge(
                $options['list_route_params'],
                ['selector' => 1, 'multiple' => $options['multiple']] // TODO (parent context)
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'allow_new'  => false,
                'allow_list' => false,
            ])
            ->setAllowedTypes('allow_new', 'bool')
            ->setAllowedTypes('allow_list', 'bool');
    }

    public static function getExtendedTypes(): iterable
    {
        return [ResourceChoiceType::class];
    }
}
