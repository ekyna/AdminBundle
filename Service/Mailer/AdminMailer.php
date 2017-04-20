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
    protected SettingManagerInterface $setting;
    protected TranslatorInterface     $translator;
    protected Environment             $twig;
    protected UrlGeneratorInterface   $urlGenerator;
    protected MailerInterface         $mailer;


    /**
     * Constructor.
     *
     * @param SettingManagerInterface $settings
     * @param TranslatorInterface     $translator
     * @param Environment             $twig
     * @param UrlGeneratorInterface   $urlGenerator
     * @param MailerInterface            $mailer
     */
    public function __construct(
        SettingManagerInterface $settings,
        TranslatorInterface $translator,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        MailerInterface $mailer
    ) {
        $this->setting = $settings;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
    }

    /**
     * Sends an email to the user to warn about successful login.
     *
     * @param UserInterface $user
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
     * @param UserInterface $user
     * @param string|null   $password
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
     *
     * @param string $recipient
     * @param string $subject
     * @param string $body
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
