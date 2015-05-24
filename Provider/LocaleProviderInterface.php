<?php

namespace Ekyna\Bundle\AdminBundle\Provider;

/**
 * Interface LocaleProviderInterface
 * @package Ekyna\Bundle\AdminBundle\Provider
 * @author Gonzalo Vilaseca <gvilaseca@reiss.co.uk>
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface LocaleProviderInterface
{
    /**
     * @return string
     */
    public function getCurrentLocale();

    /**
     * @return string
     */
    public function getFallbackLocale();
}
