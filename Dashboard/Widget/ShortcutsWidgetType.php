<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Action\CreateAction;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Service\Menu\MenuPool;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Action\Permission;
use Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

use function array_pop;
use function array_shift;
use function ceil;

/**
 * Class ShortcutsWidgetType
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShortcutsWidgetType extends AbstractWidgetType
{
    public const NAME = 'admin_shortcuts';

    private MenuPool       $pool;
    private ResourceHelper $helper;

    public function __construct(MenuPool $pool, ResourceHelper $helper)
    {
        $this->pool = $pool;
        $this->helper = $helper;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $twig->render('@EkynaAdmin/Dashboard/widget_shortcuts.html.twig', [
            'columns' => $this->createColumns(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'title'        => 'shortcuts',
            'trans_domain' => 'EkynaAdmin',
            'position'     => -9999,
        ]);
    }

    private function createColumns(): array
    {
        $groups = $this->createGroups();

        // Sort groups by count desc
        usort($groups, function (array $gA, array $gB): int {
            if ($gA['count'] === $gB['count']) {
                return 0;
            }

            return $gA['count'] < $gB['count'] ? 1 : -1;
        });

        // How many per column ?
        $count = 0;
        foreach ($groups as $group) {
            $count += $group['count'];
        }
        $countPerCol = ceil($count / 4);

        $currentCount = 0;
        $columns = [];
        $columnGroups = [];
        while (!empty($groups)) {
            if (empty($columnGroups)) {
                $group = array_shift($groups);
                $columnGroups[] = $group;
                $currentCount += $group['count'];
            }

            while ($currentCount < $countPerCol && !empty($groups)) {
                if (end($groups)['count'] + $currentCount - $countPerCol > 2) {
                    break;
                }
                $group = array_pop($groups);
                $columnGroups[] = $group;
                $currentCount += $group['count'];
            }

            $columns[] = $columnGroups;
            $columnGroups = [];
            $currentCount = 0;
        }

        return $columns;
    }

    private function createGroups(): array
    {
        $groups = [];

        foreach ($this->pool->getGroups() as $menuGroup) {
            // Entries
            $entries = [];
            foreach ($menuGroup->getEntries() as $menuEntry) {
                $entry = [
                    'label'  => $menuEntry->getLabel(),
                    'domain' => $menuEntry->getDomain(),
                ];

                if (null !== $resource = $menuEntry->getResource()) {
                    $config = $this->helper->getResourceConfig($resource);

                    if (empty($entry['label'])) {
                        $entry['label'] = $config->getResourceLabel(true);
                        $entry['domain'] = $config->getTransDomain();
                    }

                    try {
                        $entry['path'] = $this
                            ->helper
                            ->generateResourcePath($resource, ListAction::class);
                    } catch (Exception $e) {
                        continue;
                    }

                    // Skip if resource has a parent context
                    if (null !== $config->getParentId()) {
                        continue;
                    }

                    // Skip if read is not granted
                    if (!$this->helper->isGranted(Permission::READ, $resource)) {
                        continue;
                    }

                    // Add create button if granted
                    if ($this->helper->isGranted(Permission::CREATE, $resource)) {
                        try {
                            $entry['create_path'] = $this
                                ->helper
                                ->generateResourcePath($resource, CreateAction::class);
                        } catch (Exception $e) {
                        }
                    }
                } elseif (!empty($route = $menuEntry->getRoute())) {
                    $entry['path'] = $this->helper->getUrlGenerator()->generate($menuEntry->getRoute());
                } else {
                    continue;
                }

                $entries[] = $entry;
            }

            // Group
            $group = [
                'label'  => $menuGroup->getLabel(),
                'domain' => $menuGroup->getDomain(),
                'icon'   => $menuGroup->getIcon(),
            ];
            if (!empty($entries)) {
                $group['entries'] = $entries;
                $group['count'] = count($entries) + 2;
            } elseif (!empty($route = $menuGroup->getRoute())) {
                $group['path'] = $this->helper->getUrlGenerator()->generate($route);
                $group['count'] = 2;
            } else {
                continue;
            }

            $groups[] = $group;
        }

        return $groups;
    }

    public static function getName(): string
    {
        return self::NAME;
    }
}
