<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Resource\Configuration\ConfigurationInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Search\Request as SearchRequest;
use Ekyna\Component\Resource\Search\ResourceRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints;

/**
 * Class ResourceController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceController extends Controller implements ResourceControllerInterface
{
    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * Parent resource controller
     *
     * @var ResourceController
     */
    protected $parentController;

    /**
     * Parent resource configuration
     *
     * @var ConfigurationInterface
     */
    protected $parentConfiguration;


    /**
     * @inheritDoc
     */
    public function setConfiguration(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function homeAction()
    {
        return $this->redirect($this->generateUrl($this->config->getRoute('list')));
    }

    /**
     * @inheritDoc
     */
    public function listAction(Request $request)
    {
        $this->isGranted('VIEW');

        $context = $this->loadContext($request);

        $table = $this
            ->getTableFactory()
            ->createTable($this->config->getResourceName(), $this->config->getTableType());

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        if ($request->isXmlHttpRequest()) {
            $modal = $this->createModal('list');
            $modal->setContent($table->createView());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        return $this->render(
            $this->config->getTemplate('list.html'),
            $context->getTemplateVars([
                $this->config->getResourceName(true) => $table->createView(),
            ])
        );
    }

    /**
     * @inheritDoc
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
     * @param array   $data
     * @param Context $context
     *
     * @return Response|null
     */
    protected function buildShowData(
        /** @noinspection PhpUnusedParameterInspection */
        array &$data,
        /** @noinspection PhpUnusedParameterInspection */
        Context $context
    ) {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $isXhr = $request->isXmlHttpRequest();
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Resource\Model\ResourceInterface $resource */
        $resource = $this->createNew($context);

        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $resource);

        $this->getOperator()->initialize($resource);

        $form = $this->createNewResourceForm($context, !$isXhr);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return JsonResponse::create($this->normalize($resource));
                }

                $redirectPath = null;
                if ($form->get('actions')->has('saveAndList') && $form->get('actions')->get('saveAndList')->isClicked()) {
                    $redirectPath = $this->generateResourcePath($resource, 'list');
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    if ($this->hasParent() && null !== $parentResource = $this->getParentResource($context)) {
                        $redirectPath = $this->generateResourcePath($parentResource, 'show');
                    } else {
                        $redirectPath = $this->generateResourcePath($resource, 'show');
                    }
                }

                return $this->redirect($redirectPath);
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_core.button.create'
        );

        return $this->render(
            $this->config->getTemplate('new.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Creates the new resource form.
     *
     * @param Context $context
     * @param bool    $footer
     * @param array   $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createNewResourceForm(Context $context, $footer = true, array $options = [])
    {
        $resource = $context->getResource();

        if (isset($options['action'])) {
            $action = $options['action'];
        } else {
            $action = $this->generateResourcePath($resource, 'new', $context->getRequest()->query->all());
        }

        $form = $this->createForm($this->config->getFormType(), $resource, array_merge([
            'action'            => $action,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ], $options));

        if ($footer) {
            $referer = $context->getRequest()->headers->get('referer');
            if (0 < strlen($referer) && false === strpos($referer, $action)) {
                $cancelPath = $referer;
            } else {
                if ($this->hasParent()) {
                    $cancelRoute = $this->getParentController()->getConfiguration()->getRoute('show');
                } else {
                    $cancelRoute = $this->config->getRoute('list');
                }
                $cancelPath = $this->generateUrl($cancelRoute, $context->getIdentifiers());
            }

            $this->createFormFooter($form, $context, [], $cancelPath);
        }

        return $form;
    }

    /**
     * @inheritDoc
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
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return JsonResponse::create($this->normalize($resource));
                }

                $redirectPath = null;
                if ($form->get('actions')->has('saveAndList') && $form->get('actions')->get('saveAndList')->isClicked()) {
                    $redirectPath = $this->generateResourcePath($resource, 'list');
                } elseif (null === $redirectPath = $form->get('_redirect')->getData()) {
                    if ($this->hasParent() && null !== $parentResource = $this->getParentResource($context)) {
                        $redirectPath = $this->generateResourcePath($parentResource, 'show');
                    } else {
                        $redirectPath = $this->generateResourcePath($resource, 'show');
                    }
                }

                return $this->redirect($redirectPath);
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('edit', null, $resource);
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_edit', $resourceName),
            'ekyna_core.button.edit'
        );

        return $this->render(
            $this->config->getTemplate('edit.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Creates the edit resource form.
     *
     * @param Context $context
     * @param bool    $footer
     * @param array   $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createEditResourceForm(Context $context, $footer = true, array $options = [])
    {
        $resource = $context->getResource();

        if (isset($options['action'])) {
            $action = $options['action'];
        } else {
            $action = $this->generateResourcePath($resource, 'edit', $context->getRequest()->query->all());
        }

        $form = $this->createForm($this->config->getFormType(), $resource, array_merge([
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            'method'            => 'POST',
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ], $options));

        if ($footer) {
            $this->createFormFooter($form, $context);
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('DELETE', $resource);

        $isXhr = $request->isXmlHttpRequest();
        // TODO use core bundle ConfirmType
        $form = $this->createRemoveResourceForm($context, null, !$isXhr);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return JsonResponse::create([
                        'success' => true,
                    ]);
                }

                if (null === $redirectPath = $form->get('_redirect')->getData()) {
                    if ($this->hasParent() && null !== $parentResource = $this->getParentResource($context)) {
                        $redirectPath = $this->generateResourcePath($parentResource, 'show');
                    } else {
                        $redirectPath = $this->generateResourcePath($resource, 'list');
                    }
                }

                return $this->redirect($redirectPath);
            }

            // TODO all event messages should be bound to XHR response
            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }

        }

        if ($isXhr) {
            $modal = $this->createModal('remove', null, $resource);
            $vars = $context->getTemplateVars();
            unset($vars['form_template']);
            $modal
                ->setSize(Modal::SIZE_NORMAL)
                ->setContent($form->createView())
                ->setVars($vars);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_remove', $resourceName),
            'ekyna_core.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
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
     *
     * @return \Symfony\Component\Form\FormInterface
     * @deprecated
     */
    protected function createRemoveResourceForm(Context $context, $message = null, $footer = true, array $options = [])
    {
        if (null === $message) {
            $message = 'ekyna_core.message.remove_confirm';
        }

        $resource = $context->getResource();

        if (isset($options['action'])) {
            $action = $options['action'];
        } else {
            $action = $this->generateResourcePath($resource, 'remove', $context->getRequest()->query->all());
        }

        $form = $this
            ->createFormBuilder(null, array_merge([
                'action'            => $action,
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                'admin_mode'        => true,
                '_redirect_enabled' => true,
            ], $options))
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => $message,
                'attr'        => ['align_with_widget' => true],
                'required'    => true,
                'constraints' => [
                    new Constraints\IsTrue(),
                ],
            ])
            ->getForm();

        if ($footer) {
            $referer = $context->getRequest()->headers->get('referer');
            if (0 < strlen($referer) && false === strpos($referer, $action)) {
                $cancelPath = $referer;
            } else {
                if ($this->hasParent()) {
                    $cancelPath = $this->generateUrl(
                        $this->getParentController()->getConfiguration()->getRoute('show'),
                        $context->getIdentifiers()
                    );
                } else {
                    $cancelPath = $this->generateResourcePath($resource);
                }
            }

            $form->add('actions', FormActionsType::class, [
                'buttons' => [
                    'remove' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'danger',
                            'label'        => 'ekyna_core.button.remove',
                            'attr'         => ['icon' => 'trash'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $cancelPath,
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function searchAction(Request $request)
    {
        $data = [
            'results' => [],
            'total_count' => 0,
        ];

        $id = sprintf('%s.search', $this->config->getResourceId());
        if (!$this->has($id)) {
            return new JsonResponse($data);
        }

        $repository = $this->get($id);
        if (!$repository instanceof ResourceRepositoryInterface) {
            throw new \RuntimeException("Expected instance of " . ResourceRepositoryInterface::class);
        }

        $searchRequest = $this->createSearchRequest($request);

        if ($repository->supports($searchRequest)) {
            $data = $repository->search($searchRequest);
        }

        return new JsonResponse($data);
    }

    /**
     * Creates the search request.
     *
     * @param Request $request
     *
     * @return SearchRequest
     */
    protected function createSearchRequest(Request $request): SearchRequest
    {
        $page = intval($request->query->get('page', 1)) - 1;
        $limit = intval($request->query->get('limit', 10));

        $expression = (string)($request->request->get('expression') ?? $request->query->get('query'));

        $searchRequest = new SearchRequest($expression);

        return $searchRequest
            ->setType(SearchRequest::RAW)
            ->setPrivate(true)
            ->setLimit($limit)
            ->setOffset($page * $limit);
    }

    /**
     * @inheritDoc
     */
    public function findAction(Request $request)
    {
        $id = intval($request->query->get('id'));

        $resource = $this->findResourceOrThrowException(['id' => $id]);

        return JsonResponse::create([
            'id'   => $resource->getId(),
            'text' => (string)$resource,
        ]);
    }

    /**
     * Appends a link or span to the admin breadcrumb
     *
     * @param string $name
     * @param string $label
     * @param string $route
     *
     * @param array  $parameters
     */
    protected function appendBreadcrumb($name, $label, $route = null, array $parameters = [])
    {
        $this->container->get('ekyna_admin.menu.builder')->breadcrumbAppend($name, $label, $route, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function hasParent()
    {
        return 0 < strlen($this->config->getParentId());
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function getParentController()
    {
        if (null === $this->parentController && $this->hasParent()) {
            $parentId = $this->config->getParentControllerId();
            if (!$this->container->has($parentId)) {
                throw new \RuntimeException('Parent resource controller &laquo; ' . $parentId . ' &raquo; does not exists.');
            }
            $this->parentController = $this->container->get($parentId);
        }

        return $this->parentController;
    }

    /**
     * @inheritDoc
     */
    public function getParentConfiguration()
    {
        if (null === $this->parentConfiguration && $this->hasParent()) {
            $parentId = $this->config->getParentConfigurationId();
            if (!$this->container->has($parentId)) {
                throw new \RuntimeException('Parent resource configuration &laquo; ' . $parentId . ' &raquo; does not exists.');
            }
            $this->parentConfiguration = $this->container->get($parentId);
        }

        return $this->parentConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function loadContext(Request $request, Context $context = null)
    {
        if (null === $context) {
            $context = new Context($this->config, $request);
        }
        $resourceName = $this->config->getResourceName();

        if ($this->hasParent()) {
            $this->getParentController()->loadContext($request, $context);
        }

        if (!$request->isXmlHttpRequest()) {
            if ($this->hasParent()) {
                $this->appendBreadcrumb(
                    sprintf('%s_list', $resourceName),
                    $this->config->getResourceLabel(true)
                );
            } else {
                $listRoute = $this->config->getRoute('list');
                if (null === $this->getResourceHelper()->findRoute($listRoute, false)) {
                    $listRoute = null;
                }
                $this->appendBreadcrumb(
                    sprintf('%s_list', $resourceName),
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
                $showRoute = $this->config->getRoute('show');
                if (null === $this->getResourceHelper()->findRoute($showRoute, false)) {
                    $showRoute = null;
                }
                $this->appendBreadcrumb(
                    sprintf('%s_%s', $resourceName, $resource->getId()),
                    $resource,
                    $showRoute,
                    $context->getIdentifiers(true)
                );
            }
        }

        return $context;
    }

    /**
     * Returns the parent resource.
     *
     * @param Context $context
     *
     * @return null|object
     */
    protected function getParentResource(Context $context)
    {
        if ($this->hasParent()) {
            return $context->getResource($this->getParentController()->getConfiguration()->getResourceName());
        }

        return null;
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
     * @param bool       $throwException
     *
     * @throws AccessDeniedHttpException when the security context has no authentication token.
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null, $throwException = true)
    {
        if (is_null($object)) {
            $object = $this->config->getAlias();
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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get($this->config->getServiceKey('manager'));
    }

    /**
     * Returns the current resource operator.
     *
     * @TODO Temporary solution until ResourceManager is available.
     *
     * @return \Ekyna\Component\Resource\Operator\ResourceOperatorInterface
     */
    protected function getOperator()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get($this->config->getServiceKey('operator'));
    }

    /**
     * Returns the current resource entity repository.
     *
     * @return \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository
     */
    protected function getRepository()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get($this->config->getServiceKey('repository'));
    }

    /**
     * Returns the table factory.
     *
     * @return \Ekyna\Component\Table\Factory
     */
    protected function getTableFactory()
    {
        return $this->get('table.factory');
    }

    /**
     * Generates the resource path.
     *
     * @param mixed  $resource
     * @param string $action
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateResourcePath($resource, $action = 'show', array $parameters = [])
    {
        return $this->getResourceHelper()->generateResourcePath($resource, $action, $parameters);
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
     * Normalize the given data.
     *
     * @param mixed      $data
     * @param string     $format
     * @param array|null $context
     *
     * @return mixed
     */
    protected function normalize($data, $format = 'json', array $context = null)
    {
        if (null === $context) {
            $class = $this->getConfiguration()->getResourceClass();
            $context = ['groups' => substr($class, strrpos($class, '\\') + 1)];
        }

        return $this->get('serializer')->normalize($data, $format, $context);
    }

    /**
     * Creates a new resource.
     *
     * @param Context $context
     *
     * @throws \RuntimeException
     *
     * @return object|ResourceInterface
     */
    protected function createNew(Context $context)
    {
        // TODO use a factory service

        $resource = $this->getRepository()->createNew();

        if (null !== $context && $this->hasParent()) {
            $parentConfig = $this->getParentController()->getConfiguration();
            $parentResourceName = $parentConfig->getResourceName();
            //$parentResourceNamePlural = $parentConfig->getResourceName(true);
            $parent = $context->getResource($parentResourceName);

            /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata */
            $metadata = $this->get($this->config->getServiceKey('metadata'));

            $associations = $metadata->getAssociationsByTargetClass($parentConfig->getResourceClass());
            if (!empty($associations)) {
                foreach ($associations as $mapping) {
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                        try {
                            $propertyAccessor = PropertyAccess::createPropertyAccessor();
                            $propertyAccessor->setValue($resource, $mapping['fieldName'], $parent);
                        } catch (\Exception $e) {
                            throw new \RuntimeException('Failed to set resource\'s parent.');
                        }

                        return $resource;
                    }
                }
            }

            throw new \RuntimeException(sprintf('Association "%s" not found or not supported.', $parentResourceName));
        }

        return $resource;
    }

    /**
     * Creates a modal object.
     *
     * @param string            $action
     * @param string            $title
     * @param ResourceInterface $resource
     *
     * @return Modal
     */
    protected function createModal($action, $title = null, ResourceInterface $resource = null)
    {
        if (!$title) {
            $title = sprintf('%s.header.%s', $this->config->getTranslationPrefix(), $action);
        }

        if ($resource && in_array($action, ['edit', 'remove'])) {
            $title = $this->getTranslator()->trans($title, ['%name%' => (string)$resource]);
        }

        $modal = new Modal($title);

        $buttons = [];

        if (in_array($action, ['new', 'new_child', 'edit', 'remove', 'confirm'])) {
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
            } elseif ($action === 'confirm') {
                $submitButton['label'] = 'ekyna_core.button.confirm';
                $submitButton['cssClass'] = 'btn-danger';
            } elseif ($action === 'remove') {
                $submitButton['label'] = 'ekyna_core.button.remove';
                $submitButton['icon'] = 'glyphicon glyphicon-trash';
                $submitButton['cssClass'] = 'btn-danger';
            }
            $buttons[] = $submitButton;
        }

        $buttons[] = [
            'id'       => 'close',
            'label'    => 'ekyna_core.button.cancel',
            'icon'     => 'glyphicon glyphicon-remove',
            'cssClass' => 'btn-default',
        ];

        $modal->setButtons($buttons);

        return $modal;
    }

    /**
     * Create the form's footer.
     *
     * @param FormInterface $form
     * @param Context       $context
     * @param array         $buttons
     * @param string        $cancelPath
     */
    protected function createFormFooter(FormInterface $form, Context $context, array $buttons = [], $cancelPath = null)
    {
        if (empty($buttons)) {
            if (null === $cancelPath) {
                $referer = $context->getRequest()->headers->get('referer');
                if (0 < strlen($referer) && false === strpos($referer, $form->getConfig()->getAction())) {
                    $cancelPath = $referer;
                } else {
                    if ($this->hasParent()) {
                        $cancelPath = $this->generateUrl(
                            $this->getParentController()->getConfiguration()->getRoute('show'),
                            $context->getIdentifiers()
                        );
                    } else {
                        $cancelPath = $this->generateResourcePath($context->getResource());
                    }
                }
            }

            $buttons['save'] = [
                'type'    => Type\SubmitType::class,
                'options' => [
                    'button_class' => 'primary',
                    'label'        => 'ekyna_core.button.save',
                    'attr'         => ['icon' => 'ok'],
                ],
            ];
            if (!$this->hasParent()) {
                $buttons['saveAndList'] = [
                    'type'    => Type\SubmitType::class,
                    'options' => [
                        'button_class' => 'primary',
                        'label'        => 'ekyna_core.button.save_and_list',
                        'attr'         => ['icon' => 'list'],
                    ],
                ];
            }
            $buttons['cancel'] = [
                'type'    => Type\ButtonType::class,
                'options' => [
                    'label'        => 'ekyna_core.button.cancel',
                    'button_class' => 'default',
                    'as_link'      => true,
                    'attr'         => [
                        'class' => 'form-cancel-btn',
                        'icon'  => 'remove',
                        'href'  => $cancelPath,
                    ],
                ],
            ];
        }

        $form->add('actions', FormActionsType::class, [
            'buttons' => $buttons,
        ]);
    }
}
