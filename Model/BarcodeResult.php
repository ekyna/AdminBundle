<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Model;

use InvalidArgumentException;
use JsonSerializable;

use function in_array;

/**
 * Class BarcodeResult
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeResult implements JsonSerializable
{
    public const TYPE_REDIRECT = 'redirect';
    public const TYPE_MODAL    = 'modal';

    private string $type;
    private string $title;
    private string $url;


    /**
     * Constructor.
     *
     * @param string $type
     * @param string $title
     * @param string $url
     */
    public function __construct(string $type, string $title, string $url)
    {
        if (!in_array($type, [self::TYPE_REDIRECT, self::TYPE_MODAL], true)) {
            throw new InvalidArgumentException('Unexpected barcode result type.');
        }

        $this->type = $type;
        $this->title = $title;
        $this->url = $url;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'type'  => $this->type,
            'title' => $this->title,
            'url'   => $this->url,
        ];
    }
}
