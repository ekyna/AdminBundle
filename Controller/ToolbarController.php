<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Event\BarcodeEvent;
use Ekyna\Bundle\AdminBundle\Service\Search\SearchHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ToolbarController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ToolbarController
{
    private SearchHelper             $helper;
    private EventDispatcherInterface $dispatcher;


    /**
     * Constructor.
     *
     * @param SearchHelper             $helper
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        SearchHelper $helper,
        EventDispatcherInterface $dispatcher
    ) {
        $this->helper = $helper;
        $this->dispatcher = $dispatcher;
    }

    /**
     * (Wide) Search action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $results = $this->helper->search($request);

        return new JsonResponse(['results' => $results]);
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

        $this->dispatcher->dispatch($event, BarcodeEvent::NAME);

        if (0 === $event->getResults()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'barcode' => $event->getBarcode(),
            'results' => $event->getResults(),
        ]);
    }
}
