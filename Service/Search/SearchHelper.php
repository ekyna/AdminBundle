<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Search;

use Ekyna\Component\Resource\Search;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function array_map;
use function array_replace;

/**
 * Class SearchHelper
 * @package Ekyna\Bundle\AdminBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SearchHelper
{
    private const SESSION_KEY   = 'ekyna_resource.search';
    private const DATA_DEFAULTS = [
        'expression' => '',
        'resources'  => [],
    ];

    private Search\Search         $search;
    private RequestStack          $requestStack;
    private UrlGeneratorInterface $urlGenerator;
    private Environment           $twig;


    public function __construct(
        Search\Search         $search,
        RequestStack          $requestStack,
        UrlGeneratorInterface $urlGenerator,
        Environment           $twig
    ) {
        $this->search = $search;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    /**
     * Performs the toolbar search.
     */
    public function search(Request $request): array
    {
        $data = array_replace([
            'expression' => '',
            'resources'  => [],
        ], $request->request->get('search', []));

        $this
            ->requestStack
            ->getSession()
            ->set(self::SESSION_KEY, array_replace(self::DATA_DEFAULTS, $data));

        $searchRequest = new Search\Request($data['expression']);
        $searchRequest
            ->setResources($data['resources'])
            ->setPrivate(true);

        $results = $this->search->search($searchRequest);

        return array_map(function (Search\Result $result) {
            $path = $this->urlGenerator->generate($result->getRoute(), $result->getParameters());

            return [
                'title' => $result->getTitle(),
                'icon'  => $result->getIcon(),
                'url'   => $path,
            ];
        }, $results);
    }

    /**
     * Renders the search bar.
     */
    public function render(): string
    {
        $data = $this
            ->requestStack
            ->getSession()
            ->get(self::SESSION_KEY, []);

        $data = array_replace(self::DATA_DEFAULTS, $data);

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->twig->render('@EkynaAdmin/Layout/searchbar.html.twig', [
            'data'      => $data,
            'resources' => $this->search->getChoices(true),
        ]);
    }
}
