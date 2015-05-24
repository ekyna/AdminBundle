<?php

namespace Ekyna\Bundle\AdminBundle\Event;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Interface ResourceEventInterface
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceEventInterface
{
    /**
     * Sets the resource.
     *
     * @param mixed $resource
     * @return ResourceEventInterface|$this
     */
    public function setResource($resource);

    /**
     * Returns the resource.
     *
     * @return mixed
     */
    public function getResource();

    /**
     * Adds the data.
     *
     * @param string $key
     * @param mixed $value
     * @return ResourceEventInterface|$this
     */
    public function addData($key, $value);

    /**
     * Returns whether there is a data for the given key or not.
     *
     * @param $key
     * @return bool
     */
    public function hasData($key);

    /**
     * Returns the data by key.
     *
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getData($key);

    /**
     * Adds the messages.
     *
     * @param array $messages
     * @return ResourceEventInterface|$this
     */
    public function addMessages(array $messages);

    /**
     * Adds the message.
     *
     * @param ResourceMessage $message
     * @return ResourceEventInterface|$this
     */
    public function addMessage(ResourceMessage $message);

    /**
     * Returns the messages, optionally filtered by type.
     *
     * @param string $type
     * @return array|ResourceMessage[]
     */
    public function getMessages($type = null);

    /**
     * Returns whether the event has messages or not, optionally filtered by type.
     *
     * @param string $type
     * @return bool
     */
    public function hasMessages($type = null);

    /**
     * Returns whether the event has errors or not.
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * Returns the error messages.
     *
     * @return array|ResourceMessage[]
     */
    public function getErrors();

    /**
     * Converts messages to flashes.
     *
     * @param FlashBagInterface $flashBag
     */
    public function toFlashes(FlashBagInterface $flashBag);
}