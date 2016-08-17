<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PermissionsType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PermissionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Ekyna\Component\Resource\Configuration\ConfigurationRegistry $registry */
        $registry = $options['registry'];

        foreach ($registry->getConfigurations() as $config) {
            $builder->add($config->getAlias(), PermissionType::class, [
                'label' => $config->getResourceLabel(true),
                'permissions' => $options['permissions'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['permissions'] = $options['permissions'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(array('registry', 'permissions'))
            ->setRequired(array('registry', 'permissions'))
            ->setAllowedTypes('registry', ConfigurationRegistry::class)
            ->setAllowedTypes('permissions', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_admin_permissions';
    }
}
