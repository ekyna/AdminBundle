<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Tree;

use Ekyna\Bundle\AdminBundle\Action\AbstractFormAction;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\TreeInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreateChildAction
 * @package Ekyna\Bundle\AdminBundle\Action\Tree
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateChildAction extends AbstractFormAction
{
    use RepositoryTrait;

    private const NAME = 'admin_tree_create_child';

    private TreeInterface $parent;

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof TreeInterface) {
            throw new UnexpectedTypeException($resource, TreeInterface::class);
        }

        $child = $this->createResource();
        if (!$child instanceof TreeInterface) {
            throw new UnexpectedTypeException($child, TreeInterface::class);
        }

        $this->context->setResource($child);
        $child->setParent($resource);
        $this->parent = $resource;

        $form = $this->getForm([
            'action' => $this->generateResourcePath($resource, static::class, $this->request->query->all()),
        ]);

        if ($response = $this->handleForm($form)) {
            return $response;
        }

        if ($this->request->isXmlHttpRequest()) {
            $modal = $this->createModal(Modal::MODAL_CREATE);
            $modal
                ->setForm($form->createView())
                ->setVars($this->buildParameters());

            return $this->renderModal($modal);
        }

        $this->breadcrumbFromContext($this->context);

        $parameters = $this->buildParameters([
            'form'            => $form->createView(),
            'parent_resource' => $resource,
        ]);

        return $this->render($this->options['template'], $parameters);
    }

    protected function onPrePersist(): ?Response
    {
        /** @var NestedTreeRepository $repository */
        $repository = $this->getRepository();
        $repository->persistAsLastChildOf($this->context->getResource(), $this->parent);

        return null;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::CREATE,
            'route'      => [
                'name'     => 'admin_%s_tree_create',
                'path'     => '/create-child',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label' => 'button.new',
                'theme' => 'success',
                'icon'  => 'plus',
            ],
            'options'    => [
                'template'      => '@EkynaAdmin/Entity/Crud/create.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_default.html.twig',
            ],
        ];
    }
}
