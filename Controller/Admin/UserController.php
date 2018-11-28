<?php

namespace Ekyna\Bundle\AdminBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Resource\ToggleableTrait;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\AdminBundle\Event\UserEvents;
use Ekyna\Bundle\AdminBundle\Service\Search\UserRepository;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package Ekyna\Bundle\AdminBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserController extends ResourceController
{
    use ToggleableTrait;

    /**
     * {@inheritdoc}
     */
    public function searchAction(Request $request)
    {
        //$callback = $request->query->get('callback');
        $limit = intval($request->query->get('limit'));
        $query = trim($request->query->get('query'));
        $roles = $request->query->get('roles');

        $repository = $this->get('fos_elastica.manager')->getRepository($this->config->getResourceClass());
        if (!$repository instanceOf UserRepository) {
            throw new \RuntimeException('Expected instance of ' . UserRepository::class);
        }

        if (!empty($roles)) {
            $groups = $this->get('ekyna_admin.group.repository')->findByRoles((array)$roles);

            $results = $repository->searchByGroups($query, $groups, $limit);
        } else {
            $results = $repository->defaultSearch($query, $limit);
        }

        $data = $this->container->get('serializer')->serialize([
            'results'     => $results,
            'total_count' => count($results),
        ], 'json', ['groups' => ['Default']]);

        $response = new Response($data);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
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

        // New password
        $password = bin2hex(random_bytes(4));
        $resource->setPlainPassword($password);

        $event->addMessage(new ResourceMessage(
            sprintf('Generated password : "%s".', $password),
            ResourceMessage::TYPE_INFO
        ));

        // TODO use ResourceManager
        // Update event
        $operator->update($event);
        if (!$event->isPropagationStopped()) {
            $sent = $this
                ->get('ekyna_admin.admin_mailer')
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
}
