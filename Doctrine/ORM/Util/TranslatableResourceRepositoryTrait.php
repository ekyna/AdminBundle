<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderInterface;
use Ekyna\Bundle\AdminBundle\Model\TranslatableInterface;

/**
 * Class TranslatableResourceRepositoryTrait
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait TranslatableResourceRepositoryTrait
{
    use ResourceRepositoryTrait {
        createNew as traitCreateNew;
        getQueryBuilder as traitGetQueryBuilder;
        getCollectionQueryBuilder as traitGetCollectionQueryBuilder;
        getPropertyName as traitGetPropertyName;
    }

    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var array
     */
    protected $translatableFields = array();


    /**
     * {@inheritdoc}
     */
    protected function getQueryBuilder()
    {
        $queryBuilder = $this->traitGetQueryBuilder();

        $queryBuilder
            ->addSelect('translation')
            ->leftJoin($this->getAlias() . '.translations', 'translation')
        ;

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollectionQueryBuilder()
    {
        $queryBuilder = $this->traitGetCollectionQueryBuilder();

        $queryBuilder
            ->addSelect('translation')
            ->leftJoin($this->getAlias() . '.translations', 'translation')
        ;

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        $resource = $this->traitCreateNew();

        if (!$resource instanceof TranslatableInterface) {
            throw new \InvalidArgumentException('Resource must implement TranslatableInterface.');
        }

        $resource->setCurrentLocale($this->localeProvider->getCurrentLocale());
        $resource->setFallbackLocale($this->localeProvider->getFallbackLocale());

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocaleProvider(LocaleProviderInterface $provider)
    {
        $this->localeProvider = $provider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslatableFields(array $translatableFields)
    {
        $this->translatableFields = $translatableFields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPropertyName($name)
    {
        if (in_array($name, $this->translatableFields)) {
            return 'translation.'.$name;
        }
        return $this->traitGetPropertyName($name);
    }
}
