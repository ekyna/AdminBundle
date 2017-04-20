<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class AbstractMoveAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMoveAction extends RA\AbstractAction implements AdminActionInterface
{
    use RA\HelperTrait;
    use RA\ManagerTrait;
    use FlashTrait;

    protected function moveResource(int $movement): Response
    {
        $resource = $this->context->getResource();

        $accessor = PropertyAccess::createPropertyAccessor();
        $value = $accessor->getValue($resource, $this->options['property']) + $movement;

        if (0 <= $value) {
            $accessor->setValue($resource, $this->options['property'], $value + $movement);

            $event = $this->getManager()->update($resource);

            if (!$this->request->isXmlHttpRequest()) {
                $this->addFlashFromEvent($event);
            }
        }

        if ($parent = $this->context->getParentResource()) {
            $redirectPath = $this->generateResourcePath($parent, $this->options['parent_redirect']);
        } else {
            $redirectPath = $this->generateResourcePath($resource, $this->options['redirect']);
        }

        return $this->redirectToReferer($redirectPath);
    }

    public static function configureAction(): array
    {
        return [
            'permission' => Permission::UPDATE,
            'options'    => [
                'property'        => 'position',
                'redirect'        => ReadAction::class,
                'parent_redirect' => ReadAction::class,
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['property', 'redirect', 'parent_redirect'])
            ->setAllowedTypes('property', 'string')
            ->setAllowedTypes('redirect', 'string')
            ->setAllowedTypes('parent_redirect', 'string');
    }
}
