<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Mailer\AdminMailer;
use Ekyna\Bundle\AdminBundle\Service\Security\DevAuthenticator;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\User\EventListener\SecurityEventListener as BaseListener;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

use function array_replace;

/**
 * Class SecurityEventListener
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityEventListener extends BaseListener
{
    private AuthorizationCheckerInterface $securityChecker;
    private AdminMailer                   $mailer;
    private array                         $config;

    public function __construct(
        UserProviderInterface         $userUserProvider,
        ResourceManagerInterface      $userManager,
        AuthorizationCheckerInterface $securityChecker,
        AdminMailer                   $mailer,
        array                         $config
    ) {
        parent::__construct($userUserProvider, $userManager);

        $this->securityChecker = $securityChecker;
        $this->mailer = $mailer;

        $this->config = array_replace([
            'admin_login' => true,
        ], $config);
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        parent::onLoginSuccess($event);

        // Do not notify when using dev authenticator
        if ($event->getRequest()->headers->has(DevAuthenticator::HEADER)) {
            return;
        }

        $this->notifyUser();
    }

    /**
     * Notifies the user about successful interactive login.
     */
    private function notifyUser(): void
    {
        if (!$this->config['admin_login']) {
            return;
        }

        if (null === $user = $this->userProvider->getUser()) {
            return;
        }

        /** @var UserInterface $user */

        // Skip non fully authenticated
        if (!$this->securityChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)) {
            return;
        }

        // Only for Admins and fully authenticated
        if (!$this->securityChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $this->mailer->sendSuccessfulLoginEmail($user);
    }
}
