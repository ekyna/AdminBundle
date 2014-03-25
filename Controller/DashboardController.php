<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function indexAction()
    {
        return $this->render('EkynaAdminBundle:Dashboard:index.html.twig');
    }
}
