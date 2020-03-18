<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Resource\ToggleableTrait;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\AdminBundle\Event\UserEvents;
use Ekyna\Bundle\AdminBundle\Service\Mailer\AdminMailer;
use Ekyna\Bundle\AdminBundle\Service\Security\SecurityUtil;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Search\Request as SearchRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package Ekyna\Bundle\AdminBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserController extends ResourceController
{
    use ToggleableTrait;


    /**
     * @inheritDoc
     */
    protected function createSearchRequest(Request $request): SearchRequest
    {
        $searchRequest = parent::createSearchRequest($request);

        $searchRequest->setParameter('roles', (array)$request->query->get('roles'));

        return $searchRequest;
    }

    /**
     * Generate password action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generatePasswordAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Bundle\AdminBundle\Model\UserInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        // Prevent changing password of super admin
        if (in_array('ROLE_SUPER_ADMIN', $resource->getGroup()->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        $redirect = $this->generateResourcePath($resource);

        $operator = $this->getOperator();
        $event = $operator->createResourceEvent($resource);

        $dispatcher = $this->get('ekyna_resource.event_dispatcher');

        // Pre generate event
        $dispatcher->dispatch(UserEvents::PRE_GENERATE_PASSWORD, $event);
        if ($event->isPropagationStopped()) {
            $event->toFlashes($this->getFlashBag());

            return $this->redirect($redirect);
        }

        $password = SecurityUtil::generatePassword($resource);

        $event->addMessage(new ResourceMessage(
            sprintf('Generated password : "%s".', $password),
            ResourceMessage::TYPE_INFO
        ));

        // TODO use ResourceManager
        // Update event
        $operator->update($event);
        if (!$event->isPropagationStopped()) {
            $sent = $this
                ->get(AdminMailer::class)
                ->sendNewPasswordEmailMessage($resource, $password);

            if (0 < $sent) {
                $event->addMessage(new ResourceMessage(
                    'ekyna_admin.user.message.credentials_sent',
                    ResourceMessage::TYPE_INFO
                ));
            }
        }

        // Flashes
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($redirect);
    }

    /**
     * Generate API token action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generateApiToken(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Bundle\AdminBundle\Model\UserInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        SecurityUtil::generateApiToken($resource);

        // TODO use ResourceManager
        // Update event
        $event = $this->getOperator()->update($resource);

        // Flashes
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($this->generateResourcePath($resource));
    }
}
