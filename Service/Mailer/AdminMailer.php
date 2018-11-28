<?php

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AdminMailer
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdminMailer
{
    /**
     * @var SettingsManagerInterface
     */
    private $settings;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;


    /**
     * Constructor.
     *
     * @param SettingsManagerInterface $settings
     * @param TranslatorInterface      $translator
     * @param EngineInterface          $templating
     * @param UrlGeneratorInterface    $urlGenerator
     * @param \Swift_Mailer            $mailer
     */
    public function __construct(
        SettingsManagerInterface $settings,
        TranslatorInterface $translator,
        EngineInterface $templating,
        UrlGeneratorInterface $urlGenerator,
        \Swift_Mailer $mailer
    ) {
        $this->settings = $settings;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
    }

    /**
     * Sends an email to the user to warn about successful login.
     *
     * @param UserInterface $user
     */
    public function sendSuccessfulLoginEmailMessage(UserInterface $user)
    {
        $siteName = $this->settings->getParameter('general.site_name');

        $rendered = $this->templating->render('@EkynaAdmin/Email/login_success.html.twig', [
            'sitename' => $siteName,
            'date'     => new \DateTime(),
        ]);

        $subject = $this->translator->trans('ekyna_admin.email.login_success.subject', [
            '%sitename%' => $siteName,
        ]);

        $this->sendEmail($rendered, $user->getEmail(), $subject);
    }

    /**
     * Sends an email to the user to warn about the new password.
     *
     * @param UserInterface $user
     * @param string        $password
     *
     * @return integer
     */
    public function sendNewPasswordEmailMessage(UserInterface $user, $password)
    {
        $siteName = $this->settings->getParameter('general.site_name');
        $login = $user->getUsername();

        if (0 === strlen($password)) {
            return 0;
        }

        $rendered = $this->templating->render('@EkynaAdmin/Email/new_password_email.html.twig', [
            'sitename'  => $siteName,
            'login_url' => $this->urlGenerator->generate('ekyna_admin_security_login'),
            'login'     => $login,
            'password'  => $password,
        ]);

        $subject = $this->translator->trans('ekyna_user.email.new_password.subject', [
            '%sitename%' => $siteName,
        ]);

        return $this->sendEmail($rendered, $user->getEmail(), $subject);
    }

    /**
     * Sends the message.
     *
     * @param string $renderedTemplate
     * @param string $toEmail
     * @param string $subject
     *
     * @return integer
     */
    protected function sendEmail($renderedTemplate, $toEmail, $subject)
    {
        $fromEmail = $this->settings->getParameter('notification.from_email');
        $fromName = $this->settings->getParameter('notification.from_name');

        $message = new \Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setReplyTo($fromEmail, $fromName)
            ->setTo($toEmail)
            ->setBody($renderedTemplate, 'text/html');

        return $this->mailer->send($message);
    }
}
