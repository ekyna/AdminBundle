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

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $child = $this->createNewFromParent($context, $resource);

        if (0 < strlen($referer = $request->headers->get('referer'))) {
            $cancelPath = $referer;
        } else {
            $cancelPath = $this->generateResourcePath($request);
        }

        $form = $this
            ->createForm($this->config->getFormType(), $child, [
                'action' => $this->generateUrl(
                    $this->config->getRoute('new_child'),
                    $context->getIdentifiers(true)
                ),
                'method' => 'POST',
                'attr' => [
                    'class' => 'form-horizontal form-with-tabs',
                ],
                'admin_mode' => true,
                '_redirect_enabled' => true,
            ])
            ->add('actions', 'form_actions', [
                'buttons' => [
                    'saveAndList' => [
                        'type' => 'submit', 'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.save_and_list',
                            'attr' => [
                                'icon' => 'list',
                            ],
                        ],
                    ],
                    'save' => [
                        'type' => 'submit', 'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.save',
                            'attr' => [
                                'icon' => 'ok',
                            ],
                        ],
                    ],
                    'cancel' => [
                        'type' => 'button', 'options' => [
                            'label' => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link' => true,
                            'attr' => [
                                'class' => 'form-cancel-btn',
                                'icon' => 'remove',
                                'href' => $cancelPath,
                            ],
                        ],
                    ],
                ],
            ])
        ;

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {

            $this->getRepository()->persistAsLastChildOf($child, $resource);

            // TODO use ResourceManager
            $event = $this->getOperator()->create($child);

            /* if ($request->isXmlHttpRequest()) {
                if ($event->hasErrors()) {
                    $errorMessages = $event->getErrors();
                    $errors = [];
                    foreach ($errorMessages as $message) {
                        $errors[] = $message->getMessage();
                    }
                    return new JsonResponse(array('error' => implode(', ', $errors)));
                }

                return new JsonResponse(array(
                    'id' => $resource->getId(),
                    'name' => (string)$resource,
                ));
            }*/

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                /** @noinspection PhpUndefinedMethodInspection */
                if ($form->get('actions')->get('saveAndList')->isClicked()) {
                    $redirectPath = $this->generateResourcePath($resource, 'list');
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    $redirectPath = $this->generateResourcePath($child);
                }
                return $this->redirect($redirectPath);
            }
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