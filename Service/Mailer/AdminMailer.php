<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use DateTime;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class AdminMailer
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdminMailer
{
    public function __construct(
        private readonly MailerHelper          $helper,
        private readonly TranslatorInterface   $translator,
        private readonly Environment           $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailerInterface       $mailer
    ) {
    }

    /**
     * Sends an email to the user to warn about successful login.
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function sendSuccessfulLoginEmail(UserInterface $user): void
    {
        $siteName = $this->helper->getSiteName();

        /** @noinspection PhpUnhandledExceptionInspection */
        $rendered = $this->twig->render('@EkynaAdmin/Email/login_success.html.twig', [
            'sitename' => $siteName,
            'date'     => new DateTime(),
        ]);

        $subject = $this->translator->trans('email.login_success.subject', [
            '%sitename%' => $siteName,
        ], 'EkynaAdmin');

        $this->sendEmail($user->getEmail(), $subject, $rendered);
    }

    /**
     * Sends an email to the user to warn about the new password.
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function sendNewPasswordEmail(UserInterface $user, string $password = null): void
    {
        $siteName = $this->helper->getSiteName();
        $login = $user->getUsername();

        if (empty($password)) {
            return;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $rendered = $this->twig->render('@EkynaAdmin/Email/new_password_email.html.twig', [
            'sitename'  => $siteName,
            'login_url' => $this->urlGenerator->generate('admin_security_login'),
            'login'     => $login,
            'password'  => $password,
        ]);

        $subject = $this->translator->trans('email.new_password.subject', [
            '%sitename%' => $siteName,
        ], 'EkynaAdmin');

        $this->sendEmail($user->getEmail(), $subject, $rendered);
    }

    /**
     * Sends the message.
     */
    protected function sendEmail(string $recipient, string $subject, string $body): void
    {
        $sender = $this->helper->getNotificationSender();

        $message = new Email();
        $message
            ->from($sender)
            ->replyTo($sender)
            ->to($recipient)
            ->subject($subject)
            ->html($body);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->mailer->send($message);
    }
}
