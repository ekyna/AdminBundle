<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationInterface;
use Ekyna\Bundle\AdminBundle\Search\SearchRepositoryInterface;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints;

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

        $context = $this->loadContext($request);

        $table = $this->getTableFactory()
            ->createBuilder($this->config->getTableType(), [
                'name' => $this->config->getResourceId(),
                'selector' => (bool)$request->get('selector', false), // TODO use constants (single/multiple)
                'multiple' => (bool)$request->get('multiple', false),
            ])
            ->getTable($request);

        if ($request->isXmlHttpRequest()) {
            $modal = $this->createModal('list');
            $modal->setContent($table->createView());
            return $this->get('ekyna_core.modal')->render($modal);
        }

        return $this->render(
            $this->config->getTemplate('list.html'),
            $context->getTemplateVars([
                $this->config->getResourceName(true) => $table->createView()
            ])
        );
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

        $data = [];
        $response = $this->buildShowData($data, $context);
        if ($response instanceof Response) {
            return $response;
        }

        $this->fetchChildrenResources($data, $context);

        /* TODO if ($request->isXmlHttpRequest()) {
            $modal = $this->createModal('show');
            $modal->setVars($context->getTemplateVars($data));
            return $this->get('ekyna_core.modal')->render($modal);
        }*/

        return $this->render(
            $this->config->getTemplate('show.html'),
            $context->getTemplateVars($data)
        );
    }

    /**
     * Builds the show view data.
     *
     * @param array $data
     * @param Context $context
     * @return Response|null
     */
    protected function buildShowData(
        /** @noinspection PhpUnusedParameterInspection */ array &$data,
        /** @noinspection PhpUnusedParameterInspection */ Context $context
    ) {
        return null;
    }

    /**
     * Fetches children resources.
     *
     * @param array $data
     * @param Context $context
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function fetchChildrenResources(array &$data, Context $context)
    {
        $resourceName = $this->config->getResourceName();
        //$resourceNamePlural = $this->config->getResourceName(true);

        $resource = $context->getResource($resourceName);

        $childrenConfigurations = $this->get('ekyna_admin.pool_registry')->getChildren($this->config);
        foreach ($childrenConfigurations as $childConfig) {
            $childResourceName = $childConfig->getResourceName(true);
            if (!array_key_exists($childResourceName, $data)) {

                $customizeQb = null;

                /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata */
                $metadata = $this->get($childConfig->getServiceKey('metadata'));

                // Look for many to one
                if ($metadata->hasAssociation($resourceName)) {
                    $mapping = $metadata->getAssociationMapping($resourceName);
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                        $customizeQb = function (QueryBuilder $qb, $alias) use ($resourceName, $resource) {
                            $qb
                                ->andWhere(sprintf($alias . '.%s = :resource', $resourceName))
                                ->setParameter('resource', $resource);
                        };
                    } else {
                        throw new \RuntimeException(sprintf('"%s" is not a supported association type.', $childResourceName));
                    }
                // Look for many to many
                } /*elseif ($metadata->hasAssociation($resourceNamePlural)) {
                    $mapping = $metadata->getAssociationMapping($resourceNamePlural);
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                        $customizeQb = function (QueryBuilder $qb, $alias) use ($resourceNamePlural, $resource) {
                            $qb
                                ->join($alias.'.'.$resourceNamePlural, 'parent')
                                ->where('parent.id = :resource')
                                ->setParameter('resource', $resource);
                        };
                    } else {
                        throw new \RuntimeException(sprintf('"%s" is not a supported association type.', $childResourceName));
                    }
                }*/ else {
                    throw new \RuntimeException(sprintf('Association "%s" not found.', $childResourceName));
                }

                $table = $this->getTableFactory()
                    ->createBuilder($childConfig->getTableType(), [
                        'name' => $childConfig->getResourceId(),
                        'customize_qb' => $customizeQb,
                    ])
                    ->getTable($context->getRequest());

                //$table->getConfig()->setCustomizeQb($customizeQb);

                $data[$childResourceName] = $table->createView();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $isXhr = $request->isXmlHttpRequest();
        $context = $this->loadContext($request);

        $resource = $this->createNew($context);
        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $resource);

        $form = $this->createNewResourceForm($context, !$isXhr);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    $modal = $this->createModal('new');
                    $modal->setContent([
                        'id' => $resource->getId(),
                        'name' => (string) $resource,
                    ]);
                    return $this->get('ekyna_core.modal')->render($modal);
                }

                /** @noinspection PhpUndefinedMethodInspection */
                if ($form->get('actions')->get('saveAndList')->isClicked()) {
                    if ($this->hasParent()) {
                        $redirectPath = $this->generateUrl(
                            $this->getParent()->getConfiguration()->getRoute('show'),
                            $context->getIdentifiers()
                        );
                    } else {
                        $redirectPath = $this->generateResourcePath($resource, 'list');
                    }
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    $redirectPath = $this->generateResourcePath($resource);
                }
                return $this->redirect($redirectPath);
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars())
            ;
            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(sprintf('%s-new', $resourceName), 'ekyna_core.button.create');

        return $this->render(
            $this->config->getTemplate('new.html'),
            $context->getTemplateVars([
                'form' => $form->createView()
            ])
        );
    }

    /**
     * Creates the new resource form.
     *
     * @param Context $context
     * @param bool    $footer
     * @param array   $options
     * @return \Symfony\Component\Form\Form
     */
    protected function createNewResourceForm(Context $context, $footer = true, array $options = [])
    {
        $resource = $context->getResource();

        $action = $this->generateResourcePath($resource, 'new');

        $form = $this->createForm($this->config->getFormType(), $resource, array_merge([
            'action' => $action,
            'method' => 'POST',
            'attr' => ['class' => 'form-horizontal form-with-tabs'],
            'admin_mode' => true,
            '_redirect_enabled' => true,
        ], $options));

        if ($footer) {
            $referer = $context->getRequest()->headers->get('referer');
            if (0 < strlen($referer) && false === strpos($referer, $action)) {
                $cancelPath = $referer;
            } else {
                if ($this->hasParent()) {
                    $cancelRoute = $this->getParent()->getConfiguration()->getRoute('show');
                } else {
                    $cancelRoute = $this->config->getRoute('list');
                }
                $cancelPath = $this->generateUrl($cancelRoute, $context->getIdentifiers());
            }

            $form->add('actions', FormActionsType::class, [
                'buttons' => [
                    'saveAndList' => [
                        'type' => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.save_and_list',
                            'attr' => ['icon' => 'list'],
                        ],
                    ],
                    'save' => [
                        'type' => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.save',
                            'attr' => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel' => [
                        'type' => Type\ButtonType::class,
                        'options' => [
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
            ]);
        }

        return $form;
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

        $isXhr = $request->isXmlHttpRequest();
        $form = $this->createEditResourceForm($context, !$isXhr);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    $modal = $this->createModal('edit');
                    $modal->setContent([
                        'id' => $resource->getId(),
                        'name' => (string) $resource,
                    ]);
                    return $this->get('ekyna_core.modal')->render($modal);
                }

                /** @noinspection PhpUndefinedMethodInspection */
                if ($form->get('actions')->get('saveAndList')->isClicked()) {
                    if ($this->hasParent()) {
                        $redirectPath = $this->generateUrl(
                            $this->getParent()->getConfiguration()->getRoute('show'),
                            $context->getIdentifiers()
                        );
                    } else {
                        $redirectPath = $this->generateResourcePath($resource, 'list');
                    }
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    $redirectPath = $this->generateResourcePath($resource);
                }
                return $this->redirect($redirectPath);
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars())
            ;
            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s-edit', $resourceName),
            'ekyna_core.button.edit'
        );

        return $this->render(
            $this->config->getTemplate('edit.html'),
            $context->getTemplateVars([
                'form' => $form->createView()
            ])
        );
    }

    /**
     * Creates the edit resource form.
     *
     * @param Context $context
     * @param bool    $footer
     * @param array   $options
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditResourceForm(Context $context, $footer = true, array $options = [])
    {
        $resource = $context->getResource();

        $action = $this->generateResourcePath($resource, 'edit');

        $form = $this->createForm($this->config->getFormType(), $resource, array_merge([
            'action' => $action,
            'attr' => ['class' => 'form-horizontal form-with-tabs'],
            'method' => 'POST',
            'admin_mode' => true,
            '_redirect_enabled' => true,
        ], $options));

        if ($footer) {
            $referer = $context->getRequest()->headers->get('referer');
            if (0 < strlen($referer) && false === strpos($referer, $action)) {
                $cancelPath = $referer;
            } else {
                if ($this->hasParent()) {
                    $cancelPath = $this->generateUrl(
                        $this->getParent()->getConfiguration()->getRoute('show'),
                        $context->getIdentifiers()
                    );
                } else {
                    $cancelPath = $this->generateResourcePath($resource);
                }
            }

            $form->add('actions', FormActionsType::class, [
                'buttons' => [
                    'saveAndList' => [
                        'type' => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.save_and_list',
                            'attr' => ['icon' => 'list'],
                        ],
                    ],
                    'save' => [
                        'type' => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label' => 'ekyna_core.button.save',
                            'attr' => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel' => [
                        'type' => Type\ButtonType::class,
                        'options' => [
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
            ]);
        }

        return $form;
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

        $isXhr = $request->isXmlHttpRequest();
        $form = $this->createRemoveResourceForm($context, null, !$isXhr);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    $modal = $this->createModal('remove');
                    $modal
                        ->setSize(Modal::SIZE_NORMAL)
                        ->setContent(['success' => true])
                    ;
                    return $this->get('ekyna_core.modal')->render($modal);
                }

                if (null !== $redirectPath = $form->get('_redirect')->getData()) {
                    return $this->redirect($redirectPath);
                }

                if ($this->hasParent()) {
                    $returnRoute = $this->getParent()->getConfiguration()->getRoute('show');
                } else {
                    $returnRoute = $this->config->getRoute('list');
                }

                return $this->redirect(
                    $this->generateUrl(
                        $returnRoute,
                        $context->getIdentifiers()
                    )
                );
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('remove');
            $vars = $context->getTemplateVars();
            unset($vars['form_template']);
            $modal
                ->setSize(Modal::SIZE_NORMAL)
                ->setContent($form->createView())
                ->setVars($vars)
            ;
            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s-remove', $resourceName),
            'ekyna_core.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars([
                'form' => $form->createView()
            ])
        );
    }

    /**
     * Creates the remove resource form.
     *
     * @param Context $context
     * @param string  $message
     * @param bool    $footer
     * @param array   $options
     * @return \Symfony\Component\Form\Form
     */
    protected function createRemoveResourceForm(Context $context, $message = null, $footer = true, array $options = [])
    {
        if (null === $message) {
            $message = 'ekyna_core.message.remove_confirm';
        }

        $resource = $context->getResource();

        $action = $this->generateResourcePath($resource, 'remove');

        $form = $this
            ->createFormBuilder(null, array_merge([
                'action' => $action,
                'attr' => ['class' => 'form-horizontal'],
                'method' => 'POST',
                'admin_mode' => true,
                '_redirect_enabled' => true,
            ], $options))
            ->add('confirm', Type\CheckboxType::class, [
                'label' => $message,
                'attr' => ['align_with_widget' => true],
                'required' => true,
                'constraints' => [
                    new Constraints\IsTrue(),
                ]
            ])
            ->getForm()
        ;

        if ($footer) {
            $referer = $context->getRequest()->headers->get('referer');
            if (0 < strlen($referer) && false === strpos($referer, $action)) {
                $cancelPath = $referer;
            } else {
                if ($this->hasParent()) {
                    $cancelPath = $this->generateUrl(
                        $this->getParent()->getConfiguration()->getRoute('show'),
                        $context->getIdentifiers()
                    );
                } else {
                    $cancelPath = $this->generateResourcePath($resource);
                }
            }

            $form->add('actions', FormActionsType::class, [
                'buttons' => [
                    'remove' => [
                        'type' => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'danger',
                            'label' => 'ekyna_core.button.remove',
                            'attr' => ['icon' => 'trash'],
                        ],
                    ],
                    'cancel' => [
                        'type' => Type\ButtonType::class,
                        'options' => [
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
            ]);
        }

        return $form;
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
        $response = new Response(sprintf('%s(%s);', $callback, $serializer->serialize([
            'results' => $results,
            'total' => count($results)
        ], 'json', SerializationContext::create()->setGroups(['Search']))));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function findAction(Request $request)
    {
        $id = intval($request->query->get('id'));

        $resource = $this->findResourceOrThrowException(['id' => $id]);

        return JsonResponse::create([
            'id' => $resource->getId(),
            'text' => (string) $resource,
        ]);
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
    protected function appendBreadcrumb($name, $label, $route = null, array $parameters = [])
    {
        $this->container->get('ekyna_admin.menu.builder')->breadcrumbAppend($name, $label, $route, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent()
    {
        return 0 < strlen($this->config->getParentId());
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
        if (null === $context) {
            $context = new Context($this->config, $request);
        }
        $resourceName = $this->config->getResourceName();

        if ($this->hasParent()) {
            $this->getParent()->loadContext($request, $context);
        }

        if (!$request->isXmlHttpRequest()) {
            if ($this->hasParent()) {
                $this->appendBreadcrumb(
                    sprintf('%s-list', $resourceName),
                    $this->config->getResourceLabel(true)
                );
            } else {
                $listRoute = $this->config->getRoute('list');
                if (null === $this->getResourceHelper()->findRoute($listRoute)) {
                    $listRoute = null;
                }
                $this->appendBreadcrumb(
                    sprintf('%s-list', $resourceName),
                    $this->config->getResourceLabel(true),
                    $listRoute,
                    $context->getIdentifiers()
                );
            }
        }

        if ($request->attributes->has($resourceName . 'Id')) {
            $resource = $this->findResourceOrThrowException(['id' => $request->attributes->get($resourceName . 'Id')]);
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
        if (!$this->get('security.authorization_checker')->isGranted($attributes, $object)) {
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
     * Generates the resource path.
     *
     * @param object $resource
     * @param string $action
     * @return string
     */
    protected function generateResourcePath($resource, $action = 'show')
    {
        return $this->getResourceHelper()->generateResourcePath($resource, $action);
    }

    /**
     * Returns the resource helper.
     *
     * @return \Ekyna\Bundle\AdminBundle\Helper\ResourceHelper
     */
    protected function getResourceHelper()
    {
        return $this->get('ekyna_admin.helper.resource_helper');
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
            $parentConfig = $this->getParent()->getConfiguration();
            $parentResourceName = $parentConfig->getResourceName();
            //$parentResourceNamePlural = $parentConfig->getResourceName(true);
            $parent = $context->getResource($parentResourceName);

            /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata */
            $metadata = $this->get($this->config->getServiceKey('metadata'));

            // Look for many to one
            if ($metadata->hasAssociation($parentResourceName)) {
                $mapping = $metadata->getAssociationMapping($parentResourceName);
                if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                    try {
                        $propertyAccessor = PropertyAccess::createPropertyAccessor();
                        $propertyAccessor->setValue($resource, $parentResourceName, $parent);
                    } catch (\Exception $e) {
                        throw new \RuntimeException('Failed to set resource\'s parent.');
                    }
                } else {
                    throw new \RuntimeException(sprintf('"%s" is not a supported association type.', $parentResourceName));
                }
                // Look for many to many
            } /*elseif ($metadata->hasAssociation($parentResourceNamePlural)) {
                $mapping = $metadata->getAssociationMapping($parentResourceNamePlural);
                if ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                    try {
                        call_user_func(array($resource, 'add'.ucfirst($parentResourceName)), $parent);
                    } catch (\Exception $e) {
                        throw new \RuntimeException('Failed to associate resource with his parent.');
                    }
                } else {
                    throw new \RuntimeException(sprintf('"%s" is not a supported association type.', $parentResourceNamePlural));
                }
            }*/ else {
                throw new \RuntimeException(sprintf('Association "%s" not found.', $parentResourceName));
            }
        }

        return $resource;
    }

    /**
     * Creates a modal object.
     *
     * @param string $action
     * @return Modal
     */
    protected function createModal($action)
    {
        $modal = new Modal(sprintf('%s.header.%s', $this->config->getResourceId(), $action));

        $buttons = [];

        if (in_array($action, ['new', 'edit', 'remove'])) {
            $submitButton = [
                'id'       => 'submit',
                'label'    => 'ekyna_core.button.save',
                'icon'     => 'glyphicon glyphicon-ok',
                'cssClass' => 'btn-success',
                'autospin' => true,
            ];
            if ($action === 'edit') {
                $submitButton['icon'] = 'glyphicon glyphicon-ok';
                $submitButton['cssClass'] = 'btn-warning';
            } elseif ($action === 'remove') {
                $submitButton['label'] = 'ekyna_core.button.remove';
                $submitButton['icon'] = 'glyphicon glyphicon-trash';
                $submitButton['cssClass'] = 'btn-danger';
            }
            $buttons[] = $submitButton;
        }

        $buttons[] = [
            'id' => 'close',
            'label' => 'ekyna_core.button.cancel',
            'icon' => 'glyphicon glyphicon-remove',
            'cssClass' => 'btn-default',
        ];

        $modal->setButtons($buttons);

        return $modal;
    }
}
