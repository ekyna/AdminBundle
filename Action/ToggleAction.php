<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Route;

use function implode;
use function in_array;

/**
 * Class ToggleAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ToggleAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use FlashTrait;

    protected const NAME = 'admin_toggle';


    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        $property = $this->request->attributes->get('property');

        if (!in_array($property, (array)$this->options['properties'], true)) {
            return new Response('Unsupported property', Response::HTTP_BAD_REQUEST);
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $value = $accessor->getValue($resource, $property);
        $accessor->setValue($resource, $property, !$value);

        $event = $this->getManager()->update($resource);

        if (!$this->request->isXmlHttpRequest()) {
            $this->addFlashFromEvent($event);
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
            'name'       => static::NAME,
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_toggle',
                'path'     => '/toggle/{property}',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'options'    => [
                'properties'      => null,
                'redirect'        => ReadAction::class,
                'parent_redirect' => ReadAction::class,
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('properties')
            ->setDefined(['redirect', 'parent_redirect'])
            ->setAllowedTypes('properties', ['string', 'string[]'])
            ->setAllowedTypes('redirect', 'string')
            ->setAllowedTypes('parent_redirect', 'string');
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $properties = $options['properties'];

        if (is_array($properties)) {
            $properties = implode('|', $properties);
        }

        $route->addRequirements([
            'property' => $properties,
        ]);
    }
}
