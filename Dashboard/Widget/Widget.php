<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\WidgetTypeInterface;

/**
 * Class Widget
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Widget implements WidgetInterface
{
    protected string              $name;
    protected WidgetTypeInterface $type;
    protected array               $options;


    /**
     * Constructor.
     *
     * @param string              $name
     * @param WidgetTypeInterface $type
     * @param array               $options
     */
    public function __construct(string $name, WidgetTypeInterface $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): WidgetTypeInterface
    {
        return $this->type;
    }

    public function setOptions(array $options): WidgetInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOption(string $name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
