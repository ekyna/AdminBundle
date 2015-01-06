<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Resource;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TinymceTrait
 * @package Ekyna\Bundle\AdminBundle\Controller\Resource
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait TinymceTrait
{
    /**
     * Display the "tinymce" content.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tinymceAction(Request $request)
    {
        if(null === $field = $request->attributes->get('field')) {
            throw new AccessDeniedHttpException('Field parameter is mandatory.');
        }

        $context = $this->loadContext($request);

        if(null === $resource = $context->getResource()) {
            throw new \RuntimeException('Resource not found.');
        }

        $this->isGranted('VIEW', $resource);

        $propertyAcessor = PropertyAccess::createPropertyAccessor();
        $content = $propertyAcessor->getValue($resource, $field);

        return $this->render('EkynaCoreBundle:Ui:tinymce.html.twig', array(
            'content' => $content
        ));
    }
}