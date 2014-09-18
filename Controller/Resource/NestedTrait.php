<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;
use Ekyna\Bundle\AdminBundle\Controller\Context;

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
     */
    public function moveUpAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $repo = $this->getRepository();
        $repo->moveUp($resource, 1);

        return $this->redirect($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }

    /**
     * Increment the position.
     * 
     * @param Request $request
     */
    public function moveDownAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $repo = $this->getRepository();
        $repo->moveDown($resource, 1);

        return $this->redirect($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }

    /**
     * Creates a child resource.
     * 
     * @param Request $request
     */
    public function newChildAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $child = $this->createNewFromParent($context, $resource);

        $form = $this->createForm($this->config->getFormType(), $child, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $this->generateUrl(
                    $this->config->getRoute('show'),
                    $context->getIdentifiers(true)
                ),
            ),
        ));

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {

            $em = $this->getManager();
            $repo = $this->getRepository();
            
            $repo->persistAsLastChildOf($child, $resource);
            $em->flush();

            $this->addFlash('La resource a été créé avec succès.', 'success');

            if (null !== $redirectPath = $form->get('_redirect')->getData()) {
                return $this->redirect($redirectPath);
            }

            return $this->redirect(
                $this->generateUrl(
                    $this->config->getRoute('show'),
                    array_merge($context->getIdentifiers(), array(
                        sprintf('%sId', $resourceName) => $child->getId()
                    ))
                )
            );
        }

        return $this->render(
            $this->config->getTemplate('new_child.html'),
            $context->getTemplateVars(array(
                'child' => $child,
                'form' => $form->createView()
            ))
        );
    }

    /**
     * Creates a new resource and configure it regarding to the parent.
     * 
     * @param Context $resource
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