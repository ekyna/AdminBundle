<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class UserLocaleListener
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserLocaleListener
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function onInteractiveLoginEvent(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        if (null !== $locale = $user->getLocale()) {
            $this->requestStack->getSession()->set('admin_locale', $locale);
        }
    }
}
