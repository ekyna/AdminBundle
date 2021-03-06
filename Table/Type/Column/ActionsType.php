<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ActionsType as BaseType;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\TableConfig;
use Ekyna\Component\Table\View\Cell;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActionsType
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
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
     * {@inheritdoc}
     */
    protected function configureButtonOptions(OptionsResolver $resolver)
    {
        parent::configureButtonOptions($resolver);

        $resolver
            ->setDefaults([
                'permission' => null,
            ])
            ->setAllowedTypes('permission',  ['string', 'null'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareButtons(TableConfig $config, array $buttonsOptions)
    {
        $buttonResolver = new OptionsResolver();
        $this->configureButtonOptions($buttonResolver);

        $tmp = [];
        foreach($buttonsOptions as $buttonOptions) {
            $tmpButton = $buttonResolver->resolve($buttonOptions);
            if (null !== $tmpButton['permission'] && !$this->aclOperator->isAccessGranted($config->getDataClass(), $tmpButton['permission'])) {
                continue;
            }
            $tmp[] = $tmpButton;
        }
        return $tmp;
    }

    /**
     * {@inheritdoc}
     */
    public function buildViewCell(Cell $cell, Table $table, array $options)
    {
        parent::buildViewCell($cell, $table, $options);

        $cell->setVars([
            'type' => 'actions',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_actions';
    }
}
