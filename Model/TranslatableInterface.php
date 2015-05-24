<?php

namespace Ekyna\Bundle\AdminBundle\Model;

/**
 * Interface TranslatableInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface TranslatableInterface
{
    /**
     * Translation helper method.
     *
     * @param string $locale
     *
     * @return TranslationInterface
     *
     * @throws \RuntimeException
     */
    public function translate($locale = null);

    /**
     * Set current locale.
     *
     * @param string $locale
     *
     * @return self
     */
    public function setCurrentLocale($locale);

    /**
     * Set fallback locale.
     *
     * @param string $locale
     *
     * @return self
     */
    public function setFallbackLocale($locale);

    /**
     * Get all translations.
     *
     * @return TranslationInterface[]
     */
    public function getTranslations();

    /**
     * Add a new translation.
     *
     * @param TranslationInterface $translation
     *
     * @return self
     */
    public function addTranslation(TranslationInterface $translation);

    /**
     * Remove a translation.
     *
     * @param TranslationInterface $translation
     *
     * @return self
     */
    public function removeTranslation(TranslationInterface $translation);
}
