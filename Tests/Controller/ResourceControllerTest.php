<?php

namespace Ekyna\Bundle\AdminBundle\Tests\Controller;

use Ekyna\Bundle\AdminBundle\Tests\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * Class ResourceControllerTest
 * @package Ekyna\Bundle\AdminBundle\Tests\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class ResourceControllerTest extends WebTestCase
{
    /**
     * @var string
     */
    protected $configurationId;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationInterface
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = $this->client->getContainer()->get($this->configurationId);
    }

    /**
     * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected function getRouter()
    {
        return $this->client->getContainer()->get('router');
    }

    /**
     * @param $action
     * @param array $params
     * @return string
     */
    protected function generatePath($action, $params = array())
    {
        return $this->getRouter()->generate(
            $this->config->getRoute($action),
            array_merge($this->getBaseRouteParams(), $params)
        );
    }

    /**
     * Returns the base route parameters (parents identifiers).
     *
     * @return array
     */
    protected function getBaseRouteParams()
    {
        return array();
    }

    /**
     * Tests the list action.
     */
    public function testListAction()
    {
        $crawler = $this->client->request('GET', $this->generatePath('list'));

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to reach the "list" page.');

        $this->assertList($crawler);
    }

    /**
     * Asserts the list.
     *
     * @param Crawler $crawler
     * @return mixed
     */
    abstract protected function assertList(Crawler $crawler);

    /**
     * Tests the new action.
     */
    public function testNewAction()
    {
        $crawler = $this->client->request('GET', $this->generatePath('new'));

        // Asserts that this the "new" page
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to reach the "new" page.');

        // Get the form and fills values
        $form = $crawler->selectButton('submit')->form();
        $this->fillNewForm($form);

        // Submit the form
        $this->client->submit($form);

        // Asserts that the response is a redirection.
        $this->assertTrue($this->client->getResponse()->isRedirect(), '"New" form submission failed.');

        $crawler = $this->client->followRedirect();

        // Asserts that the form submission succeed.
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to follow "new" form redirection.');

        // Asserts show after creation.
        $this->assertShowAfterNew($crawler);
    }

    /**
     * Fills the "new" form.
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     * @return array
     */
    abstract protected function fillNewForm(Form $form);

    /**
     * Asserts the show action after creation.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     */
    abstract protected function assertShowAfterNew(Crawler $crawler);

    /**
     * Tests the show action.
     */
    public function testShowAction()
    {
        $params = array($this->config->getResourceName().'Id' => 1);

        $crawler = $this->client->request('GET', $this->generatePath('show', $params));

        // Asserts that this is the "show" page.
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to reach the "show" page.');

        // Run assertions on the "show" page's content.
        $this->assertShow($crawler);
    }

    /**
     * Asserts the show action.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     */
    abstract protected function assertShow(Crawler $crawler);

    /**
     * Tests the edit action.
     */
    public function testEditAction()
    {
        $params = array($this->config->getResourceName().'Id' => 1);
        $crawler = $this->client->request('GET', $this->generatePath('edit', $params));

        // Asserts that this the "edit" page
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to reach the "edit" page.');

        // Get the form and fills values
        $form = $crawler->selectButton('submit')->form();
        $this->fillEditForm($form);

        // Submit the form
        $this->client->submit($form);

        // Asserts that the response is a redirection.
        $this->assertTrue($this->client->getResponse()->isRedirect(), '"Edit" form submission failed.');

        $crawler = $this->client->followRedirect();

        // Asserts that the form submission succeed.
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to follow "edit" form redirection.');

        // Asserts show after update.
        $this->assertShowAfterEdit($crawler);
    }

    /**
     * Fills the "edit" form.
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     * @return array
     */
    abstract protected function fillEditForm(Form $form);

    /**
     * Asserts the show action after update.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     */
    abstract protected function assertShowAfterEdit(Crawler $crawler);

    /**
     * Tests the remove action.
     */
    public function testRemoveAction()
    {
        $params = array($this->config->getResourceName().'Id' => 1);
        $crawler = $this->client->request('GET', $this->generatePath('remove', $params));

        // Asserts that this the "remove" page
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to reach the "remove" page.');

        // Get the form and fills values
        $form = $crawler->selectButton('submit')->form();
        $form['form[confirm]']->tick(); // Confirm remove

        // Submit the form
        $this->client->submit($form);

        // Asserts that the response is a redirection.
        $this->assertTrue($this->client->getResponse()->isRedirect(), '"Remove" form submission failed.');

        $crawler = $this->client->followRedirect();

        // Asserts that the form submission succeed.
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Failed to follow "remove" form redirection.');

        // Asserts list after creation.
        $this->assertListAfterRemove($crawler);
    }

    /**
     * Asserts the list action after remove.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     */
    abstract protected function assertListAfterRemove(Crawler $crawler);
}
