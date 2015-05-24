<?php

namespace Ekyna\Bundle\AdminBundle\Model;

/**
 * Interface TranslationInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslationInterface
{
    /**
     * Get the translatable object.
     *
     * @return TranslatableInterface
     */
    public function getTranslatable();

    /**
     * Set the translatable object.
     *
     * @param null|TranslatableInterface $translatable
     *
     * @return self
     */
    public function setTranslatable(TranslatableInterface $translatable = null);

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale);
}
