<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use DateTime;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
        protected readonly SettingManagerInterface $setting,
        protected readonly TranslatorInterface $translator,
        protected readonly Environment $twig,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly MailerInterface $mailer
    ) {
    }

    /**
     * Sends an email to the user to warn about successful login.
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function sendSuccessfulLoginEmail(UserInterface $user): void
    {
        $siteName = $this->setting->getParameter('general.site_name');

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
        $siteName = $this->setting->getParameter('general.site_name');
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
        $fromEmail = $this->setting->getParameter('notification.from_email');
        $fromName = $this->setting->getParameter('notification.from_name');

        $sender = new Address($fromEmail, $fromName);

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
