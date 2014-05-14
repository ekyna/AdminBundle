<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Pool\Configuration;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * ResourceController
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceController extends Controller
{
    /**
     * Parent resource controller
     *
     * @var ResourceController
     */
    protected $parent;

    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function homeAction(Request $request)
    {
        return $this->redirect($this->generateUrl($this->config->getRoute('list')));
    }

    public function listAction(Request $request)
    {
        $this->isGranted('VIEW');

        $context = $this->loadContext($request);

        $table = $this->get('table.factory')
            ->createBuilder($this->config->getTableType())
            ->getTable($this->config->getId());

        $resources = $this->get('table.generator')->generateView($table);

        return $this->render(
            $this->config->getTemplate('list.html'),
            $context->getTemplateVars(array(
                $this->getResourcePluralName() => $resources
            ))
        );
    }

    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resource = $this->createNew($context);
        $resourceName = $this->config->getResourceName();

        if ($this->hasParent()) {
            $cancelPath = $this->generateUrl(
                $this->getParent()->getConfiguration()->getRoute('show'),
                $context->getIdentifiers()
            );
        } else {
            $cancelPath = $this->generateUrl($this->config->getRoute('list'));
        }

        $form = $this->createForm($this->config->getFormType(), $resource, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
            ),
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->persist($resource, true);

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(array(
                    'id' => $resource->getId(),
                    'name' => (string) $resource,
                ));
                /*$serializer = $this->container->get('jms_serializer');
                $response = new Response($serializer->serialize($resource, 'json'));
                $response->headers->set('Content-Type', 'application/json');
                return $response;*/
            }

            $this->addFlash('La resource a été créé avec succès.', 'success');

            if (null !== $redirectPath = $form->get('_redirect')->getData()) {
                return $this->redirect($redirectPath);
            }

            return $this->redirect(
                $this->generateUrl(
                    $this->config->getRoute('show'),
                    array_merge($context->getIdentifiers(), array(
                        sprintf('%sId', $resourceName) => $resource->getId()
                    ))
                )
            );
        } elseif ($request->getMethod() === 'POST' && $request->isXmlHttpRequest()) {
            return new JsonResponse(array('error' => $form->getErrors()));
        }

        $format = 'html';
        if ($request->isXmlHttpRequest()) {
            $format = 'xml';
        } else {
            $this->appendBreadcrumb(
                sprintf('%s-new', $resourceName),
            	'ekyna_core.button.create'
            );
        }

        return $this->render(
            $this->config->getTemplate('new.' . $format),
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        );
    }

    public function showAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        $this->isGranted('VIEW', $context->getResource($resourceName));

        return $this->render(
            $this->config->getTemplate('show.html'),
            $context->getTemplateVars()
        );
    }

    public function editAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $form = $this->createForm($this->config->getFormType(), $resource, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $this->generateUrl(
                    $this->config->getRoute('show'),
                    $context->getIdentifiers(true)
                ),
            ),
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->persist($resource);

            $this->addFlash('La resource a été modifiée avec succès.', 'success');

            if (null !== $redirectPath = $form->get('_redirect')->getData()) {
                return $this->redirect($redirectPath);
            }

            return $this->redirect(
                $this->generateUrl(
                    $this->config->getRoute('show'),
                    $context->getIdentifiers(true)
                )
            );
        }

        $this->appendBreadcrumb(
            sprintf('%s-edit', $resourceName),
            'ekyna_core.button.edit'
        );

        return $this->render(
            $this->config->getTemplate('edit.html'),
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        );
    }

    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('DELETE', $resource);

        $form = $this->createConfirmationForm($context);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->remove($resource);

            $this->addFlash('La resource a été supprimée avec succès.', 'success');

            return $this->redirect(
                $this->generateUrl(
                    $this->config->getRoute('list'),
                    $context->getIdentifiers()
                )
            );
        }

        $this->appendBreadcrumb(
            sprintf('%s-remove', $resourceName),
            'ekyna_core.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        );
    }

    /**
     * Returns true if controller has a parent controller
     *
     * @return boolean
     */
    public function hasParent()
    {
        return (null !== $this->config->getParentId());
    }

    /**
     * Returns the controller configuration
     * 
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * Returns parent configuration
     *
     * @return ResourceController
     * 
     * @throws RuntimeException
     */
    public function getParent()
    {
        if (null === $this->parent && $this->hasParent()) {
            $parentId = $this->config->getParentControllerId();
            if (!$this->container->has($parentId)) {
                throw new \RuntimeException('Parent resource controller &laquo; ' . $parentId . ' &raquo; does not exists.');
            }
            $this->parent = $this->container->get($parentId);
        }

        return $this->parent;
    }

    /**
     * Creates (or fill) the context for the given request
     * 
     * @param Request $request
     * @param Context $context
     * 
     * @return Context
     */
    public function loadContext(Request $request, Context $context = null)
    {
        $resourceName = $this->config->getResourceName();
        if (null === $context) {
            $context = new Context($resourceName);
        }

        if ($this->hasParent()) {
            $this->getParent()->loadContext($request, $context);
        }

        if (!$request->isXmlHttpRequest()) {
            $listRoute = $this->config->getRoute('list');
            if(null === $this->get('router')->getRouteCollection()->get($listRoute)) {
                $listRoute = null;
            }
            $this->appendBreadcrumb(
                sprintf('%s-list', $resourceName),
                $this->config->getResourceLabel(true),
                $listRoute,
                $context->getIdentifiers()
            );
        }

        if ($request->attributes->has($resourceName.'Id')) {
            $resource = $this->findResourceOrThrowException(array('id' => $request->attributes->get($resourceName.'Id')));
            $context->addResource($resourceName, $resource);
            if (!$request->isXmlHttpRequest()) {
                $this->appendBreadcrumb(
                    sprintf('%s-%s', $resourceName, $resource->getId()),
                    $resource,
                    $this->config->getRoute('show'),
                    $context->getIdentifiers(true)
                );
            }
        }

        return $context;
    }

    /**
     * Finds a resource or throw a not found exception
     * 
     * @param array $criteria
     * 
     * @throws NotFoundHttpException
     * 
     * @return Object|NULL
     */
    protected function findResourceOrThrowException(array $criteria)
    {
        if (null === $resource = $this->getRepository()->findOneBy($criteria)) {
            throw new NotFoundHttpException('Resource introuvable');
        }
        return $resource;
    }

    /**
     * Returns the resource plural name
     * 
     * @return string
     */
    protected function getResourcePluralName()
    {
        return Inflector::pluralize($this->config->getResourceName());
    }

    /**
     * Checks if the attributes are granted against the current token.
     *
     * @param mixed      $attributes
     * @param mixed|null $object
     * 
     * @throws AuthenticationCredentialsNotFoundException when the security context has no authentication token.
     *
     * @return Boolean
     */
    protected function isGranted($attributes, $object = null, $throwException = true)
    {
        if(is_null($object)) {
            $object = $this->config->getObjectIdentity();
        }else{
            $object = $this->get('ekyna_admin.pool_registry')->getObjectIdentity($object);
        }
        if(!$this->get('security.context')->isGranted($attributes, $object)) {
            if($throwException) {
                throw new AccessDeniedHttpException('You are not allowed to view this resource.');
            }
            return false;
        }
        return true;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getManager()
    {
        return $this->get($this->config->getServiceKey('manager'));
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->get($this->config->getServiceKey('repository'));
    }

    /**
     * Creates a new resource
     * 
     * @param Context $context
     * 
     * @return Object
     */
    protected function createNew(Context $context)
    {
        $resource = $this->getRepository()->createNew();

        if(null !== $context && $this->hasParent()) {
            $parentResourceName = $this->getParent()->getConfiguration()->getResourceName();
            $parent = $context->getResource($parentResourceName);

            try {
                $propertyAcessor = PropertyAccess::createPropertyAccessor();
                $propertyAcessor->setValue($resource, $parentResourceName, $parent);
                //$resource->{Inflector::camelize('set_'.$parentResourceName)}($parent);
            } catch (\Exception $e) {
                throw new \RuntimeException('Failed to set resource\'s parent.');
            }
        }

        return $resource;
    }

    /**
     * Persists a resource
     * 
     * @param object $resource
     */
    protected function persist($resource, $creation = false)
    {
        $em = $this->getManager();
        $em->persist($resource);
        $em->flush();
    }

    /**
     * Removes a resource
     * 
     * @param object $resource
     */
    protected function remove($resource)
    {
        $em = $this->getManager();
        $em->remove($resource);
        $em->flush();
    }

    /**
     * Creates a confirmation form
     * 
     * @param Context $context
     * @param string $message
     * 
     * @return \Symfony\Component\Form\Form
     */
    protected function createConfirmationForm(Context $context, $message = null)
    {
        if(null === $message) {
            $message = 'Confirmer la suppression ?';
        }

        $builder = $this->createFormBuilder(null, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $this->generateUrl(
                    $this->config->getRoute('show'),
                    $context->getIdentifiers(true)
                ),
                'buttons' => array(
            	    'submit' => array(
            	        'theme' => 'danger',
            	        'icon'  => 'trash',
            	        'label' => 'ekyna_core.button.remove',
                    )
                )
            ),
        ));

        return $builder
            ->add('confirm', 'checkbox', array(
                'label' => $message,
                'attr' => array('align_with_widget' => true),
                'required' => true
            ))
            ->getForm()
        ;
    }

    /**
     * Adds a flash message
     * 
     * @param string $message
     * @param string $type
     */
    protected function addFlash($message, $type = 'info')
    {
        $this->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * Appends a link or span to the admin breadcrumb
     * 
     * @param string $name
     * @param string $label
     * @param string $route
     * 
     * @param array $parameters
     */
    protected function appendBreadcrumb($name, $label, $route = null, array $parameters = array())
    {
        $this->container->get('ekyna_admin.menu.builder')->breadcrumbAppend($name, $label, $route, $parameters);
    }
}
