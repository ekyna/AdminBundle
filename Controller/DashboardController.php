<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * DashboardController
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardController extends Controller
{
    /**
     * Dashboard index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('EkynaAdminBundle:Dashboard:index.html.twig');
    }
}
