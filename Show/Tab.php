<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show;

use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Class Tab
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Tab
{
    public static function create(string $id, TranslatableInterface $title, string $template, array $data): Tab
    {
        return new self($id, $title, $template, $data);
    }

    public function __construct(string $id, TranslatableInterface $title, string $template, array $data)
    {
        $this->id = $id;
        $this->title = $title;
        $this->template = $template;
        $this->data = $data;
    }

    private string                $id;
    private TranslatableInterface $title;
    private string                $template;
    private array                 $data;
    private int                   $priority = 0;

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): TranslatableInterface
    {
        return $this->title;
    }

    public function setTitle(TranslatableInterface $title): Tab
    {
        $this->title = $title;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): Tab
    {
        $this->template = $template;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): Tab
    {
        $this->data = $data;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): Tab
    {
        $this->priority = $priority;

        return $this;
    }
}
