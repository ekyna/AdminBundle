<?php

namespace Ekyna\Bundle\AdminBundle\Event;

use Ekyna\Bundle\AdminBundle\Model\BarcodeResult;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BarcodeEvent
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeEvent extends Event
{
    const NAME = 'ekyna_admin.barcode';

    /**
     * @var string
     */
    private $barcode;

    /**
     * @var BarcodeResult[]
     */
    private $results = [];


    /**
     * Constructor.
     *
     * @param string $barcode
     */
    public function __construct(string $barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * Returns the barcode.
     *
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * Adds the result.
     *
     * @param BarcodeResult $result
     *
     * @return $this
     */
    public function addResult(BarcodeResult $result): self
    {
        $this->results[] = $result;

        return $this;
    }

    /**
     * Returns the results.
     *
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
