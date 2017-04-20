<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Event;

use Ekyna\Bundle\AdminBundle\Model\BarcodeResult;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class BarcodeEvent
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BarcodeEvent extends Event
{
    public const NAME = 'ekyna_admin.barcode';

    private string $barcode;
    /** @var array<BarcodeResult> */
    private array $results = [];

    public function __construct(string $barcode)
    {
        $this->barcode = $barcode;
    }

    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * @return $this|BarcodeEvent
     */
    public function addResult(BarcodeResult $result): self
    {
        $this->results[] = $result;

        return $this;
    }

    /**
     * @return array<BarcodeResult>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
