<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * TinymceTrait
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait TinymceTrait
{
    public function tinymceAction(Request $request)
    {
        if(null === $field = $request->attributes->get('field')) {
            throw new AccessDeniedHttpException('Field parameter is mandatory.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        if(null === $resource = $context->getResource($resourceName)) {
            throw new \RuntimeException('Resource not found.');
        }

        $this->isGranted('VIEW', $resource);

        $propertyAcessor = PropertyAccess::createPropertyAccessor();
        $content = $propertyAcessor->getValue($resource, $field);

        return $this->render(
            'EkynaCoreBundle:Ui:tinymce.html.twig',
            array(
                'content' => $content
            )
        );
    }
}