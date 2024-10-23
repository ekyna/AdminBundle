<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\ActionsType;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Table\Exception\LogicException;
use Ekyna\Component\Table\Exception\RuntimeException;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_push;
use function array_replace;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Class ActionsTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionsTypeExtension extends AbstractColumnTypeExtension
{
    private ResourceHelper $helper;

    public function __construct(ResourceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Builds the buttons for the configured resource and actions.
     */
    private function buildButtons(Options $options): array
    {
        if (empty($actions = $options['actions'])) {
            return [];
        }

        if (null === $resource = $options['resource']) {
            throw new LogicException("Can't configure 'actions' if 'resource' option is not defined.");
        }

        $rCfg = $this->helper->getResourceConfig($resource);
        $parametersMap = $this->helper->buildParametersMap($rCfg);

        $buttons = [];

        foreach ($actions as $action => $config) {
            if (is_string($config) && is_int($action)) {
                $action = $config;
                $config = [];
            }

            if (!$rCfg->hasAction($action)) {
                throw new RuntimeException(sprintf('Resource has nos action named \'%s\'.', $action));
            }

            $aCfg = $this->helper->getActionConfig($action);

            if (null === $button = $aCfg->getButton()) {
                throw new RuntimeException(sprintf('No button configured for action \'%s\'.', $action));
            }

            $button = array_replace([
                'route'          => $this->helper->getRoute($resource, $action),
                'parameters_map' => $parametersMap,
                'permission'     => $aCfg->getName(),
            ], $button, $config);

            $buttons[] = $button;
        }

        return $buttons;
    }

    /**
     * Builds the button.
     */
    private function buildButton(array $button, ?ResourceConfig $resource): ?array
    {
        if (empty($action = $button['action'])) {
            return $button;
        }

        if (is_null($resource)) {
            throw new LogicException("Can't configure 'action' if 'resource' option is not defined.");
        }

        if (!$resource->hasAction($action)) {
            throw new RuntimeException(sprintf('Resource has nos action named \'%s\'.', $action));
        }

        $aCfg = $this->helper->getActionConfig($action);

        if (null === $config = $aCfg->getButton()) {
            throw new RuntimeException(sprintf('No button configured for action \'%s\'.', $action));
        }

        return array_replace($config, [
            'route'          => $this->helper->getRoute($resource->getId(), $action),
            'parameters_map' => $this->helper->buildParametersMap($resource),
            'permission'     => $aCfg->getName(),
        ], $button);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'resource'        => null,
                'actions'         => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
                'buttons_loader'  => function (Options $options, $extended) {
                    return function (Options $options, array $buttons) use ($extended): array {
                        if ($extended) {
                            $buttons = $extended($options, $buttons);
                        }

                        array_push($buttons, ...$this->buildButtons($options));

                        return $buttons;
                    };
                },
                'button_resolver' => function (Options $options, $extended) {
                    if (!$extended instanceof OptionsResolver) {
                        throw new UnexpectedTypeException($extended, OptionsResolver::class);
                    }

                    return $extended
                        ->setDefaults([
                            'action'     => null,
                            'resource'   => null,
                            'permission' => null,
                        ])
                        ->setAllowedTypes('action', ['string', 'null'])
                        ->setAllowedTypes('resource', ['string', 'null'])
                        ->setAllowedTypes('permission', ['string', 'null']);
                },
                'button_builder'  => function (Options $options, $extended) {
                    $resource = null;
                    if ($r = $options['resource']) {
                        $resource = $this->helper->getResourceConfig($r);
                    }

                    return function (RowInterface $row, array $button) use ($extended, $resource): ?array {
                        $rCfg = $resource;
                        if ($r = $button['resource']) {
                            $rCfg = $this->helper->getResourceConfig($r);
                        }

                        $button = $this->buildButton($button, $rCfg);

                        $permission = $button['permission'];
                        if (!empty($permission) && is_object($data = $row->getData(null))) {
                            if (!$this->helper->isGranted($permission, $data)) {
                                return null;
                            }
                        }

                        return $extended($row, $button);
                    };
                },
            ])
            ->setAllowedTypes('resource', ['string', 'null'])
            ->setAllowedTypes('actions', 'array');
    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): array
    {
        return [ActionsType::class];
    }
}
