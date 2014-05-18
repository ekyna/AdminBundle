<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ActionsType as BaseType;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\View\Cell;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * ActionsType.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ActionsType extends BaseType
{
    /**
     * @var \Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface
     */
    private $aclOperator;

    /**
     * Constructor.
     * 
     * @param AclOperatorInterface $aclOperator
     */
    public function __construct(AclOperatorInterface $aclOperator)
    {
        $this->aclOperator = $aclOperator;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureButtonOptions(OptionsResolverInterface $resolver)
    {
        parent::configureButtonOptions($resolver);

        $resolver
            ->setDefaults(array(
                'permission' => null,
            ))
            ->setAllowedTypes(array(
                'permission' => array('string', 'null'),
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareButtons(Table $table, array $buttonsOptions)
    {
        $buttonResolver = new OptionsResolver();
        $this->configureButtonOptions($buttonResolver);

        $tmp = array();
        foreach($buttonsOptions as $buttonOptions) {
            $tmpButton = $buttonResolver->resolve($buttonOptions);
            if (null !== $tmpButton['permission'] && !$this->aclOperator->isAccessGranted($table->getEntityClass(), $tmpButton['permission'])) {
                continue;
            }
            $tmp[] = $tmpButton;
        }
        return $tmp;
    }

    /**
     * {@inheritDoc}
     */
    public function buildViewCell(Cell $cell, PropertyAccessor $propertyAccessor, $entity, array $options)
    {
        parent::buildViewCell($cell, $propertyAccessor, $entity, $options);
        $cell->setVars(array(
            'type' => 'actions',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'admin_actions';
    }
}
