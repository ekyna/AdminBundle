<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Pin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Entity\UserPin;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\User\Service\UserProviderInterface;

/**
 * Class ResourcePinHelper
 * @package Ekyna\Bundle\AdminBundle\Service\Pin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PinHelper
{
    private ResourceRegistryInterface $registry;
    private UserProviderInterface     $userProvider;
    private ResourceHelper            $resourceHelper;
    private EntityManagerInterface    $manager;

    private ?EntityRepository $repository = null;

    public function __construct(
        ResourceRegistryInterface $registry,
        UserProviderInterface     $userProvider,
        ResourceHelper            $resourceHelper,
        EntityManagerInterface    $manager
    ) {
        $this->registry = $registry;
        $this->userProvider = $userProvider;
        $this->resourceHelper = $resourceHelper;
        $this->manager = $manager;
    }

    /**
     * Returns the user pins.
     *
     * @return array<UserPin>
     */
    public function getUserPins(): array
    {
        return $this->getRepository()->findBy([
            'user' => $this->userProvider->getUser(),
        ], [
            'createdAt' => 'DESC',
        ]);
    }

    /**
     * Returns whether the given resource is pinned for the given user (default to current).
     */
    public function isPinnedResource(ResourceInterface $resource, UserInterface $user = null): bool
    {
        $user = $user ?: $this->userProvider->getUser();

        return null !== $this->findPin($resource, $user);
    }

    /**
     * Pins the given resource for the given user (default to current).
     */
    public function pinResource(ResourceInterface $resource, UserInterface $user = null): array
    {
        $user = $user ?: $this->userProvider->getUser();

        if (null === $userPin = $this->findPin($resource, $user)) {
            $config = $this->registry->find($resource);

            $userPin = new UserPin();
            $userPin
                ->setUser($user)
                ->setPath($this->resourceHelper->generateResourcePath($resource, ReadAction::class))
                ->setLabel((string)$resource)
                ->setResource($config->getId())
                ->setIdentifier((string)$resource->getId());

            $this->persistPin($userPin);
        }

        return $userPin->toArray();
    }

    /**
     * Unpins the given resource for the given user (default to current).
     */
    public function unpinResource(ResourceInterface $resource, UserInterface $user = null): ?array
    {
        $user = $user ?: $this->userProvider->getUser();

        if (null !== $userPin = $this->findPin($resource, $user)) {
            $data = $userPin->toArray();

            $this->removePin($userPin);

            return $data;
        }

        return null;
    }

    /**
     * Removes the user pin.
     */
    public function persistPin(UserPin $userPin): void
    {
        $this->manager->persist($userPin);
        $this->manager->flush();
    }

    /**
     * Removes the user pin.
     */
    public function removePin(UserPin $userPin): void
    {
        $this->manager->remove($userPin);
        $this->manager->flush();
    }

    /**
     * Returns the pin repository.
     *
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        if (null !== $this->repository) {
            return $this->repository;
        }

        return $this->repository = $this->manager->getRepository(UserPin::class);
    }

    /**
     * Finds the pin for the given resource and user.
     *
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return UserPin|null
     */
    private function findPin(ResourceInterface $resource, UserInterface $user): ?UserPin
    {
        $config = $this->registry->find($resource);

        return $this->getRepository()->findOneBy([
            'user'       => $user,
            'resource'   => $config->getId(),
            'identifier' => $resource->getId(),
        ]);
    }
}
