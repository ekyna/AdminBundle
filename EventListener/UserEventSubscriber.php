<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\UserEvents;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\User\EventListener\UserEventSubscriber as BaseListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UserEventSubscriber
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserEventSubscriber extends BaseListener implements EventSubscriberInterface
{
    protected function getUserFromEvent(ResourceEventInterface $event): UserInterface
    {
        $user = $event->getResource();

        if (!$user instanceof UserInterface) {
            throw new UnexpectedTypeException($user, UserInterface::class);
        }

        return $user;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::PRE_CREATE => ['onPreCreate'],
            UserEvents::INSERT     => ['onInsert'],
            UserEvents::UPDATE     => ['onUpdate'],
        ];
    }
}
