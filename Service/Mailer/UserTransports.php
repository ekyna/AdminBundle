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
    private TransportInterface      $default;
    private Transport               $transportFactory;
    private UserRepositoryInterface $userRepository;
    private UserProviderInterface   $userProvider;
    private string                  $dsnOverride;

    /** @var array<int, TransportInterface> */
    private array               $transports = [];
    private ?TransportInterface $override = null;

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

        if (null !== $transport = $this->getUserTransport($message)) {
            return $transport->send($message, $envelope);
        }

        return $this->default->send($message, $envelope);
    }

    public function __toString(): string
    {
        return (string)$this->default;
    }

    private function getUserTransport(Email $email): ?TransportInterface
    {
        if (null === $user = $this->resolveSender($email)) {
            return null;
        }

        if (null === $id = $user->getId()) {
            return null;
        }

        $authenticated = $this->userProvider->getUser();
        if ($authenticated && ($authenticated !== $user)) {
            $email->getHeaders()->addTextHeader('X-Ekyna-Authenticated-User', $authenticated->getEmail());
        }

        if (array_key_exists($id, $this->transports)) {
            return $this->transports[$id];
        }

        return $this->transports[$id] = $this->createUserTransport($user->getEmailConfig());
    }

    private function resolveSender(Email $message): ?UserInterface
    {
        $addresses = $message->getFrom();
        foreach ($addresses as $address) {
            $email = $address->getAddress();
            if (null !== $sender = $this->userRepository->findOneByEmail($email, true)) {
                return $sender;
            }
        }

        return null;
    }

    private function createUserTransport(?array $config): ?TransportInterface
    {
        if (empty($config)) {
            return null;
        }

        if (null !== $mailer = $this->getOverrideTransport()) {
            return $mailer;
        }

        $config = array_replace([
            'host'     => '',
            'username' => '',
            'password' => '',
            'port'     => '',
        ], $config['smtp'] ?? []);

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
        } catch (ExceptionInterface $exception) {
            return null;
        }
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
