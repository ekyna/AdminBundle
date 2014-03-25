<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;

/**
 * NestedTrait
 */
trait NestedTrait
{
    public function moveUpAction(Request $request)
    {
        $resource = $this->findResourceOrThrowException();

        $this->isGranted('EDIT', $resource);

        $repo = $this->getRepository();
        $repo->moveUp($resource, 1);

        return $this->redirect($this->generateUrl($this->configuration->getRoute('list')));
    }

    public function moveDownAction(Request $request)
    {
        $resource = $this->findResourceOrThrowException();

        $this->isGranted('EDIT', $resource);

        $repo = $this->getRepository();
        $repo->moveDown($resource, 1);

        return $this->redirect($this->generateUrl($this->configuration->getRoute('list')));
    }

    public function newChildAction(Request $request)
    {
        $this->isGranted('CREATE');

        $parent = $this->findResourceOrThrowException();

        $resourceName = $this->getResourceName();
        $resource = $this->createNew();
        $resource->setParent($parent);

        $form = $this->createForm($this->configuration->getFormType(), $resource);

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {

            $em = $this->getManager();
            $repo = $this->getRepository();
            
            $repo->persistAsLastChildOf($resource, $parent);
            $em->flush();

            $this->addFlash('La resource a été créé avec succès.', 'success');

            return $this->redirect(
                $this->generateUrl(
                    $this->configuration->getRoute('show'),
                    array(
                        sprintf('%sId', $resourceName) => $resource->getId()
                    )
                )
            );
        }

        return $this->render(
            $this->configuration->getTemplate('new_child.html'),
            array(
                $resourceName => $resource,
                'parent' => $parent,
                'form' => $form->createView()
            )
        );
    }
}