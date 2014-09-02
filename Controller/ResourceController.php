<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Pool\Configuration;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use Ekyna\Bundle\AdminBundle\Search\SearchRepositoryInterface;

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

    /**
     * Constructor.
     * 
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Home action.
     * 
     * @param Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction(Request $request)
    {
        return $this->redirect($this->generateUrl($this->config->getRoute('list')));
    }

    /**
     * List action.
     * 
     * @param Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $this->isGranted('VIEW');

        $isXmlHttpRequest = $request->isXmlHttpRequest();
        $context = $this->loadContext($request);

        $table = $this->getTableFactory()
            ->createBuilder($this->config->getTableType(), array(
                'name' => $this->config->getId(),
            ))
            ->getTable();

        $format = $isXmlHttpRequest ? 'xml' : 'html';

        return $this->render(
            $this->config->getTemplate('list.'.$format),
            $context->getTemplateVars(array(
                $this->config->getResourceName(true) => $table->createView($request)
            ))
        );
    }

    /**
     * New/Create action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
            } else {
                $this->addFlash('La resource a été créée avec succès.', 'success');
            }

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

    /**
     * Show action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('VIEW', $resource);

        $extrasDatas = array();

        $childrenConfigurations = $this->get('ekyna_admin.pool_registry')->getChildren($this->config);
        foreach($childrenConfigurations as $configuration) {
            $table = $this->getTableFactory()
                ->createBuilder($configuration->getTableType())
                ->getTable($configuration->getId());

            $table->getConfig()->setCustomizeQb(function(QueryBuilder $qb) use ($resourceName, $resource) {
                $qb
                    ->where(sprintf('a.%s = :resource', $resourceName))
                    ->setParameter('resource', $resource)
                ;
            });
            $extrasDatas[$configuration->getResourceName(true)] = $table->createView($request);
        }

        return $this->render(
            $this->config->getTemplate('show.html'),
            $context->getTemplateVars($extrasDatas)
        );
    }

    /**
     * Edit/Update action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * Remove/Delete action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
     * Search action.
     *
     * @param Request $request
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $callback = $request->query->get('callback');
        //$limit    = intval($request->query->get('limit'));
        $search   = trim($request->query->get('search'));

        $repository = $this->get('fos_elastica.manager')->getRepository($this->config->getResourceClass());
        if (! $repository instanceOf SearchRepositoryInterface) {
            throw new \RuntimeException('Repository must implements "SearchRepositoryInterface".');
        }
        $results = $repository->defaultSearch($search);

        $serializer = $this->container->get('jms_serializer');
        $response = new Response(sprintf('%s(%s);', $callback, $serializer->serialize(array(
            'results' => $results,
            'total'   => count($results)
        ), 'json', SerializationContext::create()->setGroups(array('Search')))));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Find action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function findAction(Request $request)
    {
        $id = intval($request->query->get('id'));

        $resource = $this->findResourceOrThrowException(array('id' => $id));

        return JsonResponse::create(array(
            'id' => $resource->getId(),
            'text' => $resource->getSearchText(),
        ));
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
     * @throws \RuntimeException
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
            if(null === $this->getRouter()->getRouteCollection()->get($listRoute)) {
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
            throw new NotFoundHttpException('Resource not found.');
        }
        return $resource;
    }

    /**
     * Checks if the attributes are granted against the current token.
     *
     * @param mixed      $attributes
     * @param mixed|null $object
     * @param bool $throwException
     *
     * @throws AccessDeniedHttpException when the security context has no authentication token.
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
     * Returns the current resource entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getManager()
    {
        return $this->get($this->config->getServiceKey('manager'));
    }

    /**
     * Returns the current resource entity repository.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->get($this->config->getServiceKey('repository'));
    }

    /**
     * Returns the table factory.
     *
     * @return \Ekyna\Component\Table\TableFactory
     */
    protected function getTableFactory()
    {
        return $this->get('table.factory');
    }

    /**
     * Returns the table generator.
     *
     * @return \Ekyna\Component\Table\TableGenerator
     */
    protected function getTableGenerator()
    {
        return $this->get('table.generator');
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface;
     */
    protected function getRouter()
    {
        return $this->get('router');
    }

    /**
     * Creates a new resource.
     * 
     * @param Context $context
     *
     * @throws \RuntimeException
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
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $propertyAccessor->setValue($resource, $parentResourceName, $parent);
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
     * @param bool $creation
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
