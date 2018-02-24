<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceType extends AbstractType
{
    /**
     * @var \Ekyna\Component\Resource\Configuration\ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var AclOperatorInterface
     */
    private $aclOperator;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry $configurationRegistry
     * @param AclOperatorInterface  $aclOperator
     */
    public function __construct(ConfigurationRegistry $configurationRegistry, AclOperatorInterface $aclOperator)
    {
        $this->configurationRegistry = $configurationRegistry;
        $this->aclOperator = $aclOperator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $configuration = $this->configurationRegistry->findConfiguration($options['class']);

        if ($options['disabled']) {
            return;
        }

        if ($options['new_route']) {
            $view->vars['new_route'] = $options['new_route'];
            $view->vars['new_route_params'] = $options['new_route_params'];
        } elseif ($options['allow_new'] && $this->aclOperator->isAccessGranted($options['class'], 'CREATE')) {
            $view->vars['new_route'] = $configuration->getRoute('new');
            $view->vars['new_route_params'] = $options['new_route_params']; // TODO
        }

        if ($options['list_route']) {
            $view->vars['list_route'] = $options['list_route'];
            $view->vars['list_route_params'] = $options['list_route_params'];
        } elseif ($options['allow_list'] && $this->aclOperator->isAccessGranted($options['class'], 'VIEW')) {
            $view->vars['list_route'] = $configuration->getRoute('list');
            $view->vars['list_route_params'] = array_merge(
                $options['list_route_params'],
                ['selector' => 1, 'multiple' => $options['multiple']] // TODO
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'new_route'         => null,
                'new_route_params'  => [],
                'list_route'        => null,
                'list_route_params' => [],
                'allow_new'         => false,
                'allow_list'        => false,
            ])
            ->setAllowedTypes('new_route', ['null', 'string'])
            ->setAllowedTypes('new_route_params', 'array')
            ->setAllowedTypes('list_route', ['null', 'string'])
            ->setAllowedTypes('list_route_params', 'array')
            ->setAllowedTypes('allow_new', 'bool')
            ->setAllowedTypes('allow_list', 'bool')
            ->setNormalizer('placeholder', function(Options $options, $value) {
                if (empty($value) && !$options['required']) {
                    $value = 'ekyna_core.value.none';
                }

                return $value;
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_resource';
    }
}
