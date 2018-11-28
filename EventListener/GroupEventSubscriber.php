<?php

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\GroupEvents;
use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class GroupEventSubscriber
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorization;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event)
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Returns true if operation should be abort.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool
     */
    private function preventIfNotSupperAdmin(ResourceEventInterface $event)
    {
        if (!$this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            $event->addMessage(new ResourceMessage(
                'ekyna_admin.group.message.operation_denied',
                ResourceMessage::TYPE_ERROR
            ));

            return true;
        }

        return false;
    }

    /**
     * Returns the group form the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return GroupInterface
     */
    protected function getGroupFromEvent(ResourceEventInterface $event)
    {
        $group = $event->getResource();

        if (!$group instanceof GroupInterface) {
            throw new InvalidArgumentException("Expected instance of " . GroupInterface::class);
        }

        return $group;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            GroupEvents::PRE_CREATE => ['onPreCreate'],
            GroupEvents::PRE_UPDATE => ['onPreUpdate'],
            GroupEvents::PRE_DELETE => ['onPreDelete'],
        ];
    }
}
