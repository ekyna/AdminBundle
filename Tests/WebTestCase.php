<?php

namespace Ekyna\Bundle\AdminBundle\Tests;

use Ekyna\Bundle\CoreBundle\Tests\WebTestCase as BaseTestCase;

/**
 * Class WebTestCase
 * @package Ekyna\Bundle\AdminBundle\Tests
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class WebTestCase extends BaseTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->logIn();
    }

    protected function logIn()
    {
        // TODO https://gist.github.com/deltaepsilon/6391565 ?
        $crawler = $this->client->request('GET', $this->generatePath('ekyna_admin_security_login'));

        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => 'admin@example.org',
            '_password'  => 'admin',
        ));

        $this->client->submit($form);
        $this->client->followRedirect();
    }
}
