<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Dashboard\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class DashboardController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardController
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param Dashboard       $dashboard
     * @param EngineInterface $templating
     */
    public function __construct(Dashboard $dashboard, EngineInterface $templating)
    {
        $this->dashboard = $dashboard;
        $this->templating = $templating;
    }

    /**
     * Dashboard index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return new Response($this->templating->render('@EkynaAdmin/Dashboard/index.html.twig', [
            'dashboard' => $this->dashboard,
        ]));
    }
}
