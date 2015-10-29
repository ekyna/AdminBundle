<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Menu\MenuPool;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ShortcutsWidgetType
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ShortcutsWidgetType extends AbstractWidgetType
{
    /**
     * @var MenuPool
     */
    private $menuPool;

    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var AclOperatorInterface
     */
    private $aclOperator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param MenuPool              $menuPool
     * @param ConfigurationRegistry $registry
     * @param AclOperatorInterface  $aclOperator
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        MenuPool $menuPool,
        ConfigurationRegistry $registry,
        AclOperatorInterface $aclOperator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->menuPool     = $menuPool;
        $this->registry     = $registry;
        $this->aclOperator  = $aclOperator;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function render(WidgetInterface $widget, \Twig_Environment $twig)
    {
        return $twig->render('EkynaAdminBundle:Dashboard:widget_shortcuts.html.twig', array(
            'columns' => $this->createColumns(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(array(
                'css_path' => '/bundles/ekynaadmin/css/dashboard-shortcuts.css',
            ))
        ;
    }

    private function createColumns()
    {
        $groups = $this->createGroups();

        // Sort groups by count desc
        usort($groups, function($gA, $gB) {
            if ($gA['count'] == $gB['count']) {
                return  0;
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
        do {
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

        } while(!empty($groups));

        return $columns;
    }

    private function createGroups()
    {
        $groups = [];

        foreach ($this->menuPool->getGroups() as $menuGroup) {
            // Entries
            $entries = [];
            foreach ($menuGroup->getEntries() as $menuEntry) {
                $entry = array(
                    'label'  => $menuEntry->getLabel(),
                    'domain' => $menuEntry->getDomain(),
                    'path'   => $this->urlGenerator->generate($menuEntry->getRoute()),
                );
                if (null !== $resource = $menuEntry->getResource()) {
                    if (null !== $config = $this->registry->get($resource)) {
                        if (null !== $config->getParentId() || !$this->aclOperator->isAccessGranted($resource, 'VIEW')) {
                            continue;
                        }
                        if($this->aclOperator->isAccessGranted($resource, 'CREATE')) {
                            try {
                                $path = $this->urlGenerator->generate($config->getRoute('new'));
                                $entry['create_path'] = $path;
                            } catch(ExceptionInterface $e) {
                            }
                        }
                    }
                }
                $entries[] = $entry;
            }

            // Group
            $group = array(
                'label'   => $menuGroup->getLabel(),
                'domain'  => $menuGroup->getDomain(),
                'icon'    => $menuGroup->getIcon(),
            );
            if (!empty($entries)) {
                $group['entries'] = $entries;
                $group['count']   = count($entries) + 2;
            } else {
                $group['path']  = $this->urlGenerator->generate($menuGroup->getRoute());
                $group['count'] = 2;
            }
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_shortcuts';
    }
}
