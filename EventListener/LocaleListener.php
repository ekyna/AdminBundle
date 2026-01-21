<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

use function str_starts_with;

/**
 * Class LocaleListener
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LocaleListener
{
    public function __construct(
        private readonly string $routingPrefix,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        if (!str_starts_with($request->getPathInfo(), $this->routingPrefix)) {
            return;
        }

        $session = $request->getSession();

        if (!$session->has('admin_locale')) {
            return;
        }

        $event->getRequest()->attributes->set('_locale', $session->get('admin_locale'));
    }
}
