<?php

namespace Ekyna\Bundle\AdminBundle\Model;

/**
 * Trait TranslatableTrait
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslatableTrait
{
    /**
     * Translations.
     *
     * @var \Doctrine\Common\Collections\Collection|TranslationInterface[]
     */
    protected $translations;

    /**
     * Current locale.
     *
     * @var string
     */
    protected $currentLocale;

    /**
     * Cache current translation. Useful in Doctrine 2.4+
     *
     * @var TranslationInterface
     */
    protected $currentTranslation;

    /**
     * Fallback locale.
     *
     * @var string
     */
    protected $fallbackLocale;


    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->containsKey($translation->getLocale())) {
            $this->translations->set($translation->getLocale(), $translation);
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        if ($this->translations->removeElement($translation)) {
            $translation->setTranslatable(null);
        }

        return $this;
    }

    /**
     * @param TranslationInterface $translation
     *
     * @return bool
     */
    public function hasTranslation(TranslationInterface $translation)
    {
        return $this->translations->containsKey($translation->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentLocale($currentLocale)
    {
        $this->currentLocale = $currentLocale;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setFallbackLocale($fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;

        return $this;
    }

    /**
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($locale = null)
    {
        $locale = $locale ?: $this->currentLocale;
        if (null === $locale) {
            throw new \RuntimeException('No locale has been set and current locale is undefined.');
        }

        if ($this->currentTranslation && $locale === $this->currentTranslation->getLocale()) {
            return $this->currentTranslation;
        }

        if (!$translation = $this->translations->get($locale)) {
            if (null === $this->fallbackLocale) {
                throw new \RuntimeException('No fallback locale has been set.');
            }

            if (!$fallbackTranslation = $this->translations->get($this->getFallbackLocale())) {
                $className = $this->getTranslationClass();

                /** @var TranslationInterface $translation */
                $translation = new $className();
                $translation->setLocale($locale);

                $this->addTranslation($translation);
            } else {
                $translation = clone $fallbackTranslation;
            }
        }

        $this->currentTranslation = $translation;

        return $translation;
    }

    /**
     * Return translation model class.
     *
     * @return string
     */
    protected function getTranslationClass()
    {
        return get_class($this).'Translation';
    }
}
