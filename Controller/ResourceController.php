<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationInterface;
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
class ResourceController extends Controller implements ResourceControllerInterface
{
    /**
     * Parent resource controller
     *
     * @var ResourceController
     */
    protected $parent;

    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function homeAction()
    {
        return $this->redirect($this->generateUrl($this->config->getRoute('list')));
    }

    /**
     * {@inheritdoc}
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

        $response = new Response();

        $format = 'html';
        if ($isXmlHttpRequest) {
            $format = 'xml';
            $response->headers->add(array(
                'Content-Type' => 'application/xml; charset=' . strtolower($this->get('kernel')->getCharset())
            ));
        }

        $response->setContent($this->renderView(
            $this->config->getTemplate('list.' . $format),
            $context->getTemplateVars(array(
                $this->config->getResourceName(true) => $table->createView()
            ))
        ));

        return $response;
    }

    /**
     * {@inheritdoc}
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

            $table->getConfig()->setCustomizeQb(function (QueryBuilder $qb, $alias) use ($resourceName, $resource) {
                $qb
                    ->where(sprintf($alias.'.%s = :resource', $resourceName))
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
     * {@inheritdoc}
     */
    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resource = $this->createNew($context);
        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $resource);

        $form = $this->createNewResourceForm($context);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($resource);

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
            }

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                if (null === $redirectPath = $form->get('_redirect')->getData()) {
                    $redirectPath = $this->generateUrl($this->config->getRoute('show'), $context->getIdentifiers(true));
                }
                return $this->redirect($redirectPath);
            }
        } elseif ($request->getMethod() === 'POST' && $request->isXmlHttpRequest()) {
            return new JsonResponse(array('error' => $form->getErrors()));
        }

        $response = new Response();

        $format = 'html';
        if ($request->isXmlHttpRequest()) {
            $format = 'xml';
            $response->headers->add(array(
                'Content-Type' => 'application/xml; charset='.strtolower($this->get('kernel')->getCharset())
            ));
        } else {
            $this->appendBreadcrumb(sprintf('%s-new', $resourceName), 'ekyna_core.button.create');
        }

        $response->setContent($this->renderView(
            $this->config->getTemplate('new.' . $format),
            $context->getTemplateVars(array(
                'form' => $form->createView()
            ))
        ));

        return $response;
    }

    /**
     * Creates the new resource form.
     *
     * @param Context $context
     * @return \Symfony\Component\Form\Form
     */
    protected function createNewResourceForm(Context $context)
    {
        if ($this->hasParent()) {
            $cancelRoute = $this->getParent()->getConfiguration()->getRoute('show');
        } else {
            $cancelRoute = $this->config->getRoute('list');
        }

        $resource = $context->getResource($this->config->getResourceName());
        $cancelPath = $this->generateUrl($cancelRoute, $context->getIdentifiers());

        return $this->createForm($this->config->getFormType(), $resource, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $form = $this->createEditResourceForm($context);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($resource);

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                if (null === $redirectPath = $form->get('_redirect')->getData()) {
                    $redirectPath = $this->generateUrl($this->config->getRoute('show'), $context->getIdentifiers(true));
                }
                return $this->redirect($redirectPath);
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
     * Creates the edit resource form.
     *
     * @param Context $context
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditResourceForm(Context $context)
    {
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

        $resource = $context->getResource($this->config->getResourceName());

        return $this->createForm($this->config->getFormType(), $resource, array(
            'admin_mode' => true,
            '_redirect_enabled' => true,
            '_footer' => array(
                'cancel_path' => $cancelPath,
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('DELETE', $resource);

        $form = $this->createRemoveResourceForm($context);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($resource);

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
     * Creates the remove resource form.
     *
     * @param Context $context
     * @param string $message
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createRemoveResourceForm(Context $context, $message = null)
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasParent()
    {
        return (null !== $this->config->getParentId());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function loadContext(Request $request, Context $context = null)
    {
        $resourceName = $this->config->getResourceName();
        if (null === $context) {
            $context = new Context($this->config);
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
     * @return bool
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
