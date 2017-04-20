<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Tree;

use Ekyna\Bundle\AdminBundle\Action\Acl\AbstractAction;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractMoveAction
 * @package Ekyna\Bundle\AdminBundle\Action\Tree
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMoveAction extends AbstractAction
{
    use HelperTrait;
    use RepositoryTrait;


    /**
     * Redirects after tree move.
     *
     * @return RedirectResponse
     */
    protected function redirectToRead(): RedirectResponse
    {
        if ($parent = $this->context->getParentResource()) {
            $redirectPath = $this->generateResourcePath($parent, $this->options['parent_redirect']);
        } else {
            $redirectPath = $this->generateResourcePath($this->context->getResource(), $this->options['redirect']);
        }

        return $this->redirectToReferer($redirectPath);
    }

    /**
     * @inheritDoc
     */
    public static function configureAction(): array
    {
        return [
            'permission' => Permission::UPDATE,
            'options'    => [
                'redirect'        => ReadAction::class,
                'parent_redirect' => ReadAction::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['redirect', 'parent_redirect'])
            ->setAllowedTypes('redirect', 'string')
            ->setAllowedTypes('parent_redirect', 'string');
    }
}
