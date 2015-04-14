<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceType extends AbstractType
{
    /**
     * @var ConfigurationRegistry
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
     * @param AclOperatorInterface $aclOperator
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

        if ($options['new_route']) {
            $view->vars['new_route'] = $options['new_route'];
            $view->vars['new_route_params'] = $options['new_route_params'];
        } elseif ($options['allow_new'] && $this->aclOperator->isAccessGranted($options['class'], 'CREATE')) {
            $view->vars['new_route'] = $configuration->getRoute('new');
            $view->vars['new_route_params'] = $options['new_route_params']; // TODO
        }

        if ($options['new_route']) {
            $view->vars['list_route'] = $options['list_route'];
            $view->vars['list_route_params'] = $options['list_route_params'];
        } elseif ($options['allow_list'] && $this->aclOperator->isAccessGranted($options['class'], 'VIEW')) {
            $view->vars['list_route'] = $configuration->getRoute('list');
            $view->vars['list_route_params'] = array_merge(
                $options['list_route_params'],
                array('selector' => 1, 'multiple' => $options['multiple']) // TODO
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'new_route'         => null,
                'new_route_params'  => array(),
                'list_route'        => null,
                'list_route_params' => null,
                'allow_new'         => false,
                'allow_list'        => false,
            ))
            ->setAllowedTypes(array(
                'new_route'         => array('null', 'string'),
                'new_route_params'  => 'array',
                'list_route'        => array('null', 'string'),
                'list_route_params' => 'array',
                'allow_new'         => 'bool',
                'allow_list'        => 'bool',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_resource';
    }
}
