<?php

namespace Ekyna\Bundle\AdminBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model as RB;
use Ekyna\Component\Resource\Model as RC;

/**
 * Interface UserInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface UserInterface extends RC\ResourceInterface, RB\UserInterface, RC\TimestampableInterface, \Serializable
{
    /**
     * Returns the group.
     *
     * @return GroupInterface
     */
    public function getGroup(): ?GroupInterface;

    /**
     * Sets the group.
     *
     * @param GroupInterface $group
     *
     * @return $this|UserInterface
     */
    public function setGroup(GroupInterface $group): UserInterface;

    /**
     * Returns the email.
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return $this|UserInterface
     */
    public function setEmail(string $email): UserInterface;

    /**
     * Sets the password.
     *
     * @param string $password
     *
     * @return $this|UserInterface
     */
    public function setPassword(string $password): UserInterface;

    /**
     * Returns the api token.
     *
     * @return string|null
     */
    public function getApiToken(): ?string;

    /**
     * Sets the api token.
     *
     * @param string|null $token
     *
     * @return UserInterface
     */
    public function setApiToken(string $token = null): UserInterface;

    /**
     * Returns the first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string;

    /**
     * Sets the first name.
     *
     * @param string|null $firstName
     *
     * @return $this|UserInterface
     */
    public function setFirstName(string $firstName = null): UserInterface;

    /**
     * Returns the lastName.
     *
     * @return string|null
     */
    public function getLastName(): ?string;

    /**
     * Sets the lastName.
     *
     * @param string|null $lastName
     *
     * @return $this|UserInterface
     */
    public function setLastName(string $lastName = null);

    /**
     * Returns whether the user has a full name.
     *
     * @return bool
     */
    public function hasFullName(): bool;

    /**
     * Returns the user full name.
     *
     * @return string|null
     */
    public function getFullName(): ?string;

    /**
     * Returns whether the user has a short name.
     *
     * @return bool
     */
    public function hasShortName(): bool;

    /**
     * Returns the user short name.
     *
     * @return string|null
     */
    public function getShortName(): ?string;

    /**
     * Returns the email (imap) configuration.
     *
     * @return array|null
     */
    public function getEmailConfig(): ?array;

    /**
     * Sets the email (imap) configuration.
     *
     * @param array|null $imap
     *
     * @return $this|UserInterface
     */
    public function setEmailConfig(array $imap = null): UserInterface;

    /**
     * Returns the email signature.
     *
     * @return string|null
     */
    public function getEmailSignature(): ?string;

    /**
     * Sets the email signature.
     *
     * @param string|null $signature
     *
     * @return $this|UserInterface
     */
    public function setEmailSignature(string $signature = null): UserInterface;

    /**
     * Returns whether the user is active.
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Sets whether the user is active.
     *
     * @param bool $active
     *
     * @return $this|UserInterface
     */
    public function setActive(bool $active): UserInterface;

    /**
     * Returns the plain password.
     *
     * @return string|null
     */
    public function getPlainPassword(): ?string;

    /**
     * Sets the plain password.
     *
     * @param string|null $plain
     *
     * @return $this|UserInterface
     */
    public function setPlainPassword(string $plain = null): UserInterface;
}
