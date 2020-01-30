<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Event\BarcodeEvent;
use Ekyna\Bundle\AdminBundle\Service\Search\SearchHelper;
use Ekyna\Component\Resource\Search;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ToolbarController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ToolbarController
{
    /**
     * @var Search\Search
     */
    private $search;

    /**
     * @var SearchHelper
     */
    private $helper;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;


    /**
     * Constructor.
     *
     * @param Search\Search            $search
     * @param SearchHelper             $helper
     * @param UrlGeneratorInterface    $urlGenerator
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Search\Search $search,
        SearchHelper $helper,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->search       = $search;
        $this->helper       = $helper;
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher   = $dispatcher;
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

        $data = array_replace([
            'expression' => '',
            'resources'  => [],
        ], $request->request->get('search', []));

        $this->helper->saveUserData($data);

        $searchRequest = new Search\Request($data['expression']);
        $searchRequest
            ->setResources($data['resources'])
            ->setPrivate(true);

        $results = $this->search->search($searchRequest);

        $results = array_map(function (Search\Result $result) {
            return [
                'title' => $result->getTitle(),
                'icon'  => $result->getIcon(),
                'url'   => $this->urlGenerator->generate($result->getRoute(), $result->getParameters()),
            ];
        }, $results);

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
