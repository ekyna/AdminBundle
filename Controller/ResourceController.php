<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Pool\Configuration;
use Ekyna\Bundle\AdminBundle\Search\SearchRepositoryInterface;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ResourceController
 * @package Ekyna\Bundle\AdminBundle\Controller
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction()
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
                'selector' => (bool)$request->get('selector', false), // TODO use constants (single/multiple)
                'multiple' => (bool)$request->get('multiple', false),
            ))
            ->getTable($request);

        $format = $isXmlHttpRequest ? 'xml' : 'html';

        return $this->render(
            $this->config->getTemplate('list.' . $format),
            $context->getTemplateVars(array(
                $this->config->getResourceName(true) => $table->createView()
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
            $cancelPath = $this->generateUrl(
                $this->config->getRoute('list'),
                $context->getIdentifiers()
            );
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
            // TODO use ResourceManager
            //$event = $this->getManager()->create($resource);
            $event = $this->getOperator()->create($resource);
            //$event = $this->createResource($resource);

            if ($request->isXmlHttpRequest()) {
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
                /*$serializer = $this->container->get('jms_serializer');
                $response = new Response($serializer->serialize($resource, 'json'));
                $response->headers->set('Content-Type', 'application/json');
                return $response;*/
            }

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
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
            }
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
        foreach ($childrenConfigurations as $configuration) {
            $table = $this->getTableFactory()
                ->createBuilder($configuration->getTableType(), array(
                    'name' => $configuration->getId(),
                ))
                ->getTable($request);

            $table->getConfig()->setCustomizeQb(function (QueryBuilder $qb) use ($resourceName, $resource) {
                $qb
                    ->where(sprintf('a.%s = :resource', $resourceName))
                    ->setParameter('resource', $resource);
            });
            $extrasDatas[$configuration->getResourceName(true)] = $table->createView();
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

        if ($this->hasParent()) {
            $cancelPath = $this->generateUrl(
                $this->getParent()->getConfiguration()->getRoute('show'),
                $context->getIdentifiers()
            );
        } else {
            $cancelPath = $this->generateUrl(
                $this->config->getRoute('show'),
                $context->getIdentifiers(true)
            );
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
            // TODO use ResourceManager
            //$event = $this->getManager()->update($resource);
            $event = $this->getOperator()->update($resource);
            //$event = $this->updateResource($resource);
            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
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
            // TODO use ResourceManager
            //$event = $this->getManager()->delete($resource);
            $event = $this->getOperator()->delete($resource);
            //$event = $this->deleteResource($resource);
            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                if (null !== $redirectPath = $form->get('_redirect')->getData()) {
                    return $this->redirect($redirectPath);
                }

                return $this->redirect(
                    $this->generateUrl(
                        $this->config->getRoute('list'),
                        $context->getIdentifiers()
                    )
                );
            }
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
        $search = trim($request->query->get('search'));

        $repository = $this->get('fos_elastica.manager')->getRepository($this->config->getResourceClass());
        if (!$repository instanceOf SearchRepositoryInterface) {
            throw new \RuntimeException('Repository must implements "SearchRepositoryInterface".');
        }
        $results = $repository->defaultSearch($search);

        $serializer = $this->container->get('jms_serializer');
        $response = new Response(sprintf('%s(%s);', $callback, $serializer->serialize(array(
            'results' => $results,
            'total' => count($results)
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
     * Creates a confirmation form
     *
     * @param Context $context
     * @param string $message
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createConfirmationForm(Context $context, $message = null)
    {
        if (null === $message) {
            $message = 'Confirmer la suppression ?';
        }

        if ($this->hasParent()) {
            $cancelPath = $this->generateUrl(
                $this->getParent()->getConfiguration()->getRoute('show'),
                $context->getIdentifiers()
            );
        } else {
            $cancelPath = $this->generateUrl(
                $this->config->getRoute('show'),
                $context->getIdentifiers(true)
            );
        }

        $builder = $this->createFormBuilder(null, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
                'buttons' => array(
                    'submit' => array(
                        'theme' => 'danger',
                        'icon' => 'trash',
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
            ->getForm();
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
     * Returns the parent controller.
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
            if (null === $this->getRouter()->getRouteCollection()->get($listRoute)) {
                $listRoute = null;
            }
            $this->appendBreadcrumb(
                sprintf('%s-list', $resourceName),
                $this->config->getResourceLabel(true),
                $listRoute,
                $context->getIdentifiers()
            );
        }

        if ($request->attributes->has($resourceName . 'Id')) {
            $resource = $this->findResourceOrThrowException(array('id' => $request->attributes->get($resourceName . 'Id')));
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
     * @param mixed $attributes
     * @param mixed|null $object
     * @param bool $throwException
     *
     * @throws AccessDeniedHttpException when the security context has no authentication token.
     *
     * @return Boolean
     */
    protected function isGranted($attributes, $object = null, $throwException = true)
    {
        if (is_null($object)) {
            $object = $this->config->getObjectIdentity();
        } else {
            $object = $this->get('ekyna_admin.pool_registry')->getObjectIdentity($object);
        }
        if (!$this->get('security.context')->isGranted($attributes, $object)) {
            if ($throwException) {
                throw new AccessDeniedHttpException('You are not allowed to view this resource.');
            }
            return false;
        }
        return true;
    }

    /**
     * Returns the current resource entity manager.
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    protected function getManager()
    {
        return $this->get($this->config->getServiceKey('manager'));
    }

    /**
     * Returns the current resource operator.
     *
     * @TODO Temporary solution until ResourceManager is available.
     * 
     * @return \Ekyna\Bundle\AdminBundle\Operator\ResourceOperatorInterface
     */
    protected function getOperator()
    {
        return $this->get($this->config->getServiceKey('operator'));
    }

    /**
     * Returns the current resource entity repository.
     *
     * @return \Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepository
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
     * Creates a new resource.
     *
     * @param Context $context
     *
     * @throws \RuntimeException
     *
     * @return object
     */
    protected function createNew(Context $context)
    {
        $resource = $this->getRepository()->createNew();

        if (null !== $context && $this->hasParent()) {
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
}
