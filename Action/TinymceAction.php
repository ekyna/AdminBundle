<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Route;

/**
 * Class TinymceAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TinymceAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use TemplatingTrait;

    protected const NAME = 'admin_tinymce';

    public function __invoke(): Response
    {
        if (empty($field = $this->request->attributes->get('field'))) {
            throw new NotFoundHttpException('Field parameter is mandatory.');
        }

        if (!$resource = $this->context->getResource()) {
            throw new NotFoundHttpException('Resource not found.');
        }

        if ($resource instanceof TranslatableInterface) {
            if ($locale = $this->request->getLocale()) {
                $resource->translate($locale);
            }
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $content  = $accessor->getValue($resource, $field);

        return $this->render($this->options['template'], [
            'content' => $content,
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_tinymce',
                'path'     => '/tinymce/{field}',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'options'    => [
                'template' => '@EkynaUi/Tinymce/render.html.twig',
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('template')
            ->setAllowedTypes('template', 'string');
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'field' => '\w+',
        ]);
    }
}
