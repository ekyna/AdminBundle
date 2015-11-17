<?php

namespace Ekyna\Bundle\AdminBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Bundle\AdminBundle\Model\TranslatableInterface;

/**
 * Class ORMTranslatableListener
 * @package Ekyna\Bundle\AdminBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ORMTranslatableListener extends AbstractTranslatableListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
            Events::postLoad,
        );
    }

    /**
     * Add mapping to translatable entities
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();
        $reflection    = $classMetadata->reflClass;

        if (!$reflection || $reflection->isAbstract()) {
            return;
        }

        if ($reflection->implementsInterface('Ekyna\Bundle\AdminBundle\Model\TranslatableInterface')) {
            $this->mapTranslatable($classMetadata);
        }

        if ($reflection->implementsInterface('Ekyna\Bundle\AdminBundle\Model\TranslationInterface')) {
            $this->mapTranslation($classMetadata);
        }
    }

    /**
     * Add mapping data to a translatable entity.
     *
     * @param ClassMetadata $metadata
     */
    private function mapTranslatable(ClassMetadata $metadata)
    {
        // In the case A -> B -> TranslatableInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return.
        if (!isset($this->configs[$metadata->name])) {
            return;
        }

        $metadata->mapOneToMany(array(
            'fieldName'     => 'translations',
            'targetEntity'  => $this->configs[$metadata->name],
            'mappedBy'      => 'translatable',
            'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            'indexBy'       => 'locale',
            'cascade'       => array('persist', 'merge', 'refresh', 'remove', 'detach'),
            'orphanRemoval' => true,
        ));
    }

    /**
     * Add mapping data to a translation entity.
     *
     * @param ClassMetadata $metadata
     */
    private function mapTranslation(ClassMetadata $metadata)
    {
        // In the case A -> B -> TranslationInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return.
        if (!isset($this->configs[$metadata->name])) {
            return;
        }

        $metadata->mapManyToOne(array(
            'fieldName'    => 'translatable' ,
            'targetEntity' => $this->configs[$metadata->name],
            'inversedBy'   => 'translations' ,
            'joinColumns'  => array(array(
                'name'                 => 'translatable_id',
                'referencedColumnName' => 'id',
                'onDelete'             => 'CASCADE',
                'nullable'             => false,
            )),
        ));

        if (!$metadata->hasField('locale')) {
            $metadata->mapField(array(
                'fieldName' => 'locale',
                'type'      => 'string',
                'nullable'  => false,
            ));
        }

        // Map unique index.
        $columns = array(
            $metadata->getSingleAssociationJoinColumnName('translatable'),
            'locale'
        );

        if (!$this->hasUniqueConstraint($metadata, $columns)) {
            $constraints = isset($metadata->table['uniqueConstraints']) ? $metadata->table['uniqueConstraints'] : array();

            $constraints[$metadata->getTableName().'_uniq_trans'] = array(
                'columns' => $columns,
            );

            $metadata->setPrimaryTable(array(
                'uniqueConstraints' => $constraints,
            ));
        }

        $metadata->addEntityListener('preFlush', '\Ekyna\Bundle\AdminBundle\Listener\TranslationListener', 'preFlush');
    }

    /**
     * Check if an unique constraint has been defined.
     *
     * @param ClassMetadata $metadata
     * @param array         $columns
     *
     * @return bool
     */
    private function hasUniqueConstraint(ClassMetadata $metadata, array $columns)
    {
        if (!isset($metadata->table['uniqueConstraints'])) {
            return false;
        }

        foreach ($metadata->table['uniqueConstraints'] as $constraint) {
            if (!array_diff($constraint['columns'], $columns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load translations.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $entity->setCurrentLocale($this->localeProvider->getCurrentLocale());
        $entity->setFallbackLocale($this->localeProvider->getFallbackLocale());
    }
}
