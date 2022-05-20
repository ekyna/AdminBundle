<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Event;

use Ekyna\Bundle\AdminBundle\Show\Tab;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

use function uasort;

/**
 * Class ReadResourceEvent
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReadResourceEvent extends Event
{
    private ResourceInterface $resource;
    private array             $tabs       = [];
    private ?array            $sortedTabs = null;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function addTab(Tab $tab): void
    {
        $this->tabs[] = $tab;
        $this->sortedTabs = null;
    }

    public function getSortedTabs(): array
    {
        if (null !== $this->sortedTabs) {
            return $this->sortedTabs;
        }

        $this->sortedTabs = $this->tabs;

        // Higher priority first
        uasort($this->sortedTabs, function(Tab $a, Tab $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $this->sortedTabs;
    }
}
