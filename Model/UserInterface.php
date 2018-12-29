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
    public function getGroup();

    /**
     * Sets the group.
     *
     * @param GroupInterface $group
     *
     * @return $this|UserInterface
     */
    public function setGroup(GroupInterface $group);

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return $this|UserInterface
     */
    public function setEmail($email);

    /**
     * Sets the password.
     *
     * @param string $password
     *
     * @return $this|UserInterface
     */
    public function setPassword($password);

    /**
     * Returns the firstName.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Sets the firstName.
     *
     * @param string $firstName
     *
     * @return $this|UserInterface
     */
    public function setFirstName($firstName);

    /**
     * Returns the lastName.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Sets the lastName.
     *
     * @param string $lastName
     *
     * @return $this|UserInterface
     */
    public function setLastName($lastName);

    /**
     * Returns the user short name.
     *
     * @return string
     */
    public function getShortName();

    /**
     * Returns the imap.
     *
     * @return array
     */
    public function getEmailConfig();

    /**
     * Sets the imap.
     *
     * @param array $imap
     *
     * @return $this|UserInterface
     */
    public function setEmailConfig(array $imap = null);

    /**
     * Returns the email signature.
     *
     * @return string
     */
    public function getEmailSignature();

    /**
     * Sets the email signature.
     *
     * @param string $signature
     *
     * @return $this|UserInterface
     */
    public function setEmailSignature($signature);

    /**
     * Returns whether the user is active.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Sets whether the user is active.
     *
     * @param bool $active
     *
     * @return $this|UserInterface
     */
    public function setActive($active);

    /**
     * Returns the plain password.
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * Sets the plain password.
     *
     * @param string $plain
     *
     * @return $this|UserInterface
     */
    public function setPlainPassword($plain);
}
