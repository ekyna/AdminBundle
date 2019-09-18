<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Event\BarcodeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ToolbarController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ToolbarController
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Barcode action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function barcode(Request $request): Response
    {
        if (empty($barcode = $request->attributes->get('barcode'))) {
            return new JsonResponse([
                'results' => [],
            ]);
        }

        $event = new BarcodeEvent($barcode);

        $this->dispatcher->dispatch(BarcodeEvent::NAME, $event);

        if (0 === $event->getResults()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'barcode' => $event->getBarcode(),
            'results' => $event->getResults(),
        ]);
    }
}
