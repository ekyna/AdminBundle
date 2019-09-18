<?php

namespace Ekyna\Bundle\AdminBundle\Model;

/**
 * Class BarcodeResult
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeResult implements \JsonSerializable
{
    const TYPE_REDIRECT = 'redirect';
    const TYPE_MODAL    = 'modal';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $url;


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
            throw new \InvalidArgumentException("Unexpected barcode result type.");
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
    public function jsonSerialize()
    {
        return [
            'type'  => $this->type,
            'title' => $this->title,
            'url'   => $this->url,
        ];
    }
}
