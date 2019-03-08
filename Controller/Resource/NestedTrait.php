<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NestedTrait
 * @package Ekyna\Bundle\AdminBundle\Controller\Resource
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait NestedTrait
{
    /**
     * Decrement the position.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveUpAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resource = $context->getResource();

        $this->isGranted('EDIT', $resource);

        $repo = $this->getRepository();
        $repo->moveUp($resource, 1);

        return $this->redirectToReferer($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }

    /**
     * Increment the position.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveDownAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resource = $context->getResource();

        $this->isGranted('EDIT', $resource);

        $repo = $this->getRepository();
        $repo->moveDown($resource, 1);

        return $this->redirectToReferer($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }

    /**
     * Creates a child resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newChildAction(Request $request)
    {
        $this->isGranted('CREATE');
        $isXhr = $request->isXmlHttpRequest();

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $action = $this->generateUrl(
            $this->config->getRoute('new_child'),
            $context->getIdentifiers(true)
        );

        $child = $this->createNewFromParent($context, $resource);
        $context->addResource($resourceName, $child);
        $context->addResource('parent_resource', $resource);

        $this->getOperator()->initialize($resource);

        $form = $this->createNewResourceForm($context, !$isXhr, ['action' => $action]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getRepository()->persistAsLastChildOf($child, $resource);

            // TODO use ResourceManager
            $event = $this->getOperator()->create($child);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return new JsonResponse([
                        'id'   => $child->getId(),
                        'name' => (string) $child,
                    ]);
                }

                /** @noinspection PhpUndefinedMethodInspection */
                if ($form->get('actions')->get('saveAndList')->isClicked()) {
                    $redirectPath = $this->generateResourcePath($resource, 'list');
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    $redirectPath = $this->generateResourcePath($child);
                }
                return $this->redirect($redirectPath);
            }/* TODO else {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }*/
        }

        if ($isXhr) {
            $title = $this->getTranslator()->trans(
                sprintf('%s.header.%s', $this->config->getResourceId(), 'new_child'),
                ['%name%' => (string) $resource]
            );
            $modal = $this->createModal('new_child', $title);
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars())
            ;
            return $this->get('ekyna_core.modal')->render($modal);
        }

        return $this->render(
            $this->config->getTemplate('new_child.html'),
            $context->getTemplateVars([
                'child' => $child,
                'form' => $form->createView()
            ])
        );
    }

    /**
     * Creates a new resource and configure it regarding to the parent.
     *
     * @param Context $context
     * @param object $parent
     *
     * @return object
     */
    public function createNewFromParent(Context $context, $parent)
    {
        $resource = $this->createNew($context);
        $resource->setParent($parent);
        return $resource;
    }
}
