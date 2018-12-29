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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(GroupInterface $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritdoc
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShortName()
    {
        if (!empty($this->firstName) && !empty($this->lastName)) {
            return $this->firstName . ' ' . $this->lastName[0];
        }

        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        if ($this->group) {
            return $this->group->getRoles();
        }

        return ['ROLE_USER'];
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmailConfig()
    {
        return $this->emailConfig;
    }

    /**
     * @inheritdoc
     */
    public function setEmailConfig(array $emailConfig = null)
    {
        $this->emailConfig = $emailConfig;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmailSignature()
    {
        return $this->emailSignature;
    }

    /**
     * @inheritdoc
     */
    public function setEmailSignature($signature)
    {
        $this->emailSignature = $signature;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @inheritdoc
     */
    public function setPlainPassword($plain)
    {
        $this->plainPassword = $plain;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @inheritDoc
     */
    public function getSecurityId()
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
        list (
            $this->id,
            $this->email,
            $this->password,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }
}
