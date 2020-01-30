<?php

namespace Ekyna\Bundle\AdminBundle\Service\Search;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class SearchHelper
 * @package Ekyna\Bundle\AdminBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SearchHelper
{
    private const SESSION_KEY = 'ekyna_resource.search';
    private const DATA_DEFAULTS = [
        'expression' => '',
        'resources'  => [],
    ];

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var string[]
     */
    private $resources;


    /**
     * Constructor.
     *
     * @param SessionInterface $session
     * @param EngineInterface  $engine
     * @param string[]         $resources
     */
    public function __construct(
        SessionInterface $session,
        EngineInterface $engine,
        array $resources
    ) {
        $this->session   = $session;
        $this->engine    = $engine;
        $this->resources = $resources;
    }

    /**
     * Saves the user data.
     *
     * @param array $data
     */
    public function saveUserData(array $data): void
    {
        $this->session->set(self::SESSION_KEY, array_replace(self::DATA_DEFAULTS, $data));
    }

    /**
     * Renders the search bar.
     *
     * @return string
     */
    public function render(): string
    {
        $data = array_replace(self::DATA_DEFAULTS, $this->session->get(self::SESSION_KEY, []));

        return $this->engine->render('@EkynaAdmin/Layout/searchbar.html.twig', [
            'data'      => $data,
            'resources' => $this->resources,
        ]);
    }
}
