<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Model;

use DateTimeInterface;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\User\Model\UserInterface as BaseUser;

/**
 * Interface UserInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface UserInterface extends BaseUser, AclSubjectInterface
{
    public function getGroup(): ?GroupInterface;

    public function setGroup(GroupInterface $group): UserInterface;

    public function getApiToken(): ?string;

    public function setApiToken(string $token = null): UserInterface;

    public function getApiExpiresAt(): ?DateTimeInterface;

    public function setApiExpiresAt(?DateTimeInterface $apiExpiresAt): UserInterface;

    public function getFirstName(): ?string;

    public function setFirstName(string $firstName = null): UserInterface;

    public function getLastName(): ?string;

    public function setLastName(string $lastName = null): UserInterface;

    public function getPosition(): ?string;

    public function setPosition(?string $position): UserInterface;

    public function getPhone(): ?string;

    public function setPhone(?string $phone): UserInterface;

    public function getMobile(): ?string;

    public function setMobile(?string $mobile): UserInterface;

    public function hasFullName(): bool;

    public function getFullName(): ?string;

    public function hasShortName(): bool;

    public function getShortName(): ?string;

    public function getEmailConfig(): ?array;

    public function setEmailConfig(array $config = null): UserInterface;
}
