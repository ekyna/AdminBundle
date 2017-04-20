<?php

declare(strict_types=1);

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
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'admin_mode'   => $this->authorizationChecker->isGranted('ROLE_ADMIN'),
                'admin_helper' => null,
            ])
            ->setAllowedTypes('admin_mode', 'bool')
            ->setAllowedTypes('admin_helper', ['null', 'string']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // TODO set admin_mode view var

        if (!empty($options['admin_helper'])) {
            $view->vars['attr']['data-helper'] = $options['admin_helper'];
        }
    }

    public static function getExtendedTypes(): array
    {
        return [FormType::class];
    }
}
