<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Mailer\Exception\ExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Email;

use function array_replace;
use function urlencode;

/**
 * Class MailerFactory
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MailerFactory
{
    private MailerInterface         $defaultMailer;
    private Transport               $transportFactory;
    private UserProviderInterface   $userProvider;
    private UserRepositoryInterface $userRepository;

    /** @var MailerInterface[] */
    private array $userMailers;

    public function __construct(
        MailerInterface         $defaultMailer,
        Transport               $transportFactory,
        UserProviderInterface   $userProvider,
        UserRepositoryInterface $userRepository
    ) {
        $this->defaultMailer = $defaultMailer;
        $this->transportFactory = $transportFactory;
        $this->userProvider = $userProvider;
        $this->userRepository = $userRepository;
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     */
    public function send(Email $message, UserInterface $sender = null): bool
    {
        if (null === $sender) {
            $sender = $this->resolveSender($message);
        }

        $current = $this->userProvider->getUser();
        if ($sender && $current && ($current !== $sender)) {
            $message->getHeaders()->addTextHeader('X-Ekyna-User', $current->getEmail());
        }

        $mailer = $this->getUserMailer($sender);

        try {
            $mailer->send($message);
        } catch (ExceptionInterface $exception) {
            return false;
        }

        return true;
    }

    private function resolveSender(Email $message): ?UserInterface
    {
        $addresses = $message->getFrom();
        foreach ($addresses as $address) {
            $email = $address->getAddress();
            if ($sender = $this->userRepository->findOneByEmail($email, true)) {
                return $sender;
            }
        }

        return null;
    }

    /**
     * Returns the mailer for the given user.
     */
    public function getUserMailer(?UserInterface $user): MailerInterface
    {
        if (null === $user) {
            return $this->defaultMailer;
        }

        if (isset($this->userMailers[$userId = $user->getId()])) {
            return $this->userMailers[$userId];
        }

        if ($mailer = $this->createUserMailer($user->getEmailConfig())) {
            return $this->userMailers[$userId] = $mailer;
        }

        return $this->userMailers[$userId] = $this->defaultMailer;
    }

    public function getDefaultMailer(): MailerInterface
    {
        return $this->defaultMailer;
    }

    private function createUserMailer(array $config): ?Mailer
    {
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
            $transport = $this->transportFactory->fromDsnObject($dsn);
        } catch (ExceptionInterface $exception) {
            return null;
        }

        return new Mailer($transport); // TODO bus / dispatcher
    }
}
