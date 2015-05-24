<?php

namespace Ekyna\Bundle\AdminBundle\Provider;

/**
 * Class LocaleProvider
 * @package Ekyna\Bundle\AdminBundle\Provider
 * @author Gonzalo Vilaseca <gvilaseca@reiss.co.uk>
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class LocaleProvider implements LocaleProviderInterface
{
    /**
     * @var string
     */
    private $currentLocale;

    /**
     * @var string
     */
    private $fallbackLocale;

    /**
     * @param string $currentLocale
     * @param string $fallbackLocale
     */
    public function __construct($currentLocale, $fallbackLocale)
    {
        $this->currentLocale = $currentLocale;
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }
}
