<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\GroupEvents;
use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class GroupEventSubscriber
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupEventSubscriber implements EventSubscriberInterface
{
    protected TokenStorageInterface         $tokenStorage;
    protected AuthorizationCheckerInterface $authorization;


    /**
     * Constructor.
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorization)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event): void
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event): void
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
    private function preventIfNotSupperAdmin(ResourceEventInterface $event): bool
    {
        if (null === $this->tokenStorage->getToken()) {
            return false;
        }

        if (!$this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            $event->addMessage(
                ResourceMessage::create(
                    'group.message.operation_denied',
                    ResourceMessage::TYPE_ERROR
                )->setDomain('EkynaAdmin')
            );

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
    protected function getGroupFromEvent(ResourceEventInterface $event): GroupInterface
    {
        $group = $event->getResource();

        if (!$group instanceof GroupInterface) {
            throw new UnexpectedTypeException($group, GroupInterface::class);
        }

        return $group;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GroupEvents::PRE_CREATE => ['onPreCreate'],
            GroupEvents::PRE_UPDATE => ['onPreUpdate'],
            GroupEvents::PRE_DELETE => ['onPreDelete'],
        ];
    }
}
