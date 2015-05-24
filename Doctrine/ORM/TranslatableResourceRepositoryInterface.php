<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

use Ekyna\Bundle\AdminBundle\Provider\LocaleProviderInterface;

/**
 * Interface TranslatableResourceRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableResourceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @param LocaleProviderInterface $localeProvider
     *
     * @return self
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider);

    /**
     * @param array $translatableFields
     *
     * @return self
     */
    public function setTranslatableFields(array $translatableFields);
}
