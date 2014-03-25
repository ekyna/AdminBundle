<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * TinymceTrait
 */
trait TinymceTrait
{
    public function tinymceAction(Request $request)
    {
        if(null === $field = $request->attributes->get('field')) {
            throw new AccessDeniedHttpException('Field parameter is mandatory.');
        }
    
        $resourceName = $this->getResourceName();
        $resource = $this->findResourceOrThrowException();
    
        $this->isGranted('VIEW', $resource);
    
        $propertyAcessor = PropertyAccess::createPropertyAccessor();
        $content = $propertyAcessor->getValue($resource, $field);
    
        return $this->render(
            'EkynaAdminBundle:Ui:tinymce.html.twig',
            array(
                'content' => $content
            )
        );
    }
}