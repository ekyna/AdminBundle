<?php

namespace Ekyna\Bundle\AdminBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Trait TranslationTrait
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslationTrait
{
    /**
     * @var string
     * @JMS\Exclude()
     */
    protected $locale;

    /**
     * @var TranslatableInterface
     * @JMS\Exclude()
     */
    protected $translatable;


    /**
     * Sets the translatable.
     *
     * @param TranslatableInterface $translatable
     * @return TranslationInterface|$this
     */
    public function setTranslatable(TranslatableInterface $translatable = null)
    {
        if ($translatable === $this->translatable) {
            return $this;
        }

        $previousTranslatable = $this->translatable;
        $this->translatable = $translatable;

        /** @var TranslationInterface $this */
        if (null !== $previousTranslatable) {
            $previousTranslatable->removeTranslation($this);
        }

        if (null !== $translatable) {
            $translatable->addTranslation($this);
        }

        return $this;
    }

    /**
     * Returns the translatable.
     *
     * @return TranslatableInterface
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     * @return TranslatableInterface|$this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
