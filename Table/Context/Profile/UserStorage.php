<?php

namespace Ekyna\Bundle\AdminBundle\Table\Context\Profile;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Entity\TableProfile;
use Ekyna\Component\Table\Context\Profile\ProfileInterface;
use Ekyna\Component\Table\Context\Profile\StorageInterface;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\TableInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserStorage
 * @package Ekyna\Bundle\AdminBundle\Table\Context\Profile
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserStorage implements StorageInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param EntityManagerInterface $manager
     */
    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $manager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return null !== $this->get($key);
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        return $this
            ->manager
            ->getRepository(TableProfile::class)
            ->find($key);
    }

    /**
     * @inheritDoc
     */
    public function all(TableInterface $table)
    {
        return $this
            ->manager
            ->getRepository(TableProfile::class)
            ->findBy([
                'user'      => $this->getUser(),
                'tableHash' => $table->getHash(),
            ], [
                'name' => 'ASC',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function create(TableInterface $table, $name)
    {
        if (null === $user = $this->getUser()) {
            return;
        }

        $profile = new TableProfile();
        $profile
            ->setUser($this->getUser())
            ->setTableHash($table->getHash())
            ->setData($table->getContext()->toArray())
            ->setName($name);

        $this->validateProfile($profile);

        $this->manager->persist($profile);
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function save(ProfileInterface $profile)
    {
        $this->validateProfile($profile);

        $this->manager->persist($profile);
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function remove(ProfileInterface $profile)
    {
        $this->validateProfile($profile);

        $this->manager->remove($profile);
        $this->manager->flush();
    }

    /**
     * Validates the profile.
     *
     * @param ProfileInterface $profile
     */
    private function validateProfile(ProfileInterface $profile)
    {
        if (!$profile instanceof TableProfile) {
            throw new \InvalidArgumentException("Expected instance of " . Table::class);
        }

        if (!$profile->getUser()) {
            throw new \InvalidArgumentException("Profile's user must be set.");
        }

        if (empty($profile->getTableHash())) {
            throw new \InvalidArgumentException("Profile's table hash must be set.");
        }

        if (empty($profile->getName())) {
            throw new \InvalidArgumentException("Profile's name must be set.");
        }

        if (empty($profile->getData())) {
            throw new \InvalidArgumentException("Profile's data must be set.");
        }
    }

    /**
     * Returns the current user.
     *
     * @return \Ekyna\Bundle\UserBundle\Model\UserInterface
     */
    private function getUser()
    {
        if (null !== $token = $this->tokenStorage->getToken()) {
            if (is_object($user = $token->getUser())) {
                return $user;
            }
        }

        return null;
    }
}
