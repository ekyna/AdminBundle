<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReadAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ReadAction extends RA\AbstractAction implements AdminActionInterface
{
    use RA\AuthorizationTrait;
    use RA\TemplatingTrait;
    use Util\BreadcrumbTrait;

    protected const NAME = 'admin_read';

    public function __invoke(): Response
    {
        if (null === $this->context->getResource()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $this->breadcrumbFromContext($this->context);

        return $this
            ->render($this->options['template'], $this->buildParameters())
            ->setPrivate();
    }

    /**
     * Builds the template parameters.
     */
    protected function buildParameters(): array
    {
        $config = $this->context->getConfig();

        return [
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
        ];
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_read',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'button'     => [
                'label' => 'button.show',
                'theme' => 'default',
                'icon'  => 'eye',
            ],
            'options'    => [
                'template' => '@EkynaAdmin/Entity/Crud/read.html.twig',
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['template'])
            ->setAllowedTypes('template', 'string');
    }
}
