<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $qb = $this->traitGetQueryBuilder();

        $qb
            ->addSelect('translation')
            ->leftJoin($this->getAlias() . '.translations', 'translation')
            ->andWhere($qb->expr()->eq(
                'translation.locale',
                $qb->expr()->literal($this->localeProvider->getCurrentLocale())
            ))
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollectionQueryBuilder()
    {
        $qb = $this->traitGetCollectionQueryBuilder();

        $qb
            ->addSelect('translation')
            ->leftJoin($this->getAlias() . '.translations', 'translation')
            ->andWhere($qb->expr()->eq(
                'translation.locale',
                $qb->expr()->literal($this->localeProvider->getCurrentLocale())
            ))
        ;

        return $qb;
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

    /**
     * @param Query $query
     * @return array
     */
    protected function collectionResult(Query $query)
    {
        //return $query->getResult();
        return new Paginator($query, true);
    }
}
