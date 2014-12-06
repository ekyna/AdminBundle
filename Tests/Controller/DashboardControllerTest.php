<?php

namespace Ekyna\Bundle\AdminBundle\Tests\Controller;

use Ekyna\Bundle\AdminBundle\Tests\WebTestCase;

/**
 * Class DashboardControllerTest
 * @package Ekyna\Bundle\AdminBundle\Tests\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardControllerTest extends WebTestCase
{
    /**
     * Tests the dashboard index action.
     */
    public function testIndexAction()
    {
        $crawler = $this->client->request('GET', '/admin/dashboard');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('h2:contains("Tableau de bord")')->count());
    }
}
