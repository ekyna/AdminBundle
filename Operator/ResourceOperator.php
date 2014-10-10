<?php

namespace Ekyna\Bundle\AdminBundle\Operator;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Bundle\AdminBundle\Event\ResourceMessage;
use Ekyna\Bundle\AdminBundle\Pool\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ResourceManager
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Swap with ResourceManager when ready.
 */
class ResourceOperator implements ResourceOperatorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     * @param EventDispatcherInterface $dispatcher
     * @param Configuration $config
     */
    public function __construct(EntityManagerInterface $manager, EventDispatcherInterface $dispatcher, Configuration $config)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function create($resourceOrEvent)
    {
        $event = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent : $this->createResourceEvent($resourceOrEvent);
        $this->dispatcher->dispatch($this->config->getEventName('pre_create'), $event);

        if (!$event->isPropagationStopped()) {
            $this->persistResource($event);

            $this->dispatcher->dispatch($this->config->getEventName('post_create'), $event);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function update($resourceOrEvent)
    {
        $event = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent : $this->createResourceEvent($resourceOrEvent);
        $this->dispatcher->dispatch($this->config->getEventName('pre_update'), $event);

        if (!$event->isPropagationStopped()) {
            $this->persistResource($event);

            $this->dispatcher->dispatch($this->config->getEventName('post_update'), $event);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($resourceOrEvent, $hard = false)
    {
        $event = $resourceOrEvent instanceof ResourceEvent ? $resourceOrEvent : $this->createResourceEvent($resourceOrEvent);
        $this->dispatcher->dispatch($this->config->getEventName('pre_delete'), $event);

        if (!$event->isPropagationStopped()) {
            $eventManager = $this->manager->getEventManager();
            $disabledListeners = [];
            if ($hard) {
                foreach ($eventManager->getListeners() as $eventName => $listeners) {
                    foreach ($listeners as $listener) {
                        if ($listener instanceof \Gedmo\SoftDeleteable\SoftDeleteableListener) {
                            $eventManager->removeEventListener($eventName, $listener);
                            $disabledListeners[$eventName] = $listener;
                        }
                    }
                }
            }

            $this->removeResource($event);

            if (!empty($disabledListeners)) {
                foreach($disabledListeners as $eventName => $listener) {
                    $eventManager->addEventListener($eventName, $listener);
                }
            }

            $this->dispatcher->dispatch($this->config->getEventName('post_delete'), $event);
        }

        return $event;
    }

    /**
     * Persists a resource.
     *
     * @param ResourceEvent $event
     *
     * @return ResourceEvent
     */
    private function persistResource(ResourceEvent $event)
    {
        $resource = $event->getResource();

        // TODO Validation ?

        try {
            $this->manager->persist($resource);
            $this->manager->flush();
        } catch(DBALException $e) {
            /*if ($this->get('kernel')->getEnvironment() === 'dev') {
                throw $e;
            }*/
            $event->addMessage(new ResourceMessage(
                'L\'application a rencontré une erreur relative à la base de données. La ressource n\'a pas été sauvegardée.',
                ResourceMessage::TYPE_ERROR
            ));
            return $event;
        }

        return $event->addMessage(new ResourceMessage(
            'La ressource a été sauvegardée avec succès.',
            ResourceMessage::TYPE_SUCCESS
        ));
    }

    /**
     * Removes a resource.
     *
     * @param ResourceEvent $event
     *
     * @return ResourceEvent
     */
    private function removeResource(ResourceEvent $event)
    {
        $resource = $event->getResource();

        try {
            $this->manager->remove($resource);
            $this->manager->flush();
        } catch(DBALException $e) {
            /*if ($this->get('kernel')->getEnvironment() === 'dev') {
                throw $e;
            }*/
            if (null !== $previous = $e->getPrevious()) {
                if ($previous instanceof \PDOException && $previous->getCode() == 23000) {
                    return $event->addMessage(new ResourceMessage(
                        'Cette ressource est liée à d\'autres ressources et ne peut pas être supprimée.',
                        ResourceMessage::TYPE_ERROR
                    ));
                }
            }
            return $event->addMessage(new ResourceMessage(
                'L\'application a rencontré une erreur relative à la base de données. La ressource n\'a pas été supprimée.',
                ResourceMessage::TYPE_ERROR
            ));
        }

        return $event->addMessage(new ResourceMessage(
            'La ressource a été supprimée avec succès.',
            ResourceMessage::TYPE_SUCCESS
        ));
    }

    /**
     * Creates the resource event.
     *
     * @param object $resource
     *
     * @return ResourceEvent
     */
    private function createResourceEvent($resource)
    {
        if (null !== $eventClass = $this->config->getEventClass()) {
            $event = new $eventClass($resource);
        } else {
            $event = new ResourceEvent();
            $event->setResource($resource);
        }
        return $event;
    }
}
