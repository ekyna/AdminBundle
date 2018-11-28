<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\TableBundle\Extension\Type\Column\ActionsType;
use Ekyna\Component\Table\Exception\LogicException;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ActionsTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ActionsTypeExtension extends AbstractColumnTypeExtension
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'button_resolver' => function(
                /** @noinspection PhpUnusedParameterInspection */
                Options $options, $extended) {
                if (!$extended instanceof OptionsResolver) {
                    throw new LogicException("Expected instance of " . OptionsResolver::class);
                }

                return $extended
                    ->setDefault('permission', null)
                    ->setAllowedTypes('permission',  ['string', 'null']);
            },
            'button_builder' => function(
                /** @noinspection PhpUnusedParameterInspection */
                Options $options, $extended) {
                return function (RowInterface $row, array $buttonOptions) use ($extended) {
                    $permission = $buttonOptions['permission'];
                    if (!empty($permission) && is_object($data = $row->getData())) {
                        if (!$this->authorization->isGranted($permission, $row->getData())) {
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
