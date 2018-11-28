<?php

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Service\Mailer\AdminMailer;
use Ekyna\Bundle\AdminBundle\Service\Security\UserProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Class SecurityEventSubscriber
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $securityChecker;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var AdminMailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $securityChecker
     * @param UserProviderInterface         $userProvider
     * @param AdminMailer                   $mailer
     * @param array                         $config
     */
    public function __construct(
        AuthorizationCheckerInterface $securityChecker,
        UserProviderInterface $userProvider,
        AdminMailer $mailer,
        array $config = []
    ) {
        $this->securityChecker = $securityChecker;
        $this->userProvider = $userProvider;
        $this->mailer = $mailer;

        $this->config = array_replace([
            'admin_login' => true,
        ], $config);
    }

    /**
     * Interactive login event handler.
     */
    public function onInteractiveLogin()
    {
        $this->userProvider->reset();

        $this->notifyUser();
    }

    /**
     * Notifies the user about successful interactive login.
     */
    protected function notifyUser()
    {
        if (!$this->config['admin_login']) {
            return;
        }

        if (null === $user = $this->userProvider->getUser()) {
            return;
        }

        // Skip non fully authenticated
        if (!$this->securityChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        // Only for Admins and fully authenticated
        if (!$this->securityChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $this->mailer->sendSuccessfulLoginEmailMessage($user);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin', 1024],
        ];
    }
}
