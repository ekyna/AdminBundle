<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Table\TableTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnexpectedValueException;

/**
 * Class ListAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ListAction extends AbstractAction implements AdminActionInterface
{
    use RA\AuthorizationTrait;
    use RA\TemplatingTrait;
    use Util\BreadcrumbTrait;
    use Util\TableTrait;

    protected const NAME = 'admin_list';

    /**
     * @inheritDoc
     */
    public function __invoke(): Response
    {
        $table = $this->createTable($this->context->getConfig()->getName(), $this->getTableType());

        $response = $table->handleRequest($this->request);

        if ($response instanceof Response) {
            return $response;
        } elseif (null !== $response) {
            throw new UnexpectedValueException('Expected instance of ' . Response::class);
        }

        /* TODO if ($this->request->isXmlHttpRequest()) {
            $modal = $this->createModal('list');
            $modal->setContent($table->createView());

            return $this->modal->render($modal);
        }*/

        $this->breadcrumbFromContext($this->context);

        $parameters = $this->buildParameters();
        $parameters['table'] = $table->createView();

        return $this
            ->render($this->options['template'], $parameters)
            ->setPrivate();
    }

    /**
     * Returns the configured table type.
     *
     * @return string
     */
    protected function getTableType(): string
    {
        if (isset($this->options['type'])) {
            return $this->options['type'];
        }

        if ($type = $this->context->getConfig()->getData('table')) {
            return $type;
        }

        throw new RuntimeException('No table type configured.');
    }

    /**
     * Builds the template parameters.
     *
     * @return array
     */
    protected function buildParameters(): array
    {
        return [
            'context' => $this->context,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::LIST,
            'route'      => [
                'name'    => 'admin_%s_list',
                'methods' => 'GET',
            ],
            'button'     => [
                'label' => 'button.list',
                'theme' => 'default',
                'icon'  => 'list',
            ],
            'options'    => [
                'template' => '@EkynaAdmin/Entity/Crud/list.html.twig',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['type', 'template'])
            ->setAllowedTypes('type', 'string')
            ->setAllowedTypes('template', 'string')
            ->setAllowedValues('type', function ($value) {
                if (is_null($value)) {
                    return true;
                }

                return is_subclass_of($value, TableTypeInterface::class);
            });
    }
}
