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


    /**
     * Returns the street.
     *
     * @return string
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * Sets the street.
     *
     * @param string|null $street
     *
     * @return SiteAddress
     */
    public function setStreet(?string $street): SiteAddress
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Returns the supplement.
     *
     * @return string
     */
    public function getSupplement(): ?string
    {
        return $this->supplement;
    }

    /**
     * Sets the supplement.
     *
     * @param string|null $supplement
     *
     * @return SiteAddress
     */
    public function setSupplement(?string $supplement): SiteAddress
    {
        $this->supplement = $supplement;

        return $this;
    }

    /**
     * Returns the postal code.
     *
     * @return string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * Sets the postal code.
     *
     * @param string|null $postalCode
     *
     * @return SiteAddress
     */
    public function setPostalCode(?string $postalCode): SiteAddress
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Returns the city.
     *
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Sets the city.
     *
     * @param string|null $city
     *
     * @return SiteAddress
     */
    public function setCity(?string $city): SiteAddress
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Returns the country.
     *
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Sets the country.
     *
     * @param string $country
     *
     * @return SiteAddress
     */
    public function setCountry(string $country): SiteAddress
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Sets the state.
     *
     * @param string|null $state
     *
     * @return SiteAddress
     */
    public function setState(?string $state): SiteAddress
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Returns the phone number.
     *
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Sets the phone number.
     *
     * @param string|null $phone
     *
     * @return SiteAddress
     */
    public function setPhone(?string $phone): SiteAddress
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Returns the mobile number.
     *
     * @return string
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * Sets the mobile number.
     *
     * @param string|null $mobile
     *
     * @return SiteAddress
     */
    public function setMobile(?string $mobile): SiteAddress
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Returns the longitude.
     *
     * @return string
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude.
     *
     * @param string|null $longitude
     *
     * @return SiteAddress
     */
    public function setLongitude(?string $longitude): SiteAddress
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Returns the latitude.
     *
     * @return string
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude.
     *
     * @param string|null $latitude
     *
     * @return SiteAddress
     */
    public function setLatitude(?string $latitude): SiteAddress
    {
        $this->latitude = $latitude;

        return $this;
    }
}
