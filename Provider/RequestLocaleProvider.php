<?php

namespace Ekyna\Bundle\AdminBundle\Provider;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RequestLocaleProvider
 * @package Ekyna\Bundle\AdminBundle\Provider
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class RequestLocaleProvider implements LocaleProviderInterface, EventSubscriberInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param string $defaultLocale
     */
    public function __construct($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // IMPORTANT to keep priority 34.
            KernelEvents::REQUEST => array(array('onKernelRequest', 34)),
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->request = $event->getRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLocale()
    {
        if (null === $this->request) {
            return $this->getFallbackLocale();
        }
        return $this->request->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale()
    {
        return $this->defaultLocale;
    }
}
