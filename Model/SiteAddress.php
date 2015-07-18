<?php

namespace Ekyna\Bundle\AdminBundle\Model;

use Ekyna\Bundle\CoreBundle\Entity\AbstractAddress;

/**
 * Class SiteAddress
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SiteAddress extends AbstractAddress
{
    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $mobile;


    /**
     * Set phone
     *
     * @param string $phone
     * @return SiteAddress
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return SiteAddress
     */
    public function setMobile($mobile = null)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }
}
