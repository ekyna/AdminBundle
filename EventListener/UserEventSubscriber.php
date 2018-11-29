<?php

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\UserEvents;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\SecurityUtil;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserEventSubscriber
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorization;


    /**
     * Constructor.
     *
     * @param UserPasswordEncoderInterface  $encoder
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        UserPasswordEncoderInterface $encoder,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event)
    {
        if ($this->preventIfNotSupperAdmin($event)) {
            return;
        }

        $user = $this->getUserFromEvent($event);

        if (empty($user->getPlainPassword())) {
            $password = SecurityUtil::generatePassword($user);

            $event
                ->addMessage(new ResourceMessage(
                    sprintf('Generated password : "%s".', $password),
                    ResourceMessage::TYPE_INFO
                ))
                ->addData('password', $password);
        }

        $this->encodePassword($user);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        if ($this->preventIfNotSupperAdmin($event)) {
            return;
        }

        $user = $this->getUserFromEvent($event);

        $this->encodePassword($user);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Pre generate password event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreGeneratePassword(ResourceEventInterface $event)
    {
        $this->preventIfNotSupperAdmin($event);
    }

    /**
     * Encodes the user password.
     *
     * @param UserInterface $user
     */
    protected function encodePassword(UserInterface $user)
    {
        if (empty($plain = $user->getPlainPassword())) {
            return;
        }

        $encoded = $this->encoder->encodePassword($user, $plain);

        $user
            ->setPassword($encoded)
            ->eraseCredentials();
    }

    /**
     * Returns true if operation should be abort.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool
     */
    private function preventIfNotSupperAdmin(ResourceEventInterface $event)
    {
        if (null === $this->tokenStorage->getToken()) {
            return false;
        }

        if (!$this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            $event->addMessage(new ResourceMessage(
                'ekyna_admin.user.message.operation_denied',
                ResourceMessage::TYPE_ERROR
            ));

            return true;
        }

        return false;
    }

    /**
     * Returns the user form the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return UserInterface
     */
    protected function getUserFromEvent(ResourceEventInterface $event)
    {
        $user = $event->getResource();

        if (!$user instanceof UserInterface) {
            throw new InvalidArgumentException("Expected instance of " . UserInterface::class);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvents::PRE_CREATE            => ['onPreCreate'],
            UserEvents::PRE_UPDATE            => ['onPreUpdate'],
            UserEvents::PRE_DELETE            => ['onPreDelete'],
            UserEvents::PRE_GENERATE_PASSWORD => ['onPreGeneratePassword'],
        ];
    }
}
