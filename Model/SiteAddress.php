<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Model;

/**
 * Class SiteAddress
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SiteAddress
{
    protected ?string $street     = null;
    protected ?string $supplement = null;
    protected ?string $postalCode = null;
    protected ?string $city       = null;
    protected string  $country    = 'US';
    protected ?string $state      = null;
    protected ?string $phone      = null;
    protected ?string $mobile     = null;
    protected ?string $longitude  = null;
    protected ?string $latitude   = null;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): SiteAddress
    {
        $this->street = $street;

        return $this;
    }

    public function getSupplement(): ?string
    {
        return $this->supplement;
    }

    public function setSupplement(?string $supplement): SiteAddress
    {
        $this->supplement = $supplement;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): SiteAddress
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): SiteAddress
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): SiteAddress
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): SiteAddress
    {
        $this->state = $state;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): SiteAddress
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): SiteAddress
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): SiteAddress
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): SiteAddress
    {
        $this->latitude = $latitude;

        return $this;
    }
}
