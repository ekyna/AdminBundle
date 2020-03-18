<?php

namespace Ekyna\Bundle\AdminBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class User
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class User implements UserInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var array
     */
    private $emailConfig;

    /**
     * @var string
     */
    private $emailSignature;

    /**
     * @var string
     */
    private $plainPassword;


    /**
     * @inheritDoc
     */
    public function __toString()
    {
        if (!empty($this->firstName) && !empty($this->lastName)) {
            return $this->firstName . ' ' . $this->lastName;
        }

        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getGroup(): ?GroupInterface
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(GroupInterface $group): UserInterface
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @inheritdoc
     */
    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    /**
     * @inheritdoc
     */
    public function setApiToken(string $token = null): UserInterface
    {
        $this->apiToken = $token;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName(string $firstName = null): UserInterface
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName(string $lastName = null): UserInterface
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasFullName(): bool
    {
        return !empty($this->firstName) && !empty($this->lastName);
    }

    /**
     * @inheritdoc
     */
    public function getFullName(): ?string
    {
        if ($this->hasFullName()) {
            return trim($this->firstName . ' ' . $this->lastName);
        }

        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function hasShortName(): bool
    {
        return !empty($this->firstName) && !empty($this->lastName);
    }

    /**
     * @inheritdoc
     */
    public function getShortName(): ?string
    {
        if (!empty($this->firstName) && !empty($this->lastName)) {
            return trim($this->firstName . ' ' . $this->lastName[0] . '.');
        }

        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        if ($this->group) {
            return $this->group->getRoles();
        }

        return ['ROLE_USER'];
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function setActive(bool $active): UserInterface
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmailConfig(): ?array
    {
        return $this->emailConfig;
    }

    /**
     * @inheritdoc
     */
    public function setEmailConfig(array $emailConfig = null): UserInterface
    {
        $this->emailConfig = $emailConfig;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmailSignature(): ?string
    {
        return $this->emailSignature;
    }

    /**
     * @inheritdoc
     */
    public function setEmailSignature(string $signature = null): UserInterface
    {
        $this->emailSignature = $signature;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @inheritdoc
     */
    public function setPlainPassword(string $plain = null): UserInterface
    {
        $this->plainPassword = $plain;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUsername(): ?string
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @inheritDoc
     */
    public function getSecurityId(): string
    {
        return sprintf('admin_%d', $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        [
            $this->id,
            $this->email,
            $this->password,
        ] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
