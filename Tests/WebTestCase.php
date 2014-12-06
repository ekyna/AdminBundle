<?php

namespace Ekyna\Bundle\AdminBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;
//use Symfony\Component\BrowserKit\Cookie;
//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class WebTestCase
 * @package Ekyna\Bundle\AdminBundle\Tests
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class WebTestCase extends BaseTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client = null;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
    }

    /**
     * Logs in as super administrator.
     *
     * @see http://symfony.com/fr/doc/current/cookbook/testing/simulating_authentication.html
     */
    /*protected function logInAsSuperAdmin()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'admin';
        $token = new UsernamePasswordToken('admin', 'admin', $firewall, array());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }*/
}
