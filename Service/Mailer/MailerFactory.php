<?php

namespace Ekyna\Bundle\AdminBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\UserProviderInterface;
use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerTransportFactory;
use Symfony\Component\Routing\RequestContext;

/**
 * Class MailerFactory
 * @package Ekyna\Bundle\AdminBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MailerFactory
{
    /**
     * @var \Swift_Mailer
     */
    private $defaultMailer;

    /**
     * @var \Swift_Events_EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @var \Swift_Mailer[]
     */
    private $userMailers;


    /**
     * Constructor.
     *
     * @param \Swift_Mailer                 $defaultMailer
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     * @param UserProviderInterface         $userProvider
     * @param RequestContext                $requestContext
     */
    public function __construct(
        \Swift_Mailer $defaultMailer,
        \Swift_Events_EventDispatcher $eventDispatcher,
        UserProviderInterface $userProvider,
        RequestContext $requestContext = null
    ) {
        $this->defaultMailer = $defaultMailer;
        $this->eventDispatcher = $eventDispatcher;
        $this->userProvider = $userProvider;
        $this->requestContext = $requestContext;
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * @param \Swift_Mime_Message $message
     * @param UserInterface       $sender
     * @param array               $failedRecipients An array of failures by-reference
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null, UserInterface $sender = null)
    {
        $from = array_keys($message->getFrom());
        $from = reset($from);

        if (!$sender && $from) {
            $sender = $this->userProvider->findUserByEmail($from);
            $current = $this->userProvider->getUser();

            if ($current && ($current !== $sender)) {
                $message->getHeaders()->addTextHeader('X-Ekyna-User', $current->getEmail());
            }
        }

        $mailer = $this->getUserMailer($sender);

        $sent = $mailer->send($message, $failedRecipients);

        return $sent;
    }

    /**
     * Returns the mailer for the given user.
     *
     * @param UserInterface $user
     *
     * @return \Swift_Mailer
     */
    public function getUserMailer(UserInterface $user = null)
    {
        if (!$user) {
            return $this->defaultMailer;
        }

        if (isset($this->userMailers[$user->getId()])) {
            return $this->userMailers[$user->getId()];
        }

        $config = $user->getEmailConfig();

        if (isset($config['smtp']) && !empty($config['smtp'])) {
            $transport = SwiftmailerTransportFactory::createTransport(
                array_replace(['transport' => 'smtp'], $config['smtp']),
                $this->requestContext,
                $this->eventDispatcher
            );

            return $this->userMailers[$user->getId()] = \Swift_Mailer::newInstance($transport);
        }

        return $this->userMailers[$user->getId()] = $this->defaultMailer;
    }
}
