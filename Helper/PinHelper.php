<?php

namespace Ekyna\Bundle\AdminBundle\Helper;

use Doctrine\ORM\EntityManager;
use Ekyna\Bundle\AdminBundle\Entity\UserPin;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ResourcePinHelper
 * @package Ekyna\Bundle\AdminBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PinHelper
{
    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry $registry
     * @param UserProviderInterface $userProvider
     * @param ResourceHelper        $resourceHelper
     * @param EntityManager         $manager
     */
    public function __construct(
        ConfigurationRegistry $registry,
        UserProviderInterface $userProvider,
        ResourceHelper $resourceHelper,
        EntityManager $manager
    ) {
        $this->registry = $registry;
        $this->userProvider = $userProvider;
        $this->resourceHelper = $resourceHelper;
        $this->manager = $manager;
    }

    /**
     * Returns the user pins.
     *
     * @return UserPin[]
     */
    public function getUserPins()
    {
        return $this->getRepository()->findBy([
            'user' => $this->userProvider->getUser(),
        ], [
            'createdAt' => 'DESC',
        ]);
    }

    /**
     * Returns whether or not the given resource is pinned for the given user (default to current).
     *
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return bool
     */
    public function isPinnedResource(ResourceInterface $resource, UserInterface $user = null)
    {
        $user = $user ?: $this->userProvider->getUser();

        return null !== $this->findResource($resource, $user);
    }

    /**
     * Pins the given resource for the given user (default to current).
     *
     * @param ResourceInterface  $resource
     * @param UserInterface|null $user
     *
     * @return UserPin
     */
    public function pinResource(ResourceInterface $resource, UserInterface $user = null)
    {
        $user = $user ?: $this->userProvider->getUser();

        if (null === $pin = $this->findResource($resource, $user)) {
            $config = $this->registry->findConfiguration($resource);

            $pin = new UserPin();
            $pin
                ->setUser($user)
                ->setPath($this->resourceHelper->generateResourcePath($resource))
                ->setLabel((string)$resource)
                ->setResource($config->getResourceId())
                ->setIdentifier($resource->getId());

            $this->manager->persist($pin);
            $this->manager->flush();
        }

        return $pin;
    }

    /**
     * Unpins the given resource for the given user (default to current).
     *
     * @param ResourceInterface  $resource
     * @param UserInterface|null $user
     *
     * @return UserPin|null
     */
    public function unpinResource(ResourceInterface $resource, UserInterface $user = null)
    {
        $user = $user ?: $this->userProvider->getUser();

        /** @var UserPin $pin */
        if (null !== $pin = $this->findResource($resource, $user)) {
            $this->manager->remove($pin);
            $this->manager->flush();

            return $pin;
        }

        return null;
    }

    /**
     * Finds the pin for the given resource and user.
     *
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return null|object
     */
    private function findResource(ResourceInterface $resource, UserInterface $user)
    {
        $config = $this->registry->findConfiguration($resource);

        return $this->getRepository()->findOneBy([
            'user'       => $user,
            'resource'   => $config->getResourceId(),
            'identifier' => $resource->getId(),
        ]);
    }

    /**
     * Returns the pin repository.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        if (null !== $this->repository) {
            return $this->repository;
        }

        return $this->repository = $this->manager->getRepository(UserPin::class);
    }
}
