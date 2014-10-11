<?php

namespace Ekyna\Bundle\AdminBundle\Controller;
use Ekyna\Bundle\CoreBundle\Controller\Controller;


/**
 * Class DashboardController
 * @package Ekyna\Bundle\AdminBundle\Controller
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
