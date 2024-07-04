<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Mime\Address;

use function array_map;

/**
 * Class AddressHelper
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressHelper
{
    /** @var array<int, Address>|null */
    private ?array   $notificationRecipients = null;
    private ?Address $notificationSender     = null;
    private ?Address $noReply                = null;
    private ?string  $siteName               = null;

    public function __construct(
        private readonly SettingManagerInterface $setting,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserProviderInterface   $userProvider,
    ) {
    }

    public function getCurrentUserSender(): ?Address
    {
        if (null === $user = $this->userProvider->getUser()) {
            return null;
        }

        /** @var UserInterface $user */
        return $user->toAddress();
    }

    /**
     * @return array<int, Address>
     */
    public function getNotificationRecipients(): array
    {
        if (null !== $this->notificationRecipients) {
            return $this->notificationRecipients;
        }

        return $this->notificationRecipients = array_map(
            fn(string $email): Address => new Address($email),
            $this->setting->getParameter('notification.to_emails')
        );
    }

    /**
     * @return Address
     */
    public function getNotificationSender(): Address
    {
        if (null !== $this->notificationSender) {
            return $this->notificationSender;
        }

        return $this->notificationSender = new Address(
            $this->setting->getParameter('notification.from_email'),
            $this->setting->getParameter('notification.from_name')
        );
    }

    /**
     * @return Address
     */
    public function getNoReply(): Address
    {
        if (null !== $this->noReply) {
            return $this->noReply;
        }

        return $this->noReply = new Address(
            $this->setting->getParameter('notification.no_reply')
        );
    }

    /**
     * @return string
     */
    public function getSiteName(): string
    {
        if (null !== $this->siteName) {
            return $this->siteName;
        }

        return $this->siteName = $this->setting->getParameter('general.site_name');
    }
}
