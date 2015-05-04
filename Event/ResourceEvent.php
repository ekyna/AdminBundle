<?php

namespace Ekyna\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Class ResourceEvent
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceEvent extends Event
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var array
     */
    protected $datas = [];

    /**
     * @var array|ResourceMessage[]
     */
    protected $messages = [];

    /**
     * Sets the resource.
     *
     * @param mixed $resource
     * @return ResourceEvent|$this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Returns the resource.
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Adds the data.
     *
     * @param string $key
     * @param mixed $value
     * @return ResourceEvent|$this
     */
    public function addData($key, $value)
    {
        $this->datas[$key] = $value;

        return $this;
    }

    /**
     * Returns whether there is a data for the given key or not.
     *
     * @param $key
     * @return bool
     */
    public function hasData($key)
    {
        return array_key_exists($key, $this->datas);
    }

    /**
     * Returns the data by key.
     *
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getData($key)
    {
        if ($this->hasData($key)) {
            return $this->datas[$key];
        }
        throw new \InvalidArgumentException(sprintf('Undefined "%s" data.', $key));
    }

    /**
     * Adds the messages.
     *
     * @param array $messages
     * @return ResourceEvent|$this
     */
    public function addMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * Adds the message.
     *
     * @param ResourceMessage $message
     * @return ResourceEvent|$this
     */
    public function addMessage(ResourceMessage $message)
    {
        if ($message->getType() === ResourceMessage::TYPE_ERROR) {
            $this->stopPropagation();
        }
        array_push($this->messages, $message);

        return $this;
    }

    /**
     * Returns the messages, optionally filtered by type.
     *
     * @param string $type
     * @return array|ResourceMessage[]
     */
    public function getMessages($type = null)
    {
        if (null !== $type) {
            ResourceMessage::validateType($type);
            $messages = [];
            foreach ($this->messages as $message) {
                if ($message->getType() === $type) {
                    $messages[] = $message;
                }
            }
            return $messages;
        }
        return $this->messages;
    }

    /**
     * Returns whether the event has messages or not, optionally filtered by type.
     *
     * @param string $type
     * @return bool
     */
    public function hasMessages($type = null)
    {
        if (null !== $type) {
            ResourceMessage::validateType($type);
            foreach ($this->messages as $message) {
                if ($message->getType() === $type) {
                    return true;
                }
            }
            return false;
        }
        return 0 < count($this->messages);
    }

    /**
     * Returns whether the event has errors or not.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasMessages(ResourceMessage::TYPE_ERROR);
    }

    /**
     * Returns the error messages.
     *
     * @return array|ResourceMessage[]
     */
    public function getErrors()
    {
        return $this->getMessages(ResourceMessage::TYPE_ERROR);
    }

    /**
     * Converts messages to flashes.
     *
     * @param FlashBagInterface $flashBag
     */
    public function toFlashes(FlashBagInterface $flashBag)
    {
        foreach ($this->messages as $message) {
            $flashBag->add($message->getType(), $message->getMessage());
        }
    }
}
