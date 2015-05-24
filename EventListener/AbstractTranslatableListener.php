<?php

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Provider\LocaleProviderInterface;

/**
 * Class AbstractTranslatableListener
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatableListener
{
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * Mapping.
     *
     * @var array
     */
    protected $configs;

    public function __construct(LocaleProviderInterface $localeProvider, array $configs)
    {
        $this->localeProvider = $localeProvider;
        $this->configs = $configs;
    }
}
