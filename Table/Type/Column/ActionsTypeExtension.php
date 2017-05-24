<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\ActionsType;
use Ekyna\Component\Table\Exception\LogicException;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ActionsTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ActionsTypeExtension extends AbstractColumnTypeExtension
{
    /**
     * @var AclOperatorInterface
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
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $resolver->setDefaults([
            'button_resolver' => function(Options $options, $extended) {
                if (!$extended instanceof OptionsResolver) {
                    throw new LogicException("Expected instance of " . OptionsResolver::class);
                }

                return $extended
                    ->setDefault('permission', null)
                    ->setAllowedTypes('permission',  ['string', 'null']);
            },
            'button_builder' => function(Options $options, $extended) {
                return function (RowInterface $row, array $buttonOptions) use ($extended) {
                    $permission = $buttonOptions['permission'];
                    if (!empty($permission) && is_object($data = $row->getData())) {
                        if (!$this->aclOperator->isAccessGranted($row->getData(), $permission)) {
                            return null;
                        }
                    }

                    return $extended($row, $buttonOptions);
                };
            }
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return ActionsType::class;
    }
}
