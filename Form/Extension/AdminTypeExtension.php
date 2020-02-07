<?php

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AdminTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Form\Extension
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminTypeExtension extends AbstractTypeExtension
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'admin_mode'   => $this->authorizationChecker->isGranted('ROLE_ADMIN'),
                'admin_helper' => null,
            ])
            ->setAllowedTypes('admin_mode', 'bool')
            ->setAllowedTypes('admin_helper', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // TODO set admin_mode view var

        if (0 < strlen($options['admin_helper'])) {
            $view->vars['attr']['data-helper'] = $options['admin_helper'];
        }
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
