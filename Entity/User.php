<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Entity;

use DateTimeInterface;
use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectTrait;
use Ekyna\Component\User\Model\AbstractUser;
use Symfony\Component\Mime\Address;

/**
 * Class User
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class User extends AbstractUser implements UserInterface
{
    use AclSubjectTrait;

    protected ?GroupInterface    $group        = null;
    protected ?string            $apiToken     = null;
    protected ?DateTimeInterface $apiExpiresAt = null;
    protected ?string            $firstName    = null;
    protected ?string            $lastName     = null;
    protected ?string            $position     = null;
    protected ?string            $phone        = null;
    protected ?string            $mobile       = null;
    protected ?array             $emailConfig  = null;

    public function __toString(): string
    {
        if (!empty($this->firstName) && !empty($this->lastName)) {
            return $this->firstName . ' ' . $this->lastName;
        }

        return parent::__toString();
    }

    public function getGroup(): ?GroupInterface
    {
        return $this->group;
    }

    public function setGroup(GroupInterface $group): UserInterface
    {
        $this->group = $group;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $token = null): UserInterface
    {
        $this->apiToken = $token;

        return $this;
    }

    public function getApiExpiresAt(): ?DateTimeInterface
    {
        return $this->apiExpiresAt;
    }

    public function setApiExpiresAt(?DateTimeInterface $apiExpiresAt): UserInterface
    {
        $this->apiExpiresAt = $apiExpiresAt;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName = null): UserInterface
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName = null): UserInterface
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): UserInterface
    {
        $this->position = $position;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): UserInterface
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): UserInterface
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function hasFullName(): bool
    {
        return !empty($this->firstName) && !empty($this->lastName);
    }

    public function getFullName(): ?string
    {
        if ($this->hasFullName()) {
            return trim($this->firstName . ' ' . $this->lastName);
        }

        return $this->email;
    }

    public function hasShortName(): bool
    {
        return !empty($this->firstName) && !empty($this->lastName);
    }

    public function getShortName(): ?string
    {
        if (!empty($this->firstName) && !empty($this->lastName)) {
            return trim($this->firstName . ' ' . $this->lastName[0] . '.');
        }

        return $this->email;
    }

    public function getRoles(): array
    {
        if ($this->group) {
            return $this->group->getRoles();
        }

        return ['ROLE_USER'];
    }

    public function getEmailConfig(): ?array
    {
        return $this->emailConfig;
    }

    public function setEmailConfig(array $config = null): UserInterface
    {
        $this->emailConfig = $config;

        return $this;
    }

    public function toAddress(): ?Address
    {
        return new Address($this->getEmail(), $this->hasFullName() ? $this->getFullName() : '');
    }

    public function getAclParentSubject(): ?AclSubjectInterface
    {
        return $this->group;
    }
}
