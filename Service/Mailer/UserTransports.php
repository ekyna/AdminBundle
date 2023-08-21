<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\ExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

use function array_key_exists;
use function array_replace;

/**
 * Class UserTransports
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UserTransports implements TransportInterface
{
    public const HEADER_INITIAL_USER       = 'X-Ekyna-Initial-User';
    public const HEADER_AUTHENTICATED_USER = 'X-Ekyna-Authenticated-User';
    public const HEADER_IMAP_COPY          = 'X-Ekyna-Imap-Copy';

    private TransportInterface      $default;
    private Transport               $transportFactory;
    private UserRepositoryInterface $userRepository;
    private UserProviderInterface   $userProvider;
    private string                  $dsnOverride;

    /** @var array<int, TransportInterface> */
    private array               $transports = [];
    private ?TransportInterface $override   = null;

    public function __construct(
        TransportInterface      $default,
        Transport               $transportFactory,
        UserRepositoryInterface $userRepository,
        UserProviderInterface   $userProvider,
        string                  $dsnOverride = ''
    ) {
        $this->default = $default;
        $this->transportFactory = $transportFactory;
        $this->userRepository = $userRepository;
        $this->userProvider = $userProvider;
        $this->dsnOverride = $dsnOverride;
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        if (!$message instanceof Email) {
            return $this->default->send($message, $envelope);
        }

        if (null === $user = $this->resolveSender($message)) {
            return $this->default->send($message, $envelope);
        }

        if (null === $transport = $this->getUserTransport($user)) {
            return $this->default->send($message, $envelope);
        }

        if (null !== $override = $this->getOverrideTransport()) {
            $message->getHeaders()->addTextHeader(self::HEADER_INITIAL_USER, $user->getEmail());

            return $override->send($message, $envelope);
        }

        $authenticated = $this->userProvider->getUser();
        if ($authenticated && ($authenticated !== $user)) {
            $message->getHeaders()->addTextHeader(self::HEADER_AUTHENTICATED_USER, $authenticated->getEmail());
        }

        if (null !== $sent = $transport->send($message, $envelope)) {
            $this->copyToSent($user, $message);

            return $sent;
        }

        return $this->default->send($message, $envelope);
    }

    public function __toString(): string
    {
        return (string)$this->default;
    }

    private function resolveSender(Email $message): ?UserInterface
    {
        $addresses = $message->getFrom();
        foreach ($addresses as $address) {
            $email = $address->getAddress();
            if (null === $sender = $this->userRepository->findWithEmailConfig($email)) {
                continue;
            }

            if (null === $sender->getId()) {
                continue;
            }

            if (null === $sender->getEmailConfig()) {
                continue;
            }

            return $sender;
        }

        return null;
    }

    private function getUserTransport(UserInterface $user): ?TransportInterface
    {
        $email = $user->getEmail();

        if (array_key_exists($email, $this->transports)) {
            return $this->transports[$email];
        }

        return $this->transports[$email] = $this->createUserTransport($user);
    }

    private function createUserTransport(UserInterface $user): ?TransportInterface
    {
        if (empty($config = $user->getEmailConfig())) {
            return null;
        }

        if (empty($config['smtp'])) {
            return null;
        }

        $config = array_replace([
            'host'     => '',
            'username' => '',
            'password' => '',
            'port'     => '',
        ], $config['smtp']);

        $scheme = 'smtp'; // TODO Configurable ?

        $dsn = new Dsn(
            $scheme,
            $config['host'],
            $config['username'],
            $config['password'],
            $config['port']
        );

        try {
            return $this->transportFactory->fromDsnObject($dsn);
        } catch (ExceptionInterface) {
        }

        return null;
    }

    private function copyToSent(UserInterface $user, Email $message): void
    {
        if (!$message->getHeaders()->has(self::HEADER_IMAP_COPY)) {
            return;
        }

        $message->getHeaders()->remove(self::HEADER_IMAP_COPY);

        if (empty($config = $user->getEmailConfig())) {
            return;
        }

        if (empty($config['imap'])) {
            return;
        }

        $config = array_replace([
            'mailbox'  => '',
            'folder'   => '',
            'user'     => '',
            'password' => '',
        ], $config['imap']);

        if (false === $mailbox = imap_open($config['mailbox'], $config['user'], $config['password'])) {
            return;
        }

        $folder = $config['mailbox'] . mb_convert_encoding($config['folder'], 'UTF7-IMAP', 'UTF-8');

        imap_append($mailbox, $folder, $message->toString(), '\Seen');

        imap_close($mailbox);
    }

    private function getOverrideTransport(): ?TransportInterface
    {
        if (empty($this->dsnOverride)) {
            return null;
        }

        if (null !== $this->override) {
            return $this->override;
        }

        $dsn = Dsn::fromString($this->dsnOverride);

        return $this->override = $this->transportFactory->fromDsnObject($dsn);
    }
}
