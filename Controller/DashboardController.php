<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Dashboard\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class DashboardController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardController
{
    private Dashboard   $dashboard;
    private Environment $twig;


    /**
     * Constructor.
     *
     * @param Dashboard   $dashboard
     * @param Environment $twig
     */
    public function __construct(Dashboard $dashboard, Environment $twig)
    {
        $this->dashboard = $dashboard;
        $this->twig = $twig;
    }

    /**
     * Dashboard index action.
     *
     * @return Response
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __invoke(): Response
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $content = $this->twig->render('@EkynaAdmin/Dashboard/index.html.twig', [
            'dashboard' => $this->dashboard,
        ]);

        return new Response($content);
    }
}
