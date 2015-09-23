<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait SortableTrait
 * @package Ekyna\Bundle\AdminBundle\Controller\Resource
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait SortableTrait
{
    /**
     * Move up the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveUpAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resource = $context->getResource();

        $this->isGranted('EDIT', $resource);

        $this->move($resource, -1);

        return $this->redirectToReferer($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }

    /**
     * Move down the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveDownAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resource = $context->getResource();

        $this->isGranted('EDIT', $resource);

        $this->move($resource, 1);

        return $this->redirectToReferer($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }

    /**
     * Move the resource.
     *
     * @param $resource
     * @param $movement
     */
    protected function move($resource, $movement)
    {
        $resource->setPosition($resource->getPosition() + $movement);

        // TODO use ResourceManager
        $event = $this->getOperator()->update($resource);
        $event->toFlashes($this->getFlashBag());
    }
}
