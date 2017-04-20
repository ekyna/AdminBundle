<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\User\Service\UserProvider;
use Symfony\Component\Mailer\Exception\ExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Email;

use function array_keys;
use function reset;

/**
 * Class MailerFactory
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MailerFactory
{
    private MailerInterface         $defaultMailer;
    private Transport               $transportFactory;
    private UserProvider            $userProvider;
    private UserRepositoryInterface $userRepository;

    /** @var MailerInterface[] */
    private array $userMailers;

    public function __construct(
        MailerInterface         $defaultMailer,
        Transport               $transportFactory,
        UserProvider            $userProvider,
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
        $from = array_keys($message->getFrom());
        $from = reset($from);

        if (!$sender && $from) {
            if ($sender = $this->userRepository->findOneByEmail($from, true)) {
                $current = $this->userProvider->getUser();

                if ($current && ($current !== $sender)) {
                    $message->getHeaders()->addTextHeader('X-Ekyna-User', $current->getEmail());
                }
            }
        }

        $mailer = $this->getUserMailer($sender);

        try {
            $mailer->send($message);
        } catch (ExceptionInterface $exception) {
            return false;
        }

        return true;
    }

    /**
     * Returns the mailer for the given user.
     */
    public function getUserMailer(?UserInterface $user): MailerInterface
    {
        if (!$user) {
            return $this->defaultMailer;
        }

        if (isset($this->userMailers[$user->getId()])) {
            return $this->userMailers[$user->getId()];
        }

        if ($mailer = $this->createUserMailer($user->getEmailConfig())) {
            return $this->userMailers[$user->getId()] = $mailer;
        }

        return $this->userMailers[$user->getId()] = $this->defaultMailer;
    }

    private function createUserMailer(array $config): ?Mailer
    {
        $scheme = $config['smtp'] === 'smtp.gmail.com' ? 'gmail' : 'smtp';

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
