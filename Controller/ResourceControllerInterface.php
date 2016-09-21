<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Component\Resource\Configuration\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ResourceControllerInterface
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceControllerInterface
{
    /**
     * Sets the configuration.
     *
     * @param \Ekyna\Component\Resource\Configuration\ConfigurationInterface $config
     */
    public function setConfiguration(ConfigurationInterface $config);

    /**
     * Home action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction();

    /**
     * List action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request);

    /**
     * New/Create action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request);

    /**
     * Show action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request);

    /**
     * Edit/Update action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request);

    /**
     * Remove/Delete action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request);

    /**
     * Search action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request);

    /**
     * Find action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function findAction(Request $request);

    /**
     * Returns the controller configuration.
     *
     * @return \Ekyna\Component\Resource\Configuration\ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Returns whether the resource has a parent or not.
     *
     * @return boolean
     */
    public function hasParent();

    /**
     * Returns the parent controller.
     *
     * @return ResourceControllerInterface
     */
    public function getParentController();

    /**
     * Returns the parent configuration.
     *
     * @return \Ekyna\Component\Resource\Configuration\ConfigurationInterface
     */
    public function getParentConfiguration();

    /**
     * Creates (or fill) the context for the given request
     *
     * @param Request $request
     * @return Context
     */
    public function loadContext(Request $request);
}
