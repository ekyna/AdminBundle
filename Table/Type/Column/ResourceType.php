<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Column\EntityType;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceType extends AbstractColumnType
{
    public function __construct(
        private readonly ResourceHelper                $resourceHelper,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TranslatorInterface           $translator
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        /** @var ActionConfig $readCfg */
        $readCfg = $options['read'];
        /** @var ActionConfig $summaryCfg */
        $summaryCfg = $options['summary'];

        if (null === $readCfg && null === $summaryCfg) {
            throw new LogicException(
                'You must use at least \'read\' or \'summary\' option. Otherwise, use EntityType column.'
            );
        }

        /** @var ResourceConfig $resourceCfg */
        $resourceCfg = $options['resource'];

        if ($readCfg && !$resourceCfg->hasAction($readCfg->getName())) {
            throw new LogicException(
                sprintf(
                    'Resource \'%s\' does not have \'%s\' action.',
                    $resourceCfg->getName(),
                    $readCfg->getName()
                )
            );
        }

        if ($summaryCfg && !$resourceCfg->hasAction($summaryCfg->getName())) {
            throw new LogicException(
                sprintf(
                    'Resource \'%s\' does not have \'%s\' action.',
                    $resourceCfg->getName(),
                    $summaryCfg->getName()
                )
            );
        }

        $entities = $view->vars['value'];

        $summaryKey = $options['summary_as_panel'] ? Ui::SIDE_DETAIL_ATTR : Ui::SUMMARY_ATTR;

        foreach ($entities as &$entity) {
            $resource = $entity['value'];

            $attr = [];
            if ($readCfg && $this->authorization->isGranted($readCfg->getPermission(), $resource)) {
                $attr['href'] = $this
                    ->resourceHelper
                    ->generateResourcePath($resource, $readCfg->getName(), $options['read_parameters']);
            }

            if ($summaryCfg && $this->authorization->isGranted($summaryCfg->getPermission(), $resource)) {
                $attr[$summaryKey] = $this
                    ->resourceHelper
                    ->generateResourcePath($resource, $summaryCfg->getName(), $options['summary_parameters']);
            }

            if (empty($attr)) {
                continue;
            }

            $entity['attr'] = array_replace($entity['attr'] ?? [], $attr);
            $entity['tag'] = 'a';
        }

        $view->vars['value'] = $entities;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('resource')
            ->setDefaults([
                'read'               => true,
                'read_parameters'    => [],
                'summary'            => false,
                'summary_parameters' => [],
                'summary_as_panel'   => true,
            ])
            ->setDefault('label', function (Options $options): string {
                $config = $options['resource'];

                return $this->translator->trans($config->getResourceLabel(), [], $config->getTransDomain());
            })
            ->setAllowedTypes('resource', ['string', ResourceConfig::class])
            ->setAllowedTypes('read', ['bool', 'string', ActionConfig::class])
            ->setAllowedTypes('read_parameters', 'array')
            ->setAllowedTypes('summary', ['bool', 'string', ActionConfig::class])
            ->setAllowedTypes('summary_parameters', 'array')
            ->setAllowedTypes('summary_as_panel', 'bool')
            ->setNormalizer('resource', function (Options $options, $value): ResourceConfig {
                if ($value instanceof ResourceConfig) {
                    return $value;
                }

                return $this->resourceHelper->getResourceConfig($value);
            })
            ->setNormalizer('read', function (Options $options, $value): ?ActionConfig {
                if (false === $value) {
                    return null;
                }

                if (true === $value) {
                    $value = ReadAction::class;
                }

                return $this->resourceHelper->getActionConfig($value);
            })
            ->setNormalizer('summary', function (Options $options, $value): ?ActionConfig {
                if (false === $value) {
                    return null;
                }

                if (true === $value) {
                    $value = SummaryAction::class;
                }

                return $this->resourceHelper->getActionConfig($value);
            });
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
