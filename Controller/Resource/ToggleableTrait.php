<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Trait ToggleableTrait
 * @package Ekyna\Bundle\AdminBundle\Controller\Resource
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait ToggleableTrait
{
    /**
     * Toggle the resource "enabled" (boolean) field.
     *
     * @param Request $request
     */
    public function toggleAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        if (null !== $field = $request->attributes->get('field')) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $value = $accessor->getValue($resource, $field);
            $accessor->setValue($resource, $field, !$value);

            // TODO use ResourceManager
            $event = $this->getOperator()->update($resource);
            $event->toFlashes($this->getFlashBag());
        }

        return $this->redirectToReferer($this->generateUrl(
            $this->config->getRoute('list'),
            $context->getIdentifiers()
        ));
    }
}
